<?php

namespace Tests\Feature\Localization;

use App\Filament\Support\Catalog\CatalogLabels;
use App\Models\User;
use App\Notifications\LocalizedResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Mail\Markdown;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Passkeys\Exceptions\InvalidPasskeyException;
use Tests\TestCase;

class LocalizedCopyTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_login_title_and_links_follow_the_url(): void
    {
        $this->get('/en/login')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('auth/login')->where('locale', 'en')->where('routeKey', 'login'));
        $this->assertSame('Log in', __('auth.login.title', [], 'en'));
        $this->get('/en/connexion')->assertNotFound();
        $this->postJson('/en/register', [])->assertUnprocessable()->assertJsonPath('errors.name.0', 'The name field is required.');
    }

    public function test_admin_help_and_business_labels_are_resolved_per_request(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/en/admin/games/create')->assertOk()->assertSee('Show on site')->assertSee('Presentation');
        app()->setLocale('en');
        $this->assertSame('Main expansion', CatalogLabels::expansionTypes()['main']);
        app()->setLocale('fr');
        $this->assertSame('Extension principale', CatalogLabels::expansionTypes()['main']);
    }

    public function test_vendor_literal_errors_use_php_backed_french_messages(): void
    {
        $credential = ['credential' => ['id' => 'invalid', 'rawId' => 'invalid', 'type' => 'public-key', 'response' => ['invalid' => true]]];
        $this->postJson('/fr/cles-acces/connexion', $credential)->assertUnprocessable()->assertJsonPath('errors.credential.0', 'Le format de la clé d’accès est invalide.');
        $this->postJson('/en/passkeys/login', $credential)->assertUnprocessable()->assertJsonPath('errors.credential.0', 'Invalid credential format.');
        app()->setLocale('fr');
        $this->assertSame('Le code d’authentification à deux facteurs est invalide.', __('The provided two factor authentication code was invalid.'));
        $this->assertSame(['credential' => ['Impossible d’enregistrer cette clé d’accès.']], InvalidPasskeyException::make('Unable to register this passkey.')->errors());
        app()->setLocale('en');
        $this->assertSame('Unable to register this passkey.', __('Unable to register this passkey.'));
    }

    public function test_french_validation_summary_and_account_notifications_are_localized(): void
    {
        $response = $this->postJson('/fr/inscription', []);
        $response->assertUnprocessable();
        $this->assertStringContainsString('autres erreurs', $response->json('message'));
        $this->assertStringNotContainsString('more errors', $response->json('message'));
        $user = User::factory()->create();
        $this->actingAs($user)->patch('/en/settings/profile', ['name' => 'Updated', 'email' => $user->email])->assertRedirect();
        $this->assertSame('Profile updated.', __('account.messages.profile-updated', [], 'en'));
        $this->assertSame('Profil mis à jour.', __('account.messages.profile-updated', [], 'fr'));
    }

    public function test_error_pages_use_the_requested_interface_language(): void
    {
        config(['app.debug' => false]);
        $this->get('/en/page-that-does-not-exist')->assertNotFound()->assertSee('Page not found');
        $this->get('/fr/page-qui-n-existe-pas')->assertNotFound()->assertSee('Page introuvable');
        foreach ([403, 419, 429, 500, 503] as $status) {
            $request = Request::create('/en/example');
            $this->app->instance('request', $request);
            $html = view('errors.'.$status)->render();
            $this->assertStringContainsString('lang="en"', $html);
            $this->assertStringNotContainsString('common.errors.', $html);
        }
    }

    public function test_complete_markdown_email_shell_is_localized_in_html_and_text(): void
    {
        foreach (['fr' => ['Bonjour !', 'Cordialement,', 'Tous droits réservés.', 'Si vous ne parvenez pas'], 'en' => ['Hello!', 'Regards,', 'All rights reserved.', 'If you’re having trouble']] as $locale => $expected) {
            app()->setLocale($locale);
            $user = User::factory()->create(['preferred_locale' => $locale]);
            $mail = (new LocalizedResetPassword('token', $locale))->toMail($user);
            foreach (['render', 'renderText'] as $method) {
                $rendered = (string) app(Markdown::class)->{$method}($mail->markdown, $mail->data());
                foreach ($expected as $text) {
                    $this->assertStringContainsString($text, html_entity_decode($rendered));
                }
            }
        }
    }

    public function test_email_shell_escapes_translated_greetings_and_salutation(): void
    {
        app()->setLocale('en');
        app('translator')->addLines([
            'notifications.mail.greeting' => '<img src=x onerror="alert(1)">Hello',
            'notifications.mail.error' => '<img src=x onerror="alert(2)">Oops',
            'notifications.mail.salutation' => '<img src=x onerror="alert(3)">Regards',
        ], 'en');
        $mail = (new LocalizedResetPassword('token', 'en'))->toMail(User::factory()->create());
        foreach (['info' => 'Hello', 'error' => 'Oops'] as $level => $greeting) {
            $mail->level($level);
            $html = (string) app(Markdown::class)->render($mail->markdown, $mail->data());
            $this->assertStringNotContainsString('<img src="x"', $html);
            $this->assertStringNotContainsString('<img src=x', $html);
            $this->assertStringContainsString('&lt;img src=x onerror=', $html);
            $this->assertStringContainsString('&gt;'.$greeting, $html);
            $this->assertStringContainsString('&gt;Regards', $html);
        }
    }
}
