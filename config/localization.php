<?php

return [
    'default' => 'fr',
    'fallback' => 'fr',

    // Keys are canonical URL prefixes. Laravel uses laravelLocale to locate PHP dictionaries.
    'locales' => [
        'fr' => [
            'laravelLocale' => 'fr',
            'htmlLang' => 'fr',
            'label' => 'Français',
            'direction' => 'ltr',
        ],
        'en' => [
            'laravelLocale' => 'en',
            'htmlLang' => 'en',
            'label' => 'English',
            'direction' => 'ltr',
        ],
    ],
];
