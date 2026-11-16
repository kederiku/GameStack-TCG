<?php

namespace App\Notifications;

use App\Support\Localization\Locales;
use App\Support\Localization\LocalizedRoutes;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Traits\Localizable;

final class LocalizedResetPassword extends ResetPassword
{
    use Localizable;

    public function __construct(#[\SensitiveParameter] string $token, public readonly string $localeCode)
    {
        parent::__construct($token);
    }

    public function toMail($notifiable): MailMessage
    {
        return $this->withLocale($this->locale ?? Locales::definition($this->localeCode)['laravelLocale'], function () use ($notifiable): MailMessage {
            $url = LocalizedRoutes::url('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()], $this->localeCode, true);

            return (new MailMessage)->subject(__('notifications.reset-password.subject'))
                ->line(__('notifications.reset-password.intro'))
                ->action(__('notifications.reset-password.action'), $url)
                ->line(__('notifications.reset-password.expiry', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
                ->line(__('notifications.reset-password.outro'));
        });
    }
}
