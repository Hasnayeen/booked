<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Operator;
use App\Models\User;
use DateInterval;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOperatorRegistration extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Operator $operator,
        public User $registeredBy,
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Operator Registration - Review Required')
            ->greeting("Hello {$notifiable->name}!")
            ->line('A new operator has registered and is awaiting approval.')
            ->line('**Registration Details:**')
            ->line("• **Operator Name:** {$this->operator->name}")
            ->line("• **Type:** {$this->operator->type->label()}")
            ->line("• **Contact Email:** {$this->operator->contact_email}")
            ->line('• **Contact Phone:** ' . ($this->operator->contact_phone ?? 'Not provided'))
            ->line("• **Registered By:** {$this->registeredBy->name} ({$this->registeredBy->email})")
            ->line('• **Registration Date:** ' . $this->operator->created_at->format('M j, Y \a\t g:i A'))
            ->when($this->operator->description, fn ($message) => $message
                ->line('**Description:**')
                ->line($this->operator->description))
            ->line('Please review this registration and update the operator status accordingly.')
            ->action('Review Operator', url("/admin/operators/{$this->operator->id}"))
            ->line('This operator cannot access the platform until approved.')
            ->salutation('The Booked System');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'operator_id' => $this->operator->id,
            'operator_name' => $this->operator->name,
            'operator_type' => $this->operator->type->value,
            'contact_email' => $this->operator->contact_email,
            'registered_by' => [
                'id' => $this->registeredBy->id,
                'name' => $this->registeredBy->name,
                'email' => $this->registeredBy->email,
            ],
            'registration_date' => $this->operator->created_at->toISOString(),
            'message' => "New {$this->operator->type->label()} operator '{$this->operator->name}' requires review.",
        ];
    }

    /**
     * Get the notification's delivery delay.
     */
    public function delay(): DateTime|DateInterval|null
    {
        // Delay sending by 1 minute to allow database transaction to complete
        return now()->addMinutes(1);
    }
}
