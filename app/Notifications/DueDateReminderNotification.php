<?php

namespace App\Notifications;

use App\Models\Borrowing;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DueDateReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Borrowing $borrowing) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->wantsEmailNotification('due_date_reminder_email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $dueDate = $this->borrowing->due_date->format('d M Y');
        $title   = $this->borrowing->book->title ?? 'a book';

        return (new MailMessage)
            ->subject("Reminder: '{$title}' is due on {$dueDate}")
            ->line("This is a reminder that your loan of '{$title}' is due on {$dueDate}.")
            ->action('View Book', url("/books/{$this->borrowing->book_id}"))
            ->line('Please return or renew it before the due date to avoid overdue fees.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'borrowing_id' => $this->borrowing->id,
            'book_id'      => $this->borrowing->book_id,
            'book_title'   => $this->borrowing->book->title ?? null,
            'due_date'     => $this->borrowing->due_date->toDateString(),
            'message'      => "Your loan of '{$this->borrowing->book->title}' is due on {$this->borrowing->due_date->format('d M Y')}.",
            'link'         => "/books/{$this->borrowing->book_id}",
        ];
    }
}
