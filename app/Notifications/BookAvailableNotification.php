<?php

namespace App\Notifications;

use App\Models\Book;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookAvailableNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Book $book) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->wantsEmailNotification('book_available_email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("'{$this->book->title}' is now available")
            ->line("Good news! '{$this->book->title}' is now available for borrowing.")
            ->action('Borrow Now', url("/books/{$this->book->id}"))
            ->line('Visit the library or borrow it online.');
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
