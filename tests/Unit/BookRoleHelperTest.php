<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookRoleHelperTest extends TestCase
{
    use RefreshDatabase;

    protected $business;
    protected $book;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::factory()->create();
        $this->book = Book::factory()->create(['business_id' => $this->business->id]);
        $this->user = User::factory()->create();

        $this->business->users()->attach($this->user->id, ['role' => 'employee']);
    }

    /** @test */
    public function user_book_role_returns_correct_role()
    {
        // Attach user to book as primary_admin
        $this->book->users()->attach($this->user->id, ['role' => 'primary_admin']);

        $role = $this->user->getBookRole($this->book);

        $this->assertEquals('primary_admin', $role);
    }

    /** @test */
    public function user_book_role_returns_null_when_no_access()
    {
        // User not attached to book
        $role = $this->user->getBookRole($this->book);

        $this->assertNull($role);
    }

    /** @test */
    public function business_primary_admin_has_primary_admin_access_to_all_books()
    {
        // Make user business primary admin
        $this->business->users()->updateExistingPivot($this->user->id, ['role' => 'primary_admin']);

        $role = $this->user->getBookRole($this->book);

        $this->assertEquals('primary_admin', $role);
    }

    /** @test */
    public function business_admin_has_admin_access_to_all_books()
    {
        // Make user business admin
        $this->business->users()->updateExistingPivot($this->user->id, ['role' => 'admin']);

        $role = $this->user->getBookRole($this->book);

        $this->assertEquals('admin', $role);
    }

    /** @test */
    public function user_can_check_specific_book_permissions()
    {
        // Test primary_admin permissions
        $this->book->users()->attach($this->user->id, ['role' => 'primary_admin']);

        $this->assertTrue($this->user->canManageBook($this->book));
        $this->assertTrue($this->user->canEditBook($this->book));
        $this->assertTrue($this->user->canViewBook($this->book));

        // Test employee permissions
        $this->book->users()->updateExistingPivot($this->user->id, ['role' => 'employee']);

        $this->assertFalse($this->user->canManageBook($this->book));
        $this->assertTrue($this->user->canEditBook($this->book));
        $this->assertTrue($this->user->canViewBook($this->book));

        // Test employee permissions with no manage access
        $this->book->users()->updateExistingPivot($this->user->id, ['role' => 'employee']);

        $this->assertFalse($this->user->canManageBook($this->book));
        $this->assertTrue($this->user->canEditBook($this->book));
        $this->assertTrue($this->user->canViewBook($this->book));

        // Test no access
        $this->book->users()->detach($this->user->id);

        $this->assertFalse($this->user->canManageBook($this->book));
        $this->assertFalse($this->user->canEditBook($this->book));
        $this->assertFalse($this->user->canViewBook($this->book));
    }

    /** @test */
    public function book_can_check_user_permissions()
    {
        $this->book->users()->attach($this->user->id, ['role' => 'employee']);

        $this->assertTrue($this->book->userHasAccess($this->user));
        $this->assertTrue($this->book->userCanEdit($this->user));
        $this->assertFalse($this->book->userCanManage($this->user));

        // Test with different user
        $otherUser = User::factory()->create();
        $this->business->users()->attach($otherUser->id, ['role' => 'employee']);

        $this->assertFalse($this->book->userHasAccess($otherUser));
        $this->assertFalse($this->book->userCanEdit($otherUser));
        $this->assertFalse($this->book->userCanManage($otherUser));
    }

    /** @test */
    public function accessible_books_query_works_correctly()
    {
        // Create additional books
        $book2 = Book::factory()->create(['business_id' => $this->business->id]);
        $book3 = Book::factory()->create(['business_id' => $this->business->id]);

        // Give user access to book1 and book2 only
        $this->book->users()->attach($this->user->id, ['role' => 'employee']);
        $book2->users()->attach($this->user->id, ['role' => 'employee']);

        $accessibleBooks = $this->user->accessibleBooks($this->business);

        $this->assertCount(2, $accessibleBooks);
        $this->assertTrue($accessibleBooks->contains($this->book));
        $this->assertTrue($accessibleBooks->contains($book2));
        $this->assertFalse($accessibleBooks->contains($book3));
    }

    /** @test */
    public function business_primary_admin_sees_all_books_as_accessible()
    {
        // Make user business primary admin
        $this->business->users()->updateExistingPivot($this->user->id, ['role' => 'primary_admin']);

        // Create additional books
        $book2 = Book::factory()->create(['business_id' => $this->business->id]);
        $book3 = Book::factory()->create(['business_id' => $this->business->id]);

        $accessibleBooks = $this->user->accessibleBooks($this->business);

        $this->assertCount(3, $accessibleBooks);
        $this->assertTrue($accessibleBooks->contains($this->book));
        $this->assertTrue($accessibleBooks->contains($book2));
        $this->assertTrue($accessibleBooks->contains($book3));
    }
}
