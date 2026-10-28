<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent on request from the profile page to check that system mail is delivered.
 *
 * Deliberately not queued: a broken mail configuration should fail in front of the user,
 * not silently in a worker.
 */
class TestMailNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__(':app test mail', ['app' => config('app.name')]))
            ->line(__('This is a test mail from :app.', ['app' => config('app.name')]))
            ->line(__('If you can read this, the system is able to deliver mail to you.'))
            ->line(__('Sent at :time.', ['time' => now()->toDateTimeString()]));
    }
}
