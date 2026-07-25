<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'currency',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    // Book access helper methods
    public function userHasAccess(User $user): bool
    {
        return $user->canViewBook($this);
    }

    public function userCanEdit(User $user): bool
    {
        return $user->canEditBook($this);
    }

    public function userCanManage(User $user): bool
    {
        return $user->canManageBook($this);
    }
}
