<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\User;
use App\Models\WaitingList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_available_book_with_no_waitlist_yields_available(): void
    {
        $book = Book::factory()->create(['availability' => 1]);

        $this->assertEquals('Available', $book->availabilityLabel());
        $this->assertEquals('Available', $book->availability_label);
    }

    public function test_borrowed_book_with_no_waitlist_yields_borrowed(): void
    {
        $book = Book::factory()->create(['availability' => 0]);

        $this->assertEquals('Borrowed', $book->availabilityLabel());
    }

    public function test_borrowed_book_with_active_waitlist_yields_on_waitlist(): void
    {
        $book = Book::factory()->create(['availability' => 0]);
        WaitingList::factory()->create(['book_id' => $book->id]);

        $book->load('reservations');

        $this->assertEquals('On Waitlist', $book->availabilityLabel());
    }

    public function test_availability_2_yields_lost(): void
    {
        $book = Book::factory()->create(['availability' => 2]);

        $this->assertEquals('Lost', $book->availabilityLabel());
    }

    public function test_availability_3_yields_broken(): void
    {
        $book = Book::factory()->create(['availability' => 3]);

        $this->assertEquals('Broken', $book->availabilityLabel());
    }

    public function test_availability_label_appended_in_json(): void
    {
        $book = Book::factory()->create(['availability' => 1]);

        $array = $book->toArray();

        $this->assertArrayHasKey('availability_label', $array);
        $this->assertEquals('Available', $array['availability_label']);
    }
}
