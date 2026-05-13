<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'book_id'     => Book::factory(),
            'path'        => 'images/' . fake()->uuid() . '.jpg',
            'description' => fake()->optional()->sentence(),
        ];
    }
}
