import assert from 'node:assert/strict';
import { setTimeout as wait } from 'node:timers/promises';
import { test } from 'node:test';
import { createFilterNavigation } from '../../resources/js/lib/filter-navigation.ts';

void test('language navigation uses server alternates, keeps filters and fragment, and cancels pending search first', async () => {
    const navigationModule =
        await import('../../resources/js/lib/localized-navigation.ts').catch(
            () => null,
        );
    assert.ok(navigationModule, 'localized navigation must exist');
    const visits: string[] = [];
    const navigation = createFilterNavigation(
        { q: '' },
        () => {
            visits.push('stale-search');
            return () => {};
        },
        15,
    );
    const unregister = navigationModule.registerPendingNavigation(
        navigation.cancel,
    );
    navigation.change({ q: 'pending' }, true);
    navigationModule.switchLocale(
        {
            en: '/en/pokemon?q=base&catalog=international&language=fr&edition=ja&page=2',
        },
        'en',
        '#releases',
        (url: string) => visits.push(url),
    );
    await wait(35);
    assert.deepEqual(visits, [
        '/en/pokemon?q=base&catalog=international&language=fr&edition=ja&page=2#releases',
    ]);
    unregister();
});
