<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WaitingListFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'book_id'     => Book::factory(),
            'finish_date' => now()->addDays(7),
        ];
    }
}
