<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'role',
        'google_id',
        'notification_preferences',
    ];

    protected $attributes = [
        'role' => 'member',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at'        => 'datetime',
        'password'                 => 'hashed',
        'notification_preferences' => 'array',
    ];

    public function wantsEmailNotification(string $type): bool
    {
        $prefs = $this->notification_preferences ?? [];
        return $prefs[$type] ?? true;
    }

    public function favouriteAuthors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'favourite_author');
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
