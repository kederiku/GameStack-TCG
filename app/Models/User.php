<?php

namespace App\Models;

use App\Notifications\LocalizedResetPassword;
use App\Notifications\LocalizedVerifyEmail;
use App\Support\Localization\AdminPanels;
use App\Support\Localization\Locales;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property CarbonInterface|null $email_verified_at
 * @property string $password
 * @property string|null $preferred_locale
 * @property bool $is_admin
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property CarbonInterface|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasLocalePreference, MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    public function preferredLocale(): string
    {
        return Locales::definition($this->preferredLocaleCode())['laravelLocale'];
    }

    public function preferredLocaleCode(): string
    {
        return is_string($this->preferred_locale) && Locales::supports($this->preferred_locale)
            ? $this->preferred_locale : Locales::default();
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify((new LocalizedVerifyEmail($this->preferredLocaleCode()))->locale($this->preferredLocale()));
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify((new LocalizedResetPassword($token, $this->preferredLocaleCode()))->locale($this->preferredLocale()));
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return array_key_exists($panel->getId(), AdminPanels::all()) && $this->is_admin && $this->hasVerifiedEmail();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
