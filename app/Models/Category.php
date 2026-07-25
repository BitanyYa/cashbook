<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'type',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
