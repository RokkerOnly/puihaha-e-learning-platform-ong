<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipTransactionSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public string $memberName,
        public string $referenceNo,
        public string $transactionType,
        public string $levelName,
        public string $amountDue,
    ) {
        $this->afterCommit();
    }

    public function backoff(): array
    {
        return [60, 300];
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->transactionType === 'renewal'
            ? 'renewal'
            : 'application';

        return (new MailMessage)
            ->subject("Puihaha membership {$type} received")
            ->greeting("Hello {$this->memberName},")
            ->line("Your membership {$type} has been recorded.")
            ->line("Reference number: {$this->referenceNo}")
            ->line("Membership level: {$this->levelName}")
            ->line(
                'Amount due: PHP ' . number_format(
                    (float) $this->amountDue,
                    2
                )
            )
            ->line('Initial status: Pending.')
            ->line('Membership approval is processed separately after the balance is settled.')
            ->line('Please keep this reference number for your records.')
            ->salutation('Puihaha E-Learning Platform');
    }
}