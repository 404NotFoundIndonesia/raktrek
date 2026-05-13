<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class WaitingList extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'book_id', 'finish_date'];

    protected $casts = [
        'finish_date' => 'datetime',
    ];

    protected $appends = ['is_expired'];

    public function getIsExpiredAttribute(): bool
    {
        return $this->finish_date !== null && Carbon::now()->isAfter($this->finish_date);
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
