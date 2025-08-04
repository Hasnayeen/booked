<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Operator;
use DateInterval;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OperatorRegistrationConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Operator $operator,
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
            ->subject('Operator Registration Submitted - Booked')
            ->greeting("Hello {$notifiable->name}!")
            ->line("Thank you for registering your {$this->operator->type->label()} operator '{$this->operator->name}' with Booked.")
            ->line('Your registration has been successfully submitted and is currently under review.')
            ->line('You will receive an email notification once our team has reviewed your application.')
            ->line('**Registration Details:**')
            ->line("• Operator Name: {$this->operator->name}")
            ->line("• Type: {$this->operator->type->label()}")
            ->line("• Contact Email: {$this->operator->contact_email}")
            ->line("• Status: {$this->operator->status->label()}")
            ->action('View Dashboard', url('/operator'))
            ->line('If you have any questions, please don\'t hesitate to contact our support team.')
            ->salutation('Best regards, The Booked Team');
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
            'status' => $this->operator->status->value,
            'message' => "Your {$this->operator->type->label()} operator registration has been submitted and is under review.",
        ];
    }

    /**
     * Get the notification's delivery delay.
     */
    public function delay(): DateTime|DateInterval|null
    {
        // Delay sending by 30 seconds to allow database transaction to complete
        return now()->addSeconds(30);
    }
}
