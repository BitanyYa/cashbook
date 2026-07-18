<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected $business;
    protected $book1;
    protected $book2;
    protected $employeeUser;
    protected $primaryAdminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test business
        $this->business = Business::factory()->create([
            'name' => 'Test Business',
            'currency' => 'USD'
        ]);

        // Create test books
        $this->book1 = Book::factory()->create([
            'business_id' => $this->business->id,
            'name' => 'Book 1'
        ]);

        $this->book2 = Book::factory()->create([
            'business_id' => $this->business->id,
            'name' => 'Book 2'
        ]);

        // Create users
        $this->employeeUser = User::factory()->create(['name' => 'Employee User']);
        $this->primaryAdminUser = User::factory()->create(['name' => 'Primary Admin User']);

        // Attach users to business
        $this->business->users()->attach($this->employeeUser->id, ['role' => 'employee']);
        $this->business->users()->attach($this->primaryAdminUser->id, ['role' => 'primary_admin']);

        // Give employee user access to only book1
        $this->book1->users()->attach($this->employeeUser->id, ['role' => 'employee']);

        // Primary admin has access to all books by default

        // Set active business in session
        $this->session(['active_business_id' => $this->business->id]);
    }

    /** @test */
    public function primary_admin_sees_all_books_in_dashboard()
    {
        // Create transactions in both books
        Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->book1->id,
            'user_id' => $this->primaryAdminUser->id,
            'type' => 'income',
            'amount' => 1000,
            'status' => 'approved'
        ]);

        Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->book2->id,
            'user_id' => $this->primaryAdminUser->id,
            'type' => 'expense',
            'amount' => 500,
            'status' => 'approved'
        ]);

        $this->actingAs($this->primaryAdminUser);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('accessibleBooks');

        $accessibleBooks = $response->viewData('accessibleBooks');
        $this->assertCount(2, $accessibleBooks);
    }

    /** @test */
    public function employee_user_sees_only_accessible_books_in_dashboard()
    {
        // Create transactions in both books
        Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->book1->id,
            'user_id' => $this->employeeUser->id,
            'type' => 'income',
            'amount' => 1000,
            'status' => 'pending'
        ]);

        Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->book2->id,
            'user_id' => $this->primaryAdminUser->id,
            'type' => 'expense',
            'amount' => 500,
            'status' => 'approved'
        ]);

        $this->actingAs($this->employeeUser);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);

        // Should only see book1 data
        $accessibleBooks = $response->viewData('accessibleBooks');
        $this->assertCount(1, $accessibleBooks);
        $this->assertEquals($this->book1->id, $accessibleBooks->first()->id);

        // Should NOT see the no access message since they have access to book1
        $response->assertDontSee('You don\'t have access to any books in this business yet.');
    }

    /** @test */
    public function employee_user_without_book_access_sees_no_data_message()
    {
        // Remove employee user from all books
        $this->book1->users()->detach($this->employeeUser->id);

        $this->actingAs($this->employeeUser);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee("You don't have access to any books in this business yet.", false);
        $response->assertSee('Contact your business owner or administrator', false);
    }

    /** @test */
    public function sidebar_shows_only_accessible_books()
    {
        $this->actingAs($this->employeeUser);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee($this->book1->name); // Should see book1
        $response->assertDontSee($this->book2->name); // Should not see book2
    }

    /** @test */
    public function book_creator_automatically_becomes_primary_admin()
    {
        $this->actingAs($this->employeeUser);

        $bookData = [
            'name' => 'New Book by Employee',
            'description' => 'Book created by employee user'
        ];

        $response = $this->post(route('books.store'), $bookData);

        $response->assertRedirect();

        // Check that the book was created
        $newBook = Book::where('name', 'New Book by Employee')->first();
        $this->assertNotNull($newBook);

        // Check that the creator is automatically assigned as primary_admin
        $this->assertDatabaseHas('book_user', [
            'book_id' => $newBook->id,
            'user_id' => $this->employeeUser->id,
            'role' => 'primary_admin'
        ]);
    }

    /** @test */
    public function dashboard_statistics_only_include_accessible_books()
    {
        // Create transactions in both books with today's date
        $book1Transaction = Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->book1->id,
            'user_id' => $this->employeeUser->id,
            'type' => 'income',
            'amount' => 1000,
            'status' => 'approved',
            'transaction_date' => now()->toDateString()
        ]);

        $book2Transaction = Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->book2->id,
            'user_id' => $this->primaryAdminUser->id,
            'type' => 'income',
            'amount' => 2000,
            'status' => 'approved',
            'transaction_date' => now()->toDateString()
        ]);

        $this->actingAs($this->employeeUser);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);

        // Check that totals only include book1 transactions
        $totalIncome = $response->viewData('totalIncome');
        $this->assertEquals(1000, $totalIncome); // Only book1 transaction

        // Owner should see all transactions
        $this->actingAs($this->primaryAdminUser);
        $response = $this->get(route('dashboard'));

        $totalIncome = $response->viewData('totalIncome');
        $this->assertEquals(3000, $totalIncome); // Both transactions
    }

    /** @test */
    public function recent_transactions_only_show_accessible_books()
    {
        // Create transactions in both books with today's date
        $book1Transaction = Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->book1->id,
            'user_id' => $this->employeeUser->id,
            'type' => 'income',
            'amount' => 1000,
            'status' => 'approved',
            'description' => 'Book 1 Transaction',
            'transaction_date' => now()->toDateString()
        ]);

        $book2Transaction = Transaction::factory()->create([
            'business_id' => $this->business->id,
            'book_id' => $this->book2->id,
            'user_id' => $this->primaryAdminUser->id,
            'type' => 'income',
            'amount' => 2000,
            'status' => 'approved',
            'description' => 'Book 2 Transaction',
            'transaction_date' => now()->toDateString()
        ]);

        $this->actingAs($this->employeeUser);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);

        // Should see book1 transaction but not book2
        $recentTransactions = $response->viewData('recentTransactions');
        $this->assertCount(1, $recentTransactions);
        $this->assertEquals('Book 1 Transaction', $recentTransactions->first()->description);
    }
}
