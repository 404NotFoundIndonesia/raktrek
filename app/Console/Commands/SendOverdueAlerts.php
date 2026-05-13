<?php

namespace App\Console\Commands;

use App\Models\Borrowing;
use App\Notifications\OverdueAlertNotification;
use Illuminate\Console\Command;

class SendOverdueAlerts extends Command
{
    protected $signature   = 'notifications:send-overdue-alerts';
    protected $description = 'Send overdue alert notifications for unreturned borrowings past their due date';

    public function handle(): int
    {
        $borrowings = Borrowing::with(['user', 'book'])
            ->whereNull('return_date')
            ->where('due_date', '<', now()->startOfDay())
            ->where(function ($q) {
                $q->whereNull('last_overdue_notified_at')
                  ->orWhereDate('last_overdue_notified_at', '<', today());
            })
            ->get();

        foreach ($borrowings as $borrowing) {
            $borrowing->user->notify(new OverdueAlertNotification($borrowing));
            $borrowing->update(['last_overdue_notified_at' => now()]);
        }

        $this->info("Sent {$borrowings->count()} overdue alert(s).");

        return self::SUCCESS;
    }
}
