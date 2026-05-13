<?php

namespace App\Notifications;

use App\Models\Book;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBookByFavouriteAuthorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Book $book) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->wantsEmailNotification('new_book_email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $authorName = $this->book->author->name ?? 'a favourite author';

        return (new MailMessage)
            ->subject("New book by {$authorName}: '{$this->book->title}'")
            ->line("{$authorName} just added a new book: '{$this->book->title}'.")
            ->action('View Book', url("/books/{$this->book->id}"))
            ->line('Check it out in the catalogue.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'book_id'     => $this->book->id,
            'book_title'  => $this->book->title,
            'author_name' => $this->book->author->name ?? null,
            'message'     => "New book by a favourite author: '{$this->book->title}'.",
            'link'        => "/books/{$this->book->id}",
        ];
    }
}
