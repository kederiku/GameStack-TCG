import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import { runInNewContext } from 'node:vm';

void test('the installed Filament guard cancels native navigation for changed data and releases clean forms', () => {
    const events = new EventTarget();
    let cleanup = () => {};
    const md5 = (value: string) =>
        createHash('md5').update(value).digest('hex');
    const wire = {
        data: { name: 'Original' },
        savedDataHash: md5(JSON.stringify({ name: 'Original' })),
        __instance: {
            effects: {},
            addCleanup: (callback: () => void) => {
                cleanup = callback;
            },
        },
    };
    const window = {
        jsMd5: md5,
        addEventListener: events.addEventListener.bind(events),
        removeEventListener: events.removeEventListener.bind(events),
        setUpFilamentUnsavedDataChangesAlert: (_options: {
            $wire: typeof wire;
        }) => {},
    };
    runInNewContext(
        readFileSync(
            'vendor/filament/filament/resources/js/unsaved-changes-alert.js',
            'utf8',
        ),
        { window },
    );
    window.setUpFilamentUnsavedDataChangesAlert({ $wire: wire });
    const leave = () =>
        events.dispatchEvent(new Event('beforeunload', { cancelable: true }));
    assert.equal(leave(), true);
    wire.data.name = 'Unsaved';
    assert.equal(leave(), false);
    wire.data.name = 'Original';
    assert.equal(leave(), true);
    cleanup();
    wire.data.name = 'Changed after component removal';
    assert.equal(leave(), true);
});

void test('native language submission uses the current table URL after interactions and clears removed filters', () => {
    const template = readFileSync(
        'resources/views/filament/language-switcher.blade.php',
        'utf8',
    );
    const handler = template.match(/onsubmit="([^"]+)"/)?.[1];
    assert.ok(handler);
    const form = {
        elements: { target: { value: '/en/admin/catalogs?search=old&page=1' } },
    };
    const window = {
        location: {
            search: '?filters[game][value]=42&search=Pick&sort=name%3Adesc&page=3',
            hash: '#results',
        },
    };
    const submit = () =>
        runInNewContext(`(function () { ${handler} }).call(form)`, {
            form,
            window,
        });
    submit();
    assert.equal(
        form.elements.target.value,
        '/en/admin/catalogs' + window.location.search + '#results',
    );
    // A cancelled native navigation can be followed by clearing filters and trying again.
    window.location.search = '';
    window.location.hash = '';
    submit();
    assert.equal(form.elements.target.value, '/en/admin/catalogs');
    assert.match(template, /<form method="POST"/);
    assert.doesNotMatch(template, /wire:submit|preventDefault/);
});
