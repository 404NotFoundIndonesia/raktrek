<?php

namespace App\Notifications;

use App\Models\Borrowing;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueAlertNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Borrowing $borrowing) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->wantsEmailNotification('overdue_alert_email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $dueDate = $this->borrowing->due_date->format('d M Y');
        $title   = $this->borrowing->book->title ?? 'a book';

        return (new MailMessage)
            ->subject("Overdue: '{$title}' was due on {$dueDate}")
            ->line("Your loan of '{$title}' was due on {$dueDate} and is now overdue.")
            ->action('View Book', url("/books/{$this->borrowing->book_id}"))
            ->line('Please return the book as soon as possible.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'borrowing_id' => $this->borrowing->id,
            'book_id'      => $this->borrowing->book_id,
            'book_title'   => $this->borrowing->book->title ?? null,
            'due_date'     => $this->borrowing->due_date->toDateString(),
            'message'      => "Your loan of '{$this->borrowing->book->title}' is overdue since {$this->borrowing->due_date->format('d M Y')}.",
            'link'         => "/books/{$this->borrowing->book_id}",
        ];
    }
}
