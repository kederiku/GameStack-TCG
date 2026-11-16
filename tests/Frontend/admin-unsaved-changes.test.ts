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
