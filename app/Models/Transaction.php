<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'book_id',
        'category_id',
        'user_id',
        'amount',
        'type',
        'mode',
        'status',
        'approved_by',
        'description',
        'contact_name',
        'transaction_date',
        'image_path',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function business() { return $this->belongsTo(Business::class); }
    public function book() { return $this->belongsTo(Book::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}
