<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Business;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionUpdateReliabilityTest extends TestCase
{
    use RefreshDatabase;

    protected $business;
    protected $bookA;
    protected $bookB;
    protected $bookAdminA;
    protected $bookAdminB;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::factory()->create([
            'name' => 'Test Business',
            'currency' => 'USD'
        ]);

        $this->bookA = Book::factory()->create([
            'business_id' => $this->business->id,
            'name' => 'Book A'
        ]);

        $this->bookB = Book::factory()->create([
            'business_id' => $this->business->id,
            'name' => 'Book B'
        ]);

        $this->bookAdminA = User::factory()->create(['name' => 'Book Admin A']);
        $this->bookAdminB = User::factory()->create(['name' => 'Book Admin B']);

        $this->business->users()->attach($this->bookAdminA->id, ['role' => 'employee']);
        $this->business->users()->attach($this->bookAdminB->id, ['role' => 'employee']);

        $this->bookA->users()->attach($this->bookAdminA->id, ['role' => 'admin']);
        $this->bookB->users()->attach($this->bookAdminB->id, ['role' => 'admin']);

        $this->category = Category::factory()->create([
            'business_id' => $this->business->id,
            'name' => 'Test Category'
        ]);
    }

    /** @test */
    public function authorized_book_admin_can_update_transaction_on_single_request()
    {
        $transaction = Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->bookA->id,
            'user_id' => $this->bookAdminA->id,
            'category_id' => $this->category->id,
            'amount' => 100,
            'type' => 'income',
            'transaction_date' => now(),
            'description' => 'Original description'
        ]);

        $this->actingAs($this->bookAdminA);

        $updateData = [
            'book_id' => $this->bookA->id,
            'category_id' => $this->category->id,
            'amount' => 250.50,
            'type' => 'income',
            'mode' => 'Bank',
            'transaction_date' => '2026-09-09T14:30',
            'description' => 'Updated description cleanly',
            'contact_name' => 'Customer John'
        ];

        $response = $this->putJson(route('transactions.update', $transaction), $updateData);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'book_id' => $this->bookA->id,
            'amount' => 250.50,
            'description' => 'Updated description cleanly',
            'contact_name' => 'Customer John'
        ]);
    }

    /** @test */
    public function book_admin_cannot_update_transaction_in_unauthorized_book()
    {
        $transactionInBookB = Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->bookB->id,
            'user_id' => $this->bookAdminB->id,
            'category_id' => $this->category->id,
            'amount' => 500,
            'type' => 'expense',
            'transaction_date' => now()
        ]);

        // Book Admin A tries to edit transaction in Book B
        $this->actingAs($this->bookAdminA);

        $updateData = [
            'book_id' => $this->bookB->id,
            'amount' => 999,
            'type' => 'expense',
            'transaction_date' => now()->toDateTimeString()
        ];

        $response = $this->putJson(route('transactions.update', $transactionInBookB), $updateData);

        $response->assertStatus(403);
    }
}
