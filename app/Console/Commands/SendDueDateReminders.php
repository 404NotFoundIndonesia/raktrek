<?php

namespace App\Console\Commands;

use App\Models\Borrowing;
use App\Notifications\DueDateReminderNotification;
use Illuminate\Console\Command;

class SendDueDateReminders extends Command
{
    protected $signature   = 'notifications:send-due-date-reminders';
    protected $description = 'Send due date reminder notifications for borrowings due in 3 days';

    public function handle(): int
    {
        $target = now()->addDays(3)->toDateString();

        $borrowings = Borrowing::with(['user', 'book'])
            ->whereNull('return_date')
            ->whereDate('due_date', $target)
            ->get();

        foreach ($borrowings as $borrowing) {
            $borrowing->user->notify(new DueDateReminderNotification($borrowing));
        }

        $this->info("Sent {$borrowings->count()} due date reminder(s).");

        return self::SUCCESS;
    }
}
