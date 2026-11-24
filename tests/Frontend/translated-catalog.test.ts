import assert from 'node:assert/strict';
import { test } from 'node:test';
import { createElement } from 'react';
import { renderToString } from 'react-dom/server';
import { createInertiaApp } from '@inertiajs/react';
import { createServer } from 'vite';

void test('expansion renders text with independent language provenance and omits deliberately absent descriptions', async () => {
    const server = await createServer({
        configFile: false,
        server: { middlewareMode: true, ws: false },
        resolve: { alias: { '@': `${process.cwd()}/resources/js` } },
    });
    try {
        const { LocaleProvider } = await server.ssrLoadModule(
            '/resources/js/i18n/provider.tsx',
        );
        const { default: Page } = await server.ssrLoadModule(
            '/resources/js/pages/games/expansion.tsx',
        );
        for (const description of [null, '日本語の説明']) {
            const response = await createInertiaApp({
                page: {
                    component: 'games/expansion',
                    props: {
                        locale: 'fr',
                        currentGame: {
                            name: 'TCG',
                            nameLanguage: null,
                            url: '/fr/tcg',
                        },
                        expansion: {
                            name: 'Historical title',
                            nameLanguage: null,
                            nameFallback: true,
                            description,
                            descriptionLanguage: 'ja',
                            descriptionFallback: true,
                            catalog: {
                                key: 'intl',
                                name: 'Catalogue',
                                nameLanguage: 'fr',
                            },
                            type: 'main',
                            editions: [],
                            related: [],
                            selectedEdition: null,
                        },
                        errors: {},
                    },
                    url: '/fr/tcg/intl/base',
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
            assert.match(
                response.body,
                /<h1[^>]*lang=""[^>]*>Historical title<\/h1>/,
            );
            if (description === null) {
                assert.doesNotMatch(
                    response.body,
                    /Explorez les éditions de cette extension/,
                );
            } else {
                assert.match(
                    response.body,
                    /<p[^>]*lang="ja"[^>]*>日本語の説明<\/p>/,
                );
            }
        }
    } finally {
        await server.close();
    }
});

void test('the name-sorted catalogue keeps A/B/A series cards in server order', async () => {
    const server = await createServer({
        configFile: false,
        server: { middlewareMode: true, ws: false },
        resolve: { alias: { '@': `${process.cwd()}/resources/js` } },
    });
    try {
        const { LocaleProvider } = await server.ssrLoadModule(
            '/resources/js/i18n/provider.tsx',
        );
        const { default: Page } = await server.ssrLoadModule(
            '/resources/js/pages/games/show.tsx',
        );
        const catalog = {
            id: 1,
            key: 'intl',
            name: 'Catalogue',
            nameLanguage: 'fr',
        };
        const data = ['Alpha', 'Bravo', 'Charlie'].map((name, index) => ({
            id: index + 1,
            name,
            nameLanguage: 'en',
            type: 'main',
            catalog,
            url: '/fr/tcg/intl/' + name,
            languages: [],
            series: {
                id: index % 2,
                name: index % 2 ? 'Series B' : 'Series A',
                nameLanguage: 'en',
                url: '/fr/tcg',
            },
        }));
        for (const sort of ['name', 'recent']) {
            const response = await createInertiaApp({
                page: {
                    component: 'games/show',
                    props: {
                        locale: 'fr',
                        currentGame: {
                            id: 1,
                            name: 'TCG',
                            nameLanguage: null,
                            url: '/fr/tcg',
                        },
                        gameDetails: {},
                        catalogData: {
                            total: 3,
                            catalogs: [],
                            languages: [],
                            filters: {
                                sort,
                                q: '',
                                language: '*',
                                type: 'all',
                            },
                            expansions: { data, total: 3, last_page: 1 },
                        },
                        errors: {},
                    },
                    url: '/fr/tcg',
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
            assert.deepEqual(
                [
                    ...response.body.matchAll(
                        /<h3[^>]*>(Alpha|Bravo|Charlie)<\/h3>/g,
                    ),
                ].map((match) => match[1]),
                sort === 'name'
                    ? ['Alpha', 'Bravo', 'Charlie']
                    : ['Alpha', 'Charlie', 'Bravo'],
            );
            assert.deepEqual(
                [
                    ...response.body.matchAll(
                        /title="Extensions sur cette page">(\d+)<\/span>/g,
                    ),
                ].map((match) => Number(match[1])),
                sort === 'name' ? [2, 1, 2] : [2, 1],
            );
        }
    } finally {
        await server.close();
    }
});
