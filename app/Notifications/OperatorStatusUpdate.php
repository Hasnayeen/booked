<?php

namespace App\Notifications;

use App\Enums\OperatorStatus;
use App\Models\Operator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OperatorStatusUpdate extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Operator $operator,
        public OperatorStatus $oldStatus,
        public ?string $adminMessage = null,
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
        $message = (new MailMessage)
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your {$this->operator->type->label()} operator '{$this->operator->name}' status has been updated.");

        return match ($this->operator->status) {
            OperatorStatus::APPROVED => $this->buildApprovedMessage($message),
            OperatorStatus::SUSPENDED => $this->buildSuspendedMessage($message),
            OperatorStatus::REJECTED => $this->buildRejectedMessage($message),
            OperatorStatus::PENDING => $this->buildPendingMessage($message),
        };
    }

    /**
     * Build the approved status message.
     */
    private function buildApprovedMessage(MailMessage $message): MailMessage
    {
        return $message
            ->subject('Operator Registration Approved - Welcome to Booked!')
            ->line('🎉 **Congratulations! Your operator registration has been approved.**')
            ->line('You can now start using all features of the Booked platform.')
            ->line('**What\'s next:**')
            ->line('• Set up your operator profile and preferences')
            ->line('• Configure your booking settings')
            ->line('• Add team members to your operator account')
            ->line('• Start managing your bookings')
            ->action('Access Your Dashboard', url('/operator'))
            ->when($this->adminMessage, fn ($msg) => $msg->line("**Admin Message:** {$this->adminMessage}"))
            ->salutation('Welcome aboard! The Booked Team');
    }

    /**
     * Build the suspended status message.
     */
    private function buildSuspendedMessage(MailMessage $message): MailMessage
    {
        return $message
            ->subject('Operator Account Suspended - Action Required')
            ->line('⚠️ **Your operator account has been temporarily suspended.**')
            ->line('This means you currently cannot access your operator dashboard or manage bookings.')
            ->line('**Reason:** ' . ($this->adminMessage ?? 'Please contact support for details.'))
            ->line('**To resolve this:**')
            ->line('• Review our terms of service')
            ->line('• Contact our support team for assistance')
            ->line('• Address any outstanding issues')
            ->action('Contact Support', url('/support'))
            ->salutation('The Booked Team');
    }

    /**
     * Build the rejected status message.
     */
    private function buildRejectedMessage(MailMessage $message): MailMessage
    {
        return $message
            ->subject('Operator Registration Update - Booked')
            ->line('We have reviewed your operator registration application.')
            ->line('Unfortunately, we cannot approve your registration at this time.')
            ->line('**Reason:** ' . ($this->adminMessage ?? 'Please contact support for details.'))
            ->line('**Next steps:**')
            ->line('• Review the feedback provided')
            ->line('• Contact our support team if you have questions')
            ->line('• You may reapply after addressing the concerns')
            ->action('Contact Support', url('/support'))
            ->salutation('The Booked Team');
    }

    /**
     * Build the pending status message (for status reversal).
     */
    private function buildPendingMessage(MailMessage $message): MailMessage
    {
        return $message
            ->subject('Operator Status Updated - Under Review')
            ->line('Your operator status has been changed to "Under Review".')
            ->line('Our team will review your operator account and contact you with any updates.')
            ->when($this->adminMessage, fn ($msg) => $msg->line("**Admin Message:** {$this->adminMessage}"))
            ->line('Thank you for your patience.')
            ->salutation('The Booked Team');
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
            'old_status' => $this->oldStatus->value,
            'new_status' => $this->operator->status->value,
            'admin_message' => $this->adminMessage,
            'message' => $this->getStatusUpdateMessage(),
        ];
    }

    /**
     * Get a simple status update message for database storage.
     */
    private function getStatusUpdateMessage(): string
    {
        return match ($this->operator->status) {
            OperatorStatus::APPROVED => 'Your operator registration has been approved! Welcome to Booked.',
            OperatorStatus::SUSPENDED => 'Your operator account has been temporarily suspended.',
            OperatorStatus::REJECTED => 'Your operator registration could not be approved at this time.',
            OperatorStatus::PENDING => 'Your operator status is now under review.',
        };
    }
}
