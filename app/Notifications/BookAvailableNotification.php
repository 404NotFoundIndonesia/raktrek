<?php

namespace App\Notifications;

use App\Models\Book;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookAvailableNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Book $book) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'book_id'    => $this->book->id,
            'book_title' => $this->book->title,
            'message'    => "'{$this->book->title}' is now available.",
            'link'       => "/books/{$this->book->id}",
        ];
    }
}
