<?php

namespace Tests\Feature\Localization;

use App\Models\User;
use App\Notifications\LocalizedResetPassword;
use App\Notifications\LocalizedVerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\Concerns\ForwardOnlyRefreshDatabase;
use Tests\TestCase;

class LocalizedNotificationsTest extends TestCase
{
    use ForwardOnlyRefreshDatabase;

    public function test_notifications_use_recipient_locale_and_localized_signed_urls(): void
    {
        Notification::fake();
        foreach (['fr', 'en'] as $locale) {
            $user = User::factory()->unverified()->create(['preferred_locale' => $locale]);
            $user->sendEmailVerificationNotification();
            $user->sendPasswordResetNotification('reset-token');
            $sent = Notification::sent($user, LocalizedVerifyEmail::class)->first();
            $this->assertInstanceOf(VerifyEmail::class, $sent);
            $this->assertSame($locale, $sent->locale);
            app()->setLocale($locale === 'en' ? 'fr' : 'en');
            $previous = app()->getLocale();
            $serialized = unserialize(serialize($sent));
            $mail = $serialized->toMail($user);
            $this->assertSame($previous, app()->getLocale());
            $this->assertSame($locale === 'en' ? 'Verify your email address' : 'Vérification de votre adresse e-mail', $mail->subject);
            $this->assertStringContainsString('/'.$locale.'/email/', $mail->actionUrl);
            $this->assertTrue(URL::hasValidSignature(Request::create($mail->actionUrl)));
            $reset = Notification::sent($user, LocalizedResetPassword::class)->first();
            $this->assertInstanceOf(ResetPassword::class, $reset);
            $this->assertStringContainsString('/'.$locale.'/', $reset->toMail($user)->actionUrl);
            $this->assertStringContainsString('reset-token', $reset->toMail($user)->actionUrl);
        }
    }

    public function test_legacy_signature_is_validated_on_original_url_and_tampering_fails(): void
    {
        foreach (['verification.verify', 'site.en.verification.verify'] as $name) {
            $user = User::factory()->unverified()->create(['preferred_locale' => 'en']);
            $url = URL::temporarySignedRoute($name, now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);
            $this->actingAs($user)->get($url.'&extra=1')->assertForbidden();
            $this->get(str_replace('/'.$user->id.'/', '/999999/', $url))->assertForbidden();
            $this->get(str_replace('/email/', '/changed/', $url))->assertNotFound();
            $this->assertFalse($user->fresh()->hasVerifiedEmail());
            if ($name === 'site.en.verification.verify') {
                $this->get(str_replace('/en/email/verify/', '/fr/email/verifier/', $url))->assertForbidden();
            }
            $this->get($url)->assertRedirect('/en/dashboard?verified=1');
            $this->assertTrue($user->fresh()->hasVerifiedEmail());
        }
    }

    public function test_invalid_signature_is_rejected_before_guest_login_redirect(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->get($url.'&changed=1')->assertForbidden();
        $this->get($url)->assertRedirect('/fr/connexion')->assertSessionHas('url.intended', $url);
    }
}
