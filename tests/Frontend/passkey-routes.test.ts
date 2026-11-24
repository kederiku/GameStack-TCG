import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { mkdtempSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { test } from 'node:test';
import { createElement } from 'react';
import { renderToString } from 'react-dom/server';
import { createInertiaApp, router } from '@inertiajs/react';
import { createServer, type ViteDevServer } from 'vite';

async function renderAuthPage(
    server: ViteDevServer,
    name: string,
    path: string,
) {
    const { default: Page } = await server.ssrLoadModule(
        '/resources/js/pages/auth/' + name + '.tsx',
    );
    const { LocaleProvider } = await server.ssrLoadModule(
        '/resources/js/i18n/provider.tsx',
    );
    const response = await createInertiaApp({
        page: {
            component: name,
            props: { locale: 'en', errors: {} },
            url: '/en/' + path,
            version: null,
            clearHistory: false,
            encryptHistory: false,
        },
        render: renderToString,
        resolve: () => Page,
        layout: () => [LocaleProvider],
        setup: ({ App, props }) => createElement(App, props),
    });
    assert.ok(response && typeof response === 'object');
    return response.body;
}

void test('password login and confirmation render and typecheck against a real passkey-disabled route export', async () => {
    const root = mkdtempSync(join(tmpdir(), 'gamestackr-passkeys-'));
    const source = join(process.cwd(), 'resources/js');
    const aliases = {
        '@/i18n/generated': join(root, 'i18n/generated'),
        '@/routes': join(root, 'routes'),
        '@': source,
    };
    const server = await createServer({
        configFile: false,
        cacheDir: join(root, 'vite-cache'),
        server: { middlewareMode: true, ws: false },
        resolve: { alias: aliases },
    });
    try {
        execFileSync(
            'php',
            ['tests/Fixtures/Localization/export-without-passkeys.php', root],
            {
                env: {
                    ...process.env,
                    APP_ENV: 'testing',
                    DB_DATABASE: 'gamestackr_testing',
                    DB_URL: '',
                    APP_ROUTES_CACHE: join(root, 'routes.php'),
                    APP_CONFIG_CACHE: join(root, 'config.php'),
                },
            },
        );
        for (const [name, path] of [
            ['login', 'login'],
            ['confirm-password', 'user/confirm-password'],
        ]) {
            const body = await renderAuthPage(server, name, path);
            assert.match(body, new RegExp('action="/en/' + path + '"'));
            assert.match(body, /name="password"/);
        }
        writeFileSync(
            join(root, 'tsconfig.json'),
            JSON.stringify({
                extends: join(process.cwd(), 'tsconfig.json'),
                compilerOptions: {
                    paths: Object.fromEntries(
                        Object.entries(aliases).map(([key, value]) => [
                            key + '/*',
                            [value + '/*'],
                        ]),
                    ),
                },
                exclude: [
                    join(source, 'i18n/generated'),
                    join(source, 'routes'),
                ],
            }),
        );
        execFileSync(
            'node_modules/.bin/tsc',
            ['--project', join(root, 'tsconfig.json')],
            { stdio: 'inherit' },
        );
    } finally {
        await server.close();
        rmSync(root, { recursive: true, force: true });
    }
});

void test('passkey success opens the intended Filament document', async (context) => {
    const inertiaVisits = context.mock.method(router, 'visit', () => {});
    const root = mkdtempSync(join(tmpdir(), 'gamestackr-ceremony-'));
    const hook = join(root, 'ceremony.mjs');
    writeFileSync(
        hook,
        'export const ceremony = {}; export function usePasskeyVerify(options) { ceremony.complete = options.onSuccess; return {isSupported:true}; }',
    );
    const server = await createServer({
        configFile: false,
        cacheDir: join(root, 'vite-cache'),
        server: { middlewareMode: true, ws: false },
        resolve: {
            alias: {
                '@laravel/passkeys/react': hook,
                '@': join(process.cwd(), 'resources/js'),
            },
        },
    });
    try {
        await renderAuthPage(server, 'login', 'login');
        const { ceremony } = await server.ssrLoadModule(hook);
        const visits: string[] = [];
        const target = '/en/admin/games/7/edit?tab=details';
        Object.defineProperty(globalThis, 'window', {
            configurable: true,
            value: { location: { assign: (url: string) => visits.push(url) } },
        });
        try {
            ceremony.complete({ redirect: target });
            assert.deepEqual(visits, [target]);
            assert.equal(inertiaVisits.mock.callCount(), 0);
        } finally {
            Reflect.deleteProperty(globalThis, 'window');
        }
    } finally {
        await server.close();
        rmSync(root, { recursive: true, force: true });
    }
});
