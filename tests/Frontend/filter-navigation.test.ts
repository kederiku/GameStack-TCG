import assert from 'node:assert/strict';
import { setTimeout as wait } from 'node:timers/promises';
import { test } from 'node:test';
import { createFilterNavigation } from '../../resources/js/lib/filter-navigation.ts';

void test('leaving or switching site language cancels pending search', async () => {
    const visits: object[] = [];
    const navigation = createFilterNavigation(
        { q: '', type: '*' },
        (filters) => {
            visits.push(filters);
            return () => {};
        },
        15,
    );
    navigation.change({ q: '151' }, true);
    navigation.cancel();
    await wait(35);
    assert.deepEqual(visits, []);
});

void test('rapid filter changes preserve previous choices and cancel obsolete responses', () => {
    const visits: object[] = [];
    let cancelled = 0;
    const navigation = createFilterNavigation(
        { q: '', language: '*', type: '*' },
        (filters) => {
            visits.push(filters);
            return () => {
                cancelled++;
            };
        },
    );
    navigation.change({ language: 'fr' });
    navigation.change({ type: 'special' });
    assert.deepEqual(visits[1], { q: '', language: 'fr', type: 'special' });
    assert.equal(cancelled, 1);
    navigation.cancel();
});

void test('a filter change includes the search draft without a stale delayed request', async () => {
    const visits: object[] = [];
    const navigation = createFilterNavigation(
        { q: '', type: '*' },
        (filters) => {
            visits.push(filters);
            return () => {};
        },
        15,
    );
    navigation.change({ q: '151' }, true);
    navigation.change({ type: 'special' });
    await wait(35);
    assert.deepEqual(visits, [{ q: '151', type: 'special' }]);
    navigation.cancel();
});

void test('typing a newer query cancels the old response before debounce expires', async () => {
    const visits: object[] = [];
    let cancelled = 0;
    const navigation = createFilterNavigation(
        { q: '' },
        (filters) => {
            visits.push(filters);
            return () => {
                cancelled++;
            };
        },
        15,
    );
    navigation.change({ q: '1' });
    navigation.change({ q: '151' }, true);
    assert.equal(cancelled, 1);
    await wait(35);
    assert.deepEqual(visits, [{ q: '1' }, { q: '151' }]);
    navigation.cancel();
});
