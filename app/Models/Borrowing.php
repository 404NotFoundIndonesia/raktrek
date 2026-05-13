<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Borrowing extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'book_id', 'due_date', 'return_date', 'last_overdue_notified_at'];

    protected $casts = [
        'due_date'                  => 'datetime',
        'return_date'               => 'datetime',
        'last_overdue_notified_at'  => 'datetime',
    ];

    protected $appends = ['is_overdue'];

    public function getIsOverdueAttribute(): bool
    {
        return is_null($this->return_date) && Carbon::now()->isAfter($this->due_date);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
