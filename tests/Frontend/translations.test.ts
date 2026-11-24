import assert from 'node:assert/strict';
import { test } from 'node:test';
import { createElement } from 'react';
import { renderToStaticMarkup } from 'react-dom/server';

import enMessages from '../../resources/js/i18n/generated/php_en.json' with { type: 'json' };
import frMessages from '../../resources/js/i18n/generated/php_fr.json' with { type: 'json' };
import { createTranslator } from '../../resources/js/i18n/translator.ts';

const dictionaries = {
    fr: {
        'common.items':
            '{0} Aucun élément|{1} :count élément|[2,*] :count éléments',
        'common.groups': ':count groupe|:count groupes',
        'common.greeting': 'Bonjour, :name !',
    },
    en: {
        'common.items': '{0} No items|{1} :count item|[2,*] :count items',
        'common.visitors':
            '{0} Nobody|{1} :count visitor: :name and :name|[2,*] :count visitors: :name',
        'common.greeting': 'Hello, :name!',
    },
};

const definitions = {
    fr: { laravelLocale: 'fr' },
    en: { laravelLocale: 'en' },
};
const runtimeFor = (locale: 'fr' | 'en') =>
    createTranslator(dictionaries, definitions, locale, 'fr');

void test('explicit Laravel plural ranges interpolate count in French and English', () => {
    const fr = runtimeFor('fr');
    const en = runtimeFor('en');

    assert.deepEqual(
        [0, 1, 2].map((count) => fr.tChoice('common.items', count)),
        ['Aucun élément', '1 élément', '2 éléments'],
    );
    assert.deepEqual(
        [0, 1, 2].map((count) => en.tChoice('common.items', count)),
        ['No items', '1 item', '2 items'],
    );
});

void test('a missing English key follows the French fallback plural rule', () => {
    const en = runtimeFor('en');

    assert.equal(en.tChoice('common.groups', 0), '0 groupe');
    assert.equal(en.tChoice('common.groups', 2), '2 groupes');
    assert.equal(en.t('common.unknown'), 'common.unknown');
});

void test('plural selection precedes replacement and React escapes text', () => {
    const en = runtimeFor('en');
    const name = '$& | <b>Sam</b>';
    const text = en.tChoice('common.visitors', 1, { name });

    assert.equal(text, '1 visitor: $& | <b>Sam</b> and $& | <b>Sam</b>');
    assert.equal(
        renderToStaticMarkup(createElement('p', null, text)),
        '<p>1 visitor: $&amp; | &lt;b&gt;Sam&lt;/b&gt; and $&amp; | &lt;b&gt;Sam&lt;/b&gt;</p>',
    );
});

void test('each locale renders immediately and independently across French–English–French', () => {
    const fr = runtimeFor('fr');
    const en = runtimeFor('en');
    const again = runtimeFor('fr');

    assert.equal(fr.t('common.greeting', { name: 'Sam' }), 'Bonjour, Sam !');
    assert.equal(en.t('common.greeting', { name: 'Sam' }), 'Hello, Sam!');
    assert.equal(again.t('common.greeting', { name: 'Sam' }), 'Bonjour, Sam !');
});

void test('React can consume the generated PHP dictionaries', () => {
    const translator = createTranslator(
        { fr: frMessages, en: enMessages },
        definitions,
        'en',
        'fr',
    );
    assert.equal(translator.t('auth.login.title'), 'Log in');
});
