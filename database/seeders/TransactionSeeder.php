<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Review;
use App\Models\User;
use App\Models\WaitingList;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $members = $this->seedMembers();
        $books   = Book::all();

        if ($books->isEmpty()) {
            $this->command->warn('No books found. Run MasterDataSeeder first.');
            return;
        }

        $this->seedBorrowings($members, $books);
        $this->seedReviews($members, $books);
        $this->seedWaitlists($members, $books);
    }

    private function seedMembers(): \Illuminate\Support\Collection
    {
        $memberData = [
            ['name' => 'Budi Santoso',    'email' => 'budi@example.com',    'phone' => '081111111111', 'address' => 'Jl. Merdeka No. 10, Bandung'],
            ['name' => 'Siti Rahayu',     'email' => 'siti@example.com',    'phone' => '082222222222', 'address' => 'Jl. Sudirman No. 45, Surabaya'],
            ['name' => 'Ahmad Fauzi',     'email' => 'ahmad@example.com',   'phone' => '083333333333', 'address' => 'Jl. Diponegoro No. 7, Yogyakarta'],
            ['name' => 'Dewi Kusuma',     'email' => 'dewi@example.com',    'phone' => '084444444444', 'address' => 'Jl. Gatot Subroto No. 22, Jakarta'],
            ['name' => 'Reza Pratama',    'email' => 'reza@example.com',    'phone' => '085555555555', 'address' => 'Jl. Ahmad Yani No. 3, Malang'],
            ['name' => 'Nur Indah',       'email' => 'indah@example.com',   'phone' => '086666666666', 'address' => 'Jl. Pahlawan No. 18, Semarang'],
            ['name' => 'Hendra Wijaya',   'email' => 'hendra@example.com',  'phone' => '087777777777', 'address' => 'Jl. Imam Bonjol No. 5, Medan'],
            ['name' => 'Rina Fitriani',   'email' => 'rina@example.com',    'phone' => '088888888888', 'address' => 'Jl. Cut Nyak Dien No. 9, Makassar'],
        ];

        $members = collect();
        foreach ($memberData as $data) {
            $members->push(User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                    'role'     => 'member',
                ])
            ));
        }
        return $members;
    }

    private function seedBorrowings(\Illuminate\Support\Collection $members, \Illuminate\Support\Collection $books): void
    {
        $scenarios = [
            // [user_index, book_index, borrowed_days_ago, due_days_from_borrow, returned]
            [0, 0,  30, 14, true],   // returned on time
            [0, 2,  20, 14, true],   // returned on time
            [0, 4,   5, 14, false],  // currently borrowed
            [1, 1,  25, 14, true],   // returned on time
            [1, 3,  10, 14, false],  // currently borrowed
            [1, 6,  40, 14, true],   // returned late
            [2, 5,  15, 14, true],   // returned on time
            [2, 7,   8, 14, false],  // currently borrowed
            [3, 8,  35, 14, true],   // returned on time
            [3, 10, 12, 14, false],  // currently borrowed
            [4, 9,  50, 14, true],   // returned on time
            [4, 11,  3, 14, false],  // currently borrowed (recent)
            [5, 12, 22, 14, true],   // returned on time
            [5, 13,  7, 14, false],  // currently borrowed
            [6, 14, 45, 14, true],   // returned on time
            [6, 0,  60, 14, true],   // returned, old history
            [7, 15,  9, 14, false],  // currently borrowed
            [7, 2,  33, 14, true],   // returned on time
        ];

        foreach ($scenarios as [$uIdx, $bIdx, $daysAgo, $dueDays, $returned]) {
            $user = $members->get($uIdx);
            $book = $books->get($bIdx % $books->count());

            if (! $user || ! $book) {
                continue;
            }

            $borrowedAt = Carbon::now()->subDays($daysAgo);
            $dueDate    = $borrowedAt->copy()->addDays($dueDays);
            $returnDate = null;

            if ($returned) {
                $returnDate = $dueDate->copy()->subDays(rand(0, 3));
            }

            Borrowing::firstOrCreate(
                ['user_id' => $user->id, 'book_id' => $book->id, 'created_at' => $borrowedAt],
                [
                    'due_date'    => $dueDate,
                    'return_date' => $returnDate,
                    'updated_at'  => $borrowedAt,
                ]
            );
        }

        // Mark books with active borrowings as unavailable
        $activeBorrowedBookIds = Borrowing::whereNull('return_date')->pluck('book_id');
        Book::whereIn('id', $activeBorrowedBookIds)->update(['availability' => 0]);
    }

    private function seedReviews(\Illuminate\Support\Collection $members, \Illuminate\Support\Collection $books): void
    {
        $reviews = [
            [0, 0,  5.0, 'A magical start to an incredible series. Rowling\'s world-building is second to none.'],
            [0, 2,  5.0, 'A chilling and prophetic masterpiece. Everyone should read this.'],
            [1, 1,  4.5, 'Thought-provoking satire. Short but deeply impactful.'],
            [1, 6,  5.0, 'García Márquez weaves a spell unlike any other. A lifetime of story in one book.'],
            [2, 5,  4.5, 'Tolkien is unmatched in creating a fully realised mythological world.'],
            [2, 7,  4.0, 'A brilliantly plotted mystery. Agatha Christie at her best.'],
            [3, 8,  4.5, 'Timeless romance and sharp social commentary. Austen never disappoints.'],
            [3, 10, 5.0, 'Pramoedya\'s magnum opus. A vital piece of Indonesian literary history.'],
            [4, 9,  4.5, 'A beautiful, lyrical story. The characters feel incredibly real.'],
            [4, 11, 5.0, 'Pramoedya continues to shine. A must-read for anyone interested in colonial history.'],
            [5, 12, 5.0, 'Laskar Pelangi is one of the most heartwarming stories I have ever read.'],
            [5, 4,  5.0, 'A powerful and courageous novel. Atticus Finch is one of literature\'s great heroes.'],
            [6, 14, 4.0, 'Tere Liye writes with such warmth and humanity. A deeply moving story.'],
            [7, 13, 4.5, 'A perfect companion to Laskar Pelangi. The dream sequences are beautifully written.'],
            [7, 2,  4.0, 'Orwell\'s vision of the future feels uncomfortably relevant today.'],
        ];

        foreach ($reviews as [$uIdx, $bIdx, $rating, $comment]) {
            $user = $members->get($uIdx);
            $book = $books->get($bIdx % $books->count());

            if (! $user || ! $book) {
                continue;
            }

            Review::firstOrCreate(
                ['user_id' => $user->id, 'book_id' => $book->id],
                ['rate' => $rating, 'comment' => $comment]
            );
        }
    }

    private function seedWaitlists(\Illuminate\Support\Collection $members, \Illuminate\Support\Collection $books): void
    {
        // Only queue for books that are currently borrowed (unavailable)
        $borrowedBookIds = Borrowing::whereNull('return_date')->pluck('book_id')->unique();

        $queue = [
            // [user_index, borrowed_book_position_in_list]
            [1, 0],
            [3, 1],
            [5, 0],
            [6, 1],
            [7, 2],
        ];

        foreach ($queue as [$uIdx, $bookPos]) {
            $user   = $members->get($uIdx);
            $bookId = $borrowedBookIds->get($bookPos);

            if (! $user || ! $bookId) {
                continue;
            }

            WaitingList::firstOrCreate(
                ['user_id' => $user->id, 'book_id' => $bookId],
                ['finish_date' => null]
            );
        }
    }
}
