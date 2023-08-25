<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Author extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'about', 'photo'];

    public function followers() : BelongsToMany {
        return $this->belongsToMany(User::class, 'favourite_author');
    }

    public function books() : HasMany {
        return $this->hasMany(Book::class);
    }
}
