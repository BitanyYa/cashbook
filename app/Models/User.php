<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_preferences' => 'array',
        ];
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function books()
    {
        return $this->belongsToMany(Book::class)->withPivot('role')->withTimestamps();
    }

    // Notification preferences helpers
    public function wantsNotification(string $key): bool
    {
        $prefs = $this->notification_preferences ?? [];
        // Default to true if not set
        return ($prefs[$key] ?? true) === true;
    }

    public function setNotificationPref(string $key, bool $value): void
    {
        $prefs = $this->notification_preferences ?? [];
        $prefs[$key] = $value;
        $this->notification_preferences = $prefs;
        $this->save();
    }

    // Book role helper methods
    public function getBusinessRole(?Business $business): ?string
    {
        if (!$business) {
            return null;
        }
        return $this->businesses()->where('business_id', $business->id)->value('business_user.role');
    }

    public function getBookRole(?Book $book): ?string
    {
        if (!$book) {
            return null;
        }
        
        $businessRole = $book->business_id ? $this->getBusinessRole($book->business) : null;
        if (in_array($businessRole, ['primary_admin', 'admin'])) {
            return $businessRole;
        }

        // Fall back to the explicit book_user pivot role.
        return $this->books()->where('book_id', $book->id)->value('book_user.role');
    }

    public function canViewBook(?Book $book): bool
    {
        if (!$book) return false;
        return $this->getBookRole($book) !== null;
    }

    public function canEditBook(?Book $book): bool
    {
        if (!$book) return false;
        $role = $this->getBookRole($book);
        return in_array($role, ['primary_admin', 'admin', 'employee']);
    }

    public function canManageBook(?Book $book): bool
    {
        if (!$book) return false;
        $role = $this->getBookRole($book);
        return in_array($role, ['primary_admin', 'admin']);
    }

    public function getUserBookRole(?Book $book): ?string
    {
        if (!$book) return null;
        $user = $this->books()->where('book_id', $book->id)->first();
        return $user ? $user->pivot->role : null;
    }

    public function accessibleBooks(?Business $business)
    {
        if (!$business) {
            return collect();
        }

        // If user is a primary admin or admin, return all books
        $businessRole = $this->getBusinessRole($business);
        if (in_array($businessRole, ['primary_admin', 'admin'])) {
            return $business->books;
        }

        // Return only books the user has explicit access to
        return $business->books()->whereHas('users', function ($query) {
            $query->where('user_id', $this->id);
        })->get();
    }
}
