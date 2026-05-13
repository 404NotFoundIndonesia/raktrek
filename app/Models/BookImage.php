<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookImage extends Model
{
    use HasFactory;

    protected $table = 'book_image';

    protected $fillable = ['book_id', 'path', 'description'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
