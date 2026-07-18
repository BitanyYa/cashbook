<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Notifications\RecurringExecuted;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessRecurringTransactions extends Command
{
    protected $signature = 'cashbook:recurring:process';
    protected $description = 'Process due recurring transactions and create actual entries.';

    public function handle(): int
    {
        $today = Carbon::today();
        $due = RecurringTransaction::whereDate('next_due_date', '<=', $today)->get();
        foreach ($due as $r) {
            // Create transaction
            $t = Transaction::create([
                'business_id' => $r->business_id,
                'book_id' => $r->book_id,
                'category_id' => $r->category_id,
                'user_id' => $r->user_id,
                'amount' => $r->amount,
                'type' => $r->type,
                'status' => 'approved',
                'approved_by' => $r->user_id,
                'description' => '[Recurring] '.$r->description,
                'transaction_date' => $today,
            ]);

            ActivityLog::create([
                'action' => 'recurring.executed',
                'user_id' => $r->user_id,
                'business_id' => $r->business_id,
                'subject_type' => Transaction::class,
                'subject_id' => $t->id,
                'details' => ['recurring_id' => $r->id],
            ]);

            // Notify admins of the business
            $notifiables = $r->business->users()->wherePivotIn('role', ['primary_admin','admin'])->get();
            foreach ($notifiables as $n) {
                if (method_exists($n, 'wantsNotification') ? $n->wantsNotification('recurring_executed') : true) {
                    $n->notify(new RecurringExecuted($t));
                }
            }

            // Update next_due_date
            $next = match ($r->frequency) {
                'daily' => $today->copy()->addDay(),
                'weekly' => $today->copy()->addWeek(),
                'monthly' => $today->copy()->addMonthNoOverflow(),
            };
            $r->next_due_date = $next;
            $r->save();
        }

        $this->info('Processed '.$due->count().' recurring transactions.');
        return self::SUCCESS;
    }
}
