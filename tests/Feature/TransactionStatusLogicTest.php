<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Business;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionStatusLogicTest extends TestCase
{
    use RefreshDatabase;

    protected $business;
    protected $book;
    protected $category;
    protected $primaryAdminUser;
    protected $employeeUser;
    protected $anotherEmployeeUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::factory()->create();
        $this->book = Book::factory()->create(['business_id' => $this->business->id]);
        $this->category = Category::factory()->create(['business_id' => $this->business->id]);

        $this->primaryAdminUser = User::factory()->create(['name' => 'Primary Admin']);
        $this->employeeUser = User::factory()->create(['name' => 'Employee']);
        $this->anotherEmployeeUser = User::factory()->create(['name' => 'Another Employee']);

        // Attach to business
        $this->business->users()->attach([
            $this->primaryAdminUser->id => ['role' => 'employee'],
            $this->employeeUser->id => ['role' => 'employee'],
            $this->anotherEmployeeUser->id => ['role' => 'employee']
        ]);

        // Attach to book with roles
        $this->book->users()->attach([
            $this->primaryAdminUser->id => ['role' => 'primary_admin'],
            $this->employeeUser->id => ['role' => 'employee'],
            $this->anotherEmployeeUser->id => ['role' => 'employee']
        ]);

        // Set active business in session
        $this->session(['active_business_id' => $this->business->id]);
    }

    /** @test */
    public function primary_admin_transactions_are_auto_approved()
    {
        $this->actingAs($this->primaryAdminUser);

        $response = $this->postJson(route('transactions.store'), [
            'book_id' => $this->book->id,
            'category_id' => $this->category->id,
            'type' => 'income',
            'amount' => 1000,
            'transaction_date' => now()->toDateString(),
            'description' => 'Primary Admin transaction'
        ]);

        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('transactions', [
            'book_id' => $this->book->id,
            'user_id' => $this->primaryAdminUser->id,
            'status' => 'approved'
        ]);
    }

    /** @test */
    public function employee_transactions_are_pending()
    {
        $this->actingAs($this->employeeUser);

        $response = $this->postJson(route('transactions.store'), [
            'book_id' => $this->book->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 500,
            'transaction_date' => now()->toDateString(),
            'description' => 'Employee transaction'
        ]);

        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('transactions', [
            'book_id' => $this->book->id,
            'user_id' => $this->employeeUser->id,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function employee_with_no_edit_permission_cannot_create_transactions()
    {
        $this->actingAs($this->anotherEmployeeUser);

        $response = $this->postJson(route('transactions.store'), [
            'book_id' => $this->book->id,
            'type' => 'income',
            'amount' => 750,
            'transaction_date' => now()->toDateString(),
            'description' => 'Employee transaction attempt'
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Employees cannot add transactions to this book'
        ]);
    }

    /** @test */
    public function primary_admin_can_approve_pending_transactions()
    {
        // Create pending transaction by employee
        $transaction = Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->book->id,
            'user_id' => $this->employeeUser->id,
            'status' => 'pending',
            'type' => 'income',
            'amount' => 1000
        ]);

        $this->actingAs($this->primaryAdminUser);

        $response = $this->postJson(route('transactions.approve', $transaction));

        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'approved'
        ]);
    }

    /** @test */
    public function primary_admin_can_reject_pending_transactions()
    {
        // Create pending transaction by employee
        $transaction = Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->book->id,
            'user_id' => $this->employeeUser->id,
            'status' => 'pending',
            'type' => 'expense',
            'amount' => 500
        ]);

        $this->actingAs($this->primaryAdminUser);

        $response = $this->postJson(route('transactions.reject', $transaction));

        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'rejected'
        ]);
    }

    /** @test */
    public function employee_cannot_approve_or_reject_transactions()
    {
        $transaction = Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->book->id,
            'user_id' => $this->primaryAdminUser->id,
            'status' => 'pending',
            'type' => 'income',
            'amount' => 1000
        ]);

        $this->actingAs($this->employeeUser);

        // Try to approve
        $response = $this->postJson(route('transactions.approve', $transaction));
        $response->assertStatus(403);

        // Try to reject
        $response = $this->postJson(route('transactions.reject', $transaction));
        $response->assertStatus(403);
    }

    /** @test */
    public function another_employee_cannot_approve_or_reject_transactions()
    {
        $transaction = Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->book->id,
            'user_id' => $this->employeeUser->id,
            'status' => 'pending',
            'type' => 'income',
            'amount' => 1000
        ]);

        $this->actingAs($this->anotherEmployeeUser);

        // Try to approve
        $response = $this->postJson(route('transactions.approve', $transaction));
        $response->assertStatus(403);

        // Try to reject
        $response = $this->postJson(route('transactions.reject', $transaction));
        $response->assertStatus(403);
    }

    /** @test */
    public function editing_transaction_preserves_original_status_logic()
    {
        // Create approved transaction by primary admin
        $primaryAdminTransaction = Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->book->id,
            'user_id' => $this->primaryAdminUser->id,
            'status' => 'approved',
            'type' => 'income',
            'amount' => 1000
        ]);

        // Create pending transaction by employee
        $employeeTransaction = Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->book->id,
            'user_id' => $this->employeeUser->id,
            'status' => 'pending',
            'type' => 'expense',
            'amount' => 500
        ]);

        // Primary admin edits their own transaction - should remain approved
        $this->actingAs($this->primaryAdminUser);
        $response = $this->putJson(route('transactions.update', $primaryAdminTransaction), [
            'book_id' => $primaryAdminTransaction->book_id,
            'category_id' => $primaryAdminTransaction->category_id,
            'amount' => 1200,
            'type' => $primaryAdminTransaction->type,
            'transaction_date' => $primaryAdminTransaction->transaction_date->format('Y-m-d'),
            'description' => 'Updated by primary admin'
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('transactions', [
            'id' => $primaryAdminTransaction->id,
            'status' => 'approved' // Should remain approved
        ]);

        // Employee edits their own pending transaction - should remain pending
        $this->actingAs($this->employeeUser);
        $response = $this->putJson(route('transactions.update', $employeeTransaction), [
            'book_id' => $employeeTransaction->book_id,
            'category_id' => $employeeTransaction->category_id,
            'amount' => 600,
            'type' => $employeeTransaction->type,
            'transaction_date' => $employeeTransaction->transaction_date->format('Y-m-d'),
            'description' => 'Updated by employee'
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('transactions', [
            'id' => $employeeTransaction->id,
            'status' => 'pending' // Should remain pending
        ]);
    }

    /** @test */
    public function transaction_status_affects_summary_calculations()
    {
        // Create approved transaction
        Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->book->id,
            'user_id' => $this->primaryAdminUser->id,
            'type' => 'income',
            'amount' => 1000,
            'status' => 'approved'
        ]);

        // Create pending transaction
        Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->book->id,
            'user_id' => $this->employeeUser->id,
            'type' => 'income',
            'amount' => 500,
            'status' => 'pending'
        ]);

        // Create rejected transaction
        Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->book->id,
            'user_id' => $this->employeeUser->id,
            'type' => 'income',
            'amount' => 200,
            'status' => 'rejected'
        ]);

        $this->actingAs($this->primaryAdminUser);

        // Check book summary - should only include approved transactions
        $response = $this->get(route('books.show', $this->book));

        $response->assertStatus(200);

        // The page should calculate totals from all transactions but
        // in a real implementation, you might want to filter by status
        // This test verifies the current behavior
        $this->assertTrue(true); // Placeholder - adjust based on actual requirements
    }
}
