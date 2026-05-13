<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'page_number', 'synopsis', 'publication_year',
        'publisher', 'language', 'author_id', 'availability',
    ];

    protected $appends = ['availability_label'];

    public function author() : BelongsTo {
        return $this->belongsTo(Author::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'book_genre');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(BookImage::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(WaitingList::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    public function averageRating(): float
    {
        return round($this->reviews()->avg('rate') ?? 0, 1);
    }

    public function getAvailabilityLabelAttribute(): string
    {
        return $this->availabilityLabel();
    }

    public function availabilityLabel(): string
    {
        if ($this->availability !== 0) {
            return match ($this->availability) {
                2 => 'Lost',
                3 => 'Broken',
                default => 'Available',
            };
        }

        $hasWaitlist = $this->relationLoaded('reservations')
            ? $this->reservations->isNotEmpty()
            : $this->reservations()->exists();

        return $hasWaitlist ? 'On Waitlist' : 'Borrowed';
    }

}
