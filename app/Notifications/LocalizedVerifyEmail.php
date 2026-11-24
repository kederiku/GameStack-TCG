<?php

namespace App\Notifications;

use App\Support\Localization\Locales;
use App\Support\Localization\LocalizedRoutes;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Traits\Localizable;

final class LocalizedVerifyEmail extends VerifyEmail
{
    use Localizable;

    public function __construct(public readonly string $localeCode) {}

    public function toMail($notifiable): MailMessage
    {
        return $this->withLocale($this->locale ?? Locales::definition($this->localeCode)['laravelLocale'], function () use ($notifiable): MailMessage {
            $url = LocalizedRoutes::temporarySignedUrl('verification.verify', now()->addMinutes(config('auth.verification.expire', 60)), [
                'id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification()),
            ], $this->localeCode);

            return (new MailMessage)->subject(__('notifications.verify-email.subject'))
                ->line(__('notifications.verify-email.intro'))
                ->action(__('notifications.verify-email.action'), $url)
                ->line(__('notifications.verify-email.outro'));
        });
    }
}
