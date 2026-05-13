<?php

namespace Tests\Feature\Book;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_books_index_returns_paginated_list(): void
    {
        Book::factory()->count(20)->create();

        $response = $this->get('/books');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Books/Index')
                 ->has('books.data', 15)
                 ->has('books.total')
                 ->has('filters')
        );
    }

    public function test_search_filters_by_title(): void
    {
        Book::factory()->create(['title' => 'Laravel Deep Dive']);
        Book::factory()->count(5)->create(['title' => 'Unrelated Book Title']);

        $response = $this->get('/books?search=Laravel');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Books/Index')
                 ->has('books.data', 1)
                 ->where('books.data.0.title', 'Laravel Deep Dive')
        );
    }

    public function test_genre_filter_returns_only_books_in_that_genre(): void
    {
        $genre = Genre::factory()->create();
        $book  = Book::factory()->create();
        $book->genres()->attach($genre);

        Book::factory()->count(3)->create();

        $response = $this->get("/books?genre={$genre->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Books/Index')
                 ->has('books.data', 1)
                 ->where('books.data.0.id', $book->id)
        );
    }

    public function test_availability_filter_returns_only_matching_books(): void
    {
        Book::factory()->create(['availability' => 1]);
        Book::factory()->create(['availability' => 0]);

        $response = $this->get('/books?availability=1');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Books/Index')
                 ->has('books.data', 1)
                 ->where('books.data.0.availability', 1)
        );
    }

    public function test_sort_by_rating_orders_correctly(): void
    {
        $low  = Book::factory()->create();
        $high = Book::factory()->create();

        Review::factory()->create(['book_id' => $low->id, 'rate' => 2]);
        Review::factory()->create(['book_id' => $high->id, 'rate' => 5]);

        $response = $this->get('/books?sort=rating');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Books/Index')
                 ->where('books.data.0.id', $high->id)
        );
    }

    public function test_eager_loaded_relations_present_in_response(): void
    {
        $author = Author::factory()->create();
        $book   = Book::factory()->create(['author_id' => $author->id]);

        $response = $this->get('/books');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('books.data.0.author')
                 ->has('books.data.0.genres')
        );
    }

    public function test_pagination_returns_second_page(): void
    {
        Book::factory()->count(20)->create();

        $response = $this->get('/books?page=2');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('books.current_page', 2)
        );
    }

    public function test_genres_list_sent_to_view(): void
    {
        Genre::factory()->count(3)->create();

        $response = $this->get('/books');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('genres', 3)
        );
    }
}
