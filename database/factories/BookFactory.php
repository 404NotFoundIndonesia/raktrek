<?php

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'page_number' => fake()->numberBetween(50, 800),
            'synopsis' => fake()->paragraph(),
            'publication_year' => fake()->year(),
            'publisher' => fake()->company(),
            'language' => fake()->randomElement(['English', 'Indonesian', 'French']),
            'author_id' => Author::factory(),
            'availability' => 1,
        ];
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'availability' => 0,
        ]);
    }
}
