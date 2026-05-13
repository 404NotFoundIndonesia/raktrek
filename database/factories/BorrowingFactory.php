<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BorrowingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'book_id'     => Book::factory(),
            'due_date'    => now()->addDays(14),
            'return_date' => null,
        ];
    }

    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'return_date' => now(),
        ]);
    }
}
