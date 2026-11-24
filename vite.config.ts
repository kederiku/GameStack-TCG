import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import babel from '@rolldown/plugin-babel';
import tailwindcss from '@tailwindcss/vite';
import react, { reactCompilerPreset } from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { bunny, google } from 'laravel-vite-plugin/fonts';
import { defineConfig, lazyPlugins } from 'vite-plus';
import type { Plugin } from 'vite';

function frontendTranslations(): Plugin {
    function exportTranslations(root: string) {
        execFileSync('php', ['artisan', 'localization:export'], {
            cwd: root,
            stdio: 'inherit',
        });
    }

    let root: string;

    return {
        name: 'gamestackr-frontend-translations',
        configResolved(config) {
            root = config.root;
        },
        buildStart: {
            order: 'post',
            sequential: true,
            handler() {
                exportTranslations(root);
            },
        },
        configureServer(server) {
            const { logger } = server.config;
            const lang = resolve(root, 'lang');
            const localeConfig = resolve(root, 'config/localization.php');
            server.watcher.add([lang, localeConfig]);
            server.watcher.on('all', (event, path) => {
                if (
                    !['add', 'change', 'unlink'].includes(event) ||
                    !(
                        path === localeConfig ||
                        (path.startsWith(lang + sep) && path.endsWith('.php'))
                    )
                ) {
                    return;
                }

                try {
                    exportTranslations(root);
                    server.ws.send({ type: 'full-reload' });
                } catch (error) {
                    logger.error(String(error));
                }
            });
        },
    };
}

export default defineConfig({
    plugins: lazyPlugins(() => [
        frontendTranslations(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/filament/admin.css',
                'resources/js/app.tsx',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
                google('Big Shoulders Display', {
                    weights: [800, 900],
                }),
            ],
        }),
        inertia(),
        react(),
        babel({
            presets: [reactCompilerPreset()],
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
            patterns: [
                'routes/**/*.php',
                'app/**/Http/**/*.php',
                'app/Support/Localization/**/*.php',
                'lang/*/routes.php',
                'config/localization.php',
            ],
        }),
    ]),
    server: {
        host: '0.0.0.0',
        port: Number(process.env.VITE_PORT ?? 5173),
        strictPort: true,
        hmr: {
            host: 'localhost',
            clientPort: Number(process.env.VITE_PORT ?? 5173),
        },
        watch: {
            ignored: [
                '**/.agents/**',
                '**/.claude/**',
                '**/.cursor/**',
                '**/.junie/**',
                '**/vendor/**',
            ],
        },
    },
    lint: {
        ignorePatterns: [
            'vendor/**',
            'node_modules/**',
            'public/**',
            'bootstrap/ssr/**',
            'tailwind.config.js',
            'resources/js/actions/**',
            'resources/js/i18n/generated/**',
            'resources/js/components/ui/*',
            'resources/js/routes/**',
            'resources/js/wayfinder/**',
        ],
        options: {
            denyWarnings: true,
            typeAware: true,
        },
    },
    fmt: {
        printWidth: 80,
        tabWidth: 4,
        singleQuote: true,
        semi: true,
        singleAttributePerLine: false,
        htmlWhitespaceSensitivity: 'css',
        ignorePatterns: [
            '.github/**',
            'composer.json',
            'resources/js/i18n/generated/**',
            'resources/js/components/ui/*',
            'resources/views/mail/*',
        ],
        sortTailwindcss: {
            functions: ['clsx', 'cn', 'cva'],
            stylesheet: 'resources/css/app.css',
        },
    },
});
