<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Business;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected $business;
    protected $book;
    protected $managerUser;
    protected $editorUser;
    protected $viewerUser;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test business
        $this->business = Business::factory()->create([
            'name' => 'Test Business',
            'currency' => 'USD'
        ]);

        // Create test book
        $this->book = Book::factory()->create([
            'business_id' => $this->business->id,
            'name' => 'Test Book',
            'description' => 'Test book for role testing'
        ]);

        // Create users with different roles
        $this->managerUser = User::factory()->create(['name' => 'Manager User']);
        $this->editorUser = User::factory()->create(['name' => 'Editor User']);
        $this->viewerUser = User::factory()->create(['name' => 'Viewer User']);

        // Attach users to business
        $this->business->users()->attach($this->managerUser->id, ['role' => 'employee']);
        $this->business->users()->attach($this->editorUser->id, ['role' => 'employee']);
        $this->business->users()->attach($this->viewerUser->id, ['role' => 'employee']);

        // Attach users to book with different roles
        $this->book->users()->attach($this->managerUser->id, ['role' => 'primary_admin']);
        $this->book->users()->attach($this->editorUser->id, ['role' => 'employee']);
        $this->book->users()->attach($this->viewerUser->id, ['role' => 'employee']);

        // Create test category
        $this->category = Category::factory()->create([
            'business_id' => $this->business->id,
            'name' => 'Test Category'
        ]);
    }

    /** @test */
    public function manager_can_access_book_page()
    {
        $this->actingAs($this->managerUser);

        $response = $this->get(route('books.show', $this->book));

        $response->assertStatus(200);
        $response->assertSee($this->book->name);
        $response->assertSee('Cash In'); // Should see summary cards
        $response->assertSee('Cash Out'); // Should see summary cards
    }

    /** @test */
    public function editor_can_access_book_page()
    {
        $this->actingAs($this->editorUser);

        $response = $this->get(route('books.show', $this->book));

        $response->assertStatus(200);
        $response->assertSee($this->book->name);
        $response->assertSee('Cash In'); // Should see summary cards
        $response->assertSee('Cash Out'); // Should see summary cards
    }

    /** @test */
    public function viewer_can_access_book_page_with_limited_ui()
    {
        $this->actingAs($this->viewerUser);

        $response = $this->get(route('books.show', $this->book));

        $response->assertStatus(200);
        $response->assertSee($this->book->name);
        // Viewer should not see Cash In/Out summary cards
        $response->assertDontSee('Cash In');
        $response->assertDontSee('Cash Out');
        // But should see Net Balance
        $response->assertSee('Net Balance');
    }

    /** @test */
    public function user_without_book_access_cannot_view_book()
    {
        $unauthorizedUser = User::factory()->create(['name' => 'Unauthorized User']);
        $this->business->users()->attach($unauthorizedUser->id, ['role' => 'employee']);

        $this->actingAs($unauthorizedUser);

        $response = $this->get(route('books.show', $this->book));

        $response->assertStatus(403);
    }

    /** @test */
    public function manager_can_create_approved_transactions()
    {
        $this->actingAs($this->managerUser);

        $transactionData = [
            'book_id' => $this->book->id,
            'type' => 'income',
            'amount' => 1000,
            'transaction_date' => now()->toDateString(),
            'description' => 'Test income transaction',
            'category_id' => $this->category->id
        ];

        $response = $this->postJson(route('transactions.store'), $transactionData);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('transactions', [
            'book_id' => $this->book->id,
            'user_id' => $this->managerUser->id,
            'amount' => 1000,
            'status' => 'approved' // Manager transactions should be auto-approved
        ]);
    }

    /** @test */
    public function editor_can_create_pending_transactions()
    {
        $this->actingAs($this->editorUser);

        $transactionData = [
            'book_id' => $this->book->id,
            'type' => 'expense',
            'amount' => 500,
            'transaction_date' => now()->toDateString(),
            'description' => 'Test expense transaction',
            'category_id' => $this->category->id
        ];

        $response = $this->postJson(route('transactions.store'), $transactionData);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('transactions', [
            'book_id' => $this->book->id,
            'user_id' => $this->editorUser->id,
            'amount' => 500,
            'status' => 'pending' // Editor transactions should be pending
        ]);
    }

    /** @test */
    public function viewer_cannot_create_transactions()
    {
        $this->actingAs($this->viewerUser);

        $transactionData = [
            'book_id' => $this->book->id,
            'type' => 'income',
            'amount' => 750,
            'transaction_date' => now()->toDateString(),
            'description' => 'Test transaction by viewer',
            'category_id' => $this->category->id
        ];

        $response = $this->postJson(route('transactions.store'), $transactionData);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Viewers cannot add transactions to this book.'
        ]);
    }

    /** @test */
    public function manager_can_edit_any_transaction()
    {
        // Create transaction by editor
        $transaction = Transaction::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->editorUser->id,
            'type' => 'income',
            'amount' => 1000,
            'status' => 'pending'
        ]);

        $this->actingAs($this->managerUser);

        $updateData = [
            'type' => 'expense',
            'amount' => 1200,
            'transaction_date' => now()->toDateString(),
            'description' => 'Updated by manager'
        ];

        $response = $this->putJson(route('transactions.update', $transaction), $updateData);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 1200,
            'type' => 'expense'
        ]);
    }

    /** @test */
    public function editor_can_only_edit_own_transactions()
    {
        // Create transaction by editor
        $ownTransaction = Transaction::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->editorUser->id,
            'type' => 'income',
            'amount' => 1000,
            'status' => 'pending'
        ]);

        // Create transaction by manager
        $otherTransaction = Transaction::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->managerUser->id,
            'type' => 'income',
            'amount' => 2000,
            'status' => 'approved'
        ]);

        $this->actingAs($this->editorUser);

        // Should be able to edit own transaction
        $updateData = [
            'amount' => 1500,
            'description' => 'Updated own transaction'
        ];

        $response = $this->putJson(route('transactions.update', $ownTransaction), $updateData);
        $response->assertStatus(200);

        // Should not be able to edit other's transaction
        $response = $this->putJson(route('transactions.update', $otherTransaction), $updateData);
        $response->assertStatus(403);
    }

    /** @test */
    public function viewer_cannot_edit_any_transaction()
    {
        $transaction = Transaction::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->managerUser->id,
            'type' => 'income',
            'amount' => 1000,
            'status' => 'approved'
        ]);

        $this->actingAs($this->viewerUser);

        $updateData = [
            'amount' => 1500,
            'description' => 'Viewer trying to update'
        ];

        $response = $this->putJson(route('transactions.update', $transaction), $updateData);

        $response->assertStatus(403);
    }

    /** @test */
    public function manager_can_delete_any_transaction()
    {
        $transaction = Transaction::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->editorUser->id,
            'type' => 'income',
            'amount' => 1000,
            'status' => 'pending'
        ]);

        $this->actingAs($this->managerUser);

        $response = $this->deleteJson(route('transactions.destroy', $transaction));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id
        ]);
    }

    /** @test */
    public function editor_can_only_delete_own_transactions()
    {
        // Create own transaction
        $ownTransaction = Transaction::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->editorUser->id,
            'type' => 'income',
            'amount' => 1000,
            'status' => 'pending'
        ]);

        // Create other's transaction
        $otherTransaction = Transaction::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->managerUser->id,
            'type' => 'income',
            'amount' => 2000,
            'status' => 'approved'
        ]);

        $this->actingAs($this->editorUser);

        // Should be able to delete own transaction
        $response = $this->deleteJson(route('transactions.destroy', $ownTransaction));
        $response->assertStatus(200);

        // Should not be able to delete other's transaction
        $response = $this->deleteJson(route('transactions.destroy', $otherTransaction));
        $response->assertStatus(403);
    }

    /** @test */
    public function viewer_cannot_delete_any_transaction()
    {
        $transaction = Transaction::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->managerUser->id,
            'type' => 'income',
            'amount' => 1000,
            'status' => 'approved'
        ]);

        $this->actingAs($this->viewerUser);

        $response = $this->deleteJson(route('transactions.destroy', $transaction));

        $response->assertStatus(403);
    }

    /** @test */
    public function transactions_datatable_shows_user_column()
    {
        // Create transactions by different users
        $managerTransaction = Transaction::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->managerUser->id,
            'type' => 'income',
            'amount' => 1000,
            'status' => 'approved',
            'description' => 'Manager transaction'
        ]);

        $editorTransaction = Transaction::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->editorUser->id,
            'type' => 'expense',
            'amount' => 500,
            'status' => 'pending',
            'description' => 'Editor transaction'
        ]);

        $this->actingAs($this->managerUser);

        $response = $this->getJson(route('books.transactions.data', $this->book));

        $response->assertStatus(200);
        $responseData = $response->json();

        // Check that user names are included in the response
        $this->assertCount(2, $responseData['data']);

        $transactionData = collect($responseData['data']);
        $managerTransactionData = $transactionData->firstWhere('id', $managerTransaction->id);
        $editorTransactionData = $transactionData->firstWhere('id', $editorTransaction->id);

        $this->assertStringContainsString('Manager User', $managerTransactionData['user']);
        $this->assertStringContainsString('Editor User', $editorTransactionData['user']);
    }

    /** @test */
    public function action_buttons_respect_book_roles()
    {
        // Create transactions by different users
        $managerTransaction = Transaction::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->managerUser->id,
            'type' => 'income',
            'amount' => 1000,
            'status' => 'approved'
        ]);

        $editorTransaction = Transaction::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->editorUser->id,
            'type' => 'expense',
            'amount' => 500,
            'status' => 'pending'
        ]);

        // Test as manager - should see all action buttons
        $this->actingAs($this->managerUser);
        $response = $this->getJson(route('books.transactions.data', $this->book));
        $responseData = $response->json();

        foreach ($responseData['data'] as $transaction) {
            $this->assertStringContainsString('editTransaction', $transaction['actions']);
            $this->assertStringContainsString('deleteTransaction', $transaction['actions']);
        }

        // Test as editor - should only see buttons for own transactions
        $this->actingAs($this->editorUser);
        $response = $this->getJson(route('books.transactions.data', $this->book));
        $responseData = $response->json();

        $editorTransactionData = collect($responseData['data'])->firstWhere('id', $editorTransaction->id);
        $managerTransactionData = collect($responseData['data'])->firstWhere('id', $managerTransaction->id);

        // Should see edit/delete for own transaction
        $this->assertStringContainsString('editTransaction', $editorTransactionData['actions']);
        $this->assertStringContainsString('deleteTransaction', $editorTransactionData['actions']);

        // Should not see edit/delete for manager's transaction
        $this->assertStringNotContainsString('editTransaction', $managerTransactionData['actions']);
        $this->assertStringNotContainsString('deleteTransaction', $managerTransactionData['actions']);

        // Test as viewer - should not see any action buttons
        $this->actingAs($this->viewerUser);
        $response = $this->getJson(route('books.transactions.data', $this->book));
        $responseData = $response->json();

        foreach ($responseData['data'] as $transaction) {
            $this->assertStringNotContainsString('editTransaction', $transaction['actions']);
            $this->assertStringNotContainsString('deleteTransaction', $transaction['actions']);
        }
    }

    /** @test */
    public function book_user_management_works_correctly()
    {
        $businessOwner = User::factory()->create(['name' => 'Business Owner']);
        $this->business->users()->attach($businessOwner->id, ['role' => 'primary_admin']);

        $this->actingAs($businessOwner);

        // Test getting book users
        $response = $this->getJson(route('books.users.index', $this->book));
        $response->assertStatus(200);

        $bookUsers = $response->json()['bookUsers'];
        $this->assertCount(3, $bookUsers); // primary_admin, employee, employee

        // Test adding new user to book
        $newUser = User::factory()->create(['name' => 'New User']);
        $this->business->users()->attach($newUser->id, ['role' => 'employee']);

        $response = $this->postJson(route('books.users.invite', $this->book), [
            'user_id' => $newUser->id,
            'role' => 'employee'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('book_user', [
            'book_id' => $this->book->id,
            'user_id' => $newUser->id,
            'role' => 'employee'
        ]);

        // Test updating user role
        $response = $this->putJson(route('books.users.role', [$this->book, $newUser]), [
            'role' => 'primary_admin'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('book_user', [
            'book_id' => $this->book->id,
            'user_id' => $newUser->id,
            'role' => 'primary_admin'
        ]);

        // Test removing user from book
        $response = $this->deleteJson(route('books.users.destroy', [$this->book, $newUser]));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('book_user', [
            'book_id' => $this->book->id,
            'user_id' => $newUser->id
        ]);
    }

    /** @test */
    public function user_search_functionality_works()
    {
        $businessOwner = User::factory()->create(['name' => 'Business Owner']);
        $this->business->users()->attach($businessOwner->id, ['role' => 'primary_admin']);

        // Create a user not in business
        $externalUser = User::factory()->create([
            'name' => 'External User',
            'email' => 'external@example.com'
        ]);

        $this->actingAs($businessOwner);

        // Search for external user
        $response = $this->getJson(route('books.users.search', $this->book) . '?q=External');

        $response->assertStatus(200);
        $users = $response->json()['users'];

        $this->assertCount(1, $users);
        $this->assertEquals('External User', $users[0]['name']);
        $this->assertFalse($users[0]['is_business_member']);

        // Search for existing business member
        $response = $this->getJson(route('books.users.search', $this->book) . '?q=Manager');

        $response->assertStatus(200);
        $users = $response->json()['users'];

        $this->assertCount(1, $users);
        $this->assertEquals('Manager User', $users[0]['name']);
        $this->assertTrue($users[0]['is_business_member']);
    }
}
