import assert from 'node:assert/strict';
import { test } from 'node:test';
import { createElement } from 'react';
import { renderToString, renderToStaticMarkup } from 'react-dom/server';
import { createInertiaApp } from '@inertiajs/react';
import { createServer } from 'vite';
import en from '../../resources/js/i18n/generated/php_en.json' with { type: 'json' };
import fr from '../../resources/js/i18n/generated/php_fr.json' with { type: 'json' };
import { createTranslator } from '../../resources/js/i18n/translator.ts';
import { passkeyErrorMessage } from '../../resources/js/lib/passkey-error.ts';

void test('application counts, replacement text and public passkey errors are localized safely', () => {
    const t = createTranslator(
        { fr, en },
        { fr: { laravelLocale: 'fr' }, en: { laravelLocale: 'en' } },
        'en',
        'fr',
    );
    assert.deepEqual(
        [0, 1, 2].map((count) => t.tChoice('auth.passkeys.count', count)),
        [
            '0 registered passkeys',
            '1 registered passkey',
            '2 registered passkeys',
        ],
    );
    const name = '<script>alert(1)</script>';
    const html = renderToStaticMarkup(
        createElement('p', null, t.t('auth.passkeys.delete-confirm', { name })),
    );
    assert.match(html, /&lt;script&gt;alert\(1\)&lt;\/script&gt;/);
    assert.doesNotMatch(html, /<script>/);
    const error = new Error('The passkey operation was cancelled.');
    error.name = 'UserCancelledError';
    const french = createTranslator(
        { fr, en },
        { fr: { laravelLocale: 'fr' }, en: { laravelLocale: 'en' } },
        'fr',
        'fr',
    );
    assert.equal(
        passkeyErrorMessage(error, french.t),
        'L’opération de clé d’accès a été annulée.',
    );
    assert.equal(
        passkeyErrorMessage(new Error('Erreur du serveur.'), french.t),
        'Erreur du serveur.',
    );
});

void test('real Inertia auth and account renders translate layouts, accessible controls and links per locale', async () => {
    const server = await createServer({
        configFile: false,
        server: { middlewareMode: true },
        resolve: { alias: { '@': `${process.cwd()}/resources/js` } },
    });
    try {
        const { TooltipProvider } = await server.ssrLoadModule(
            '/resources/js/components/ui/tooltip.tsx',
        );
        const { LocaleProvider } = await server.ssrLoadModule(
            '/resources/js/i18n/provider.tsx',
        );
        const { default: AuthLayout } = await server.ssrLoadModule(
            '/resources/js/layouts/auth-layout.tsx',
        );
        const { default: AppLayout } = await server.ssrLoadModule(
            '/resources/js/layouts/app-layout.tsx',
        );
        const { default: SettingsLayout } = await server.ssrLoadModule(
            '/resources/js/layouts/settings/layout.tsx',
        );
        for (const locale of ['en', 'fr', 'en']) {
            for (const name of [
                'auth/login',
                'auth/register',
                'auth/forgot-password',
                'auth/reset-password',
                'auth/confirm-password',
                'auth/two-factor-challenge',
                'auth/verify-email',
                'settings/profile',
                'settings/security',
                'settings/appearance',
                'dashboard',
            ]) {
                const { default: Page } = await server.ssrLoadModule(
                    `/resources/js/pages/${name}.tsx`,
                );
                const auth = name.startsWith('auth/');
                const response = await createInertiaApp({
                    page: {
                        component: name,
                        props: {
                            locale,
                            games: [],
                            localizedUrls: {
                                fr: '/fr/connexion',
                                en: '/en/login',
                            },
                            availableLocales: [
                                { code: 'fr', label: 'Français' },
                                { code: 'en', label: 'English' },
                            ],
                            routeKey: 'login',
                            canResetPassword: true,
                            canRegister: true,
                            token: 'token',
                            email: 'person@example.com',
                            status: '',
                            mustVerifyEmail: true,
                            canManagePasskeys: true,
                            canManageTwoFactor: true,
                            twoFactorEnabled: false,
                            requiresConfirmation: true,
                            passkeys: [],
                            sidebarOpen: true,
                            auth: {
                                user: {
                                    id: 1,
                                    name: '<script>alert(1)</script>',
                                    email: 'person@example.com',
                                    email_verified_at: null,
                                },
                            },
                            errors: {},
                        },
                        url: `/${locale}/login`,
                        version: null,
                        clearHistory: false,
                        encryptHistory: false,
                    },
                    render: renderToString,
                    resolve: () => Page,
                    layout: () =>
                        auth
                            ? [LocaleProvider, AuthLayout]
                            : name.startsWith('settings/')
                              ? [LocaleProvider, AppLayout, SettingsLayout]
                              : [LocaleProvider, AppLayout],
                    setup: ({ App, props }) =>
                        createElement(
                            TooltipProvider,
                            null,
                            createElement(App, props),
                        ),
                });
                assert.ok(response && typeof response === 'object');
                assert.doesNotMatch(
                    response.body,
                    /<script>alert\(1\)<\/script>/,
                );
                assert.doesNotMatch(
                    response.body,
                    /(?:auth|account|common)\.[a-z-]+\.[a-z-]+/,
                );
                if (name === 'auth/login') {
                    assert.match(
                        response.body,
                        new RegExp(
                            `action="/${locale}/${locale === 'en' ? 'login' : 'connexion'}"`,
                        ),
                    );
                    assert.match(
                        response.body,
                        new RegExp(
                            `href="/${locale}/${locale === 'en' ? 'register' : 'inscription'}"`,
                        ),
                    );
                    assert.match(
                        response.body,
                        new RegExp(
                            `aria-label="${locale === 'en' ? 'Show password' : 'Afficher le mot de passe'}"`,
                        ),
                    );
                    assert.match(
                        response.body,
                        new RegExp(
                            `aria-label="${locale === 'en' ? 'Site language' : 'Langue du site'}"`,
                        ),
                    );
                }
                if (name === 'settings/security') {
                    assert.match(
                        response.body,
                        new RegExp(
                            locale === 'en'
                                ? 'Two-factor authentication'
                                : 'Double authentification',
                        ),
                    );
                    assert.match(
                        response.body,
                        new RegExp(
                            locale === 'en'
                                ? '0 registered passkeys'
                                : '0 clé enregistrée',
                        ),
                    );
                }
                if (!auth)
                    assert.match(
                        response.body,
                        new RegExp(
                            `href="/${locale}/${locale === 'en' ? 'settings/profile' : 'parametres/profil'}"`,
                        ),
                    );
            }
        }
    } finally {
        await server.close();
    }
});
