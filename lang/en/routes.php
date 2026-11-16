<?php

return [
    'home' => '',
    'dashboard' => 'dashboard',
    'games' => [
        'show' => '{game}',
        'expansion' => '{game}/{catalogKey}/{expansionKey}',
    ],
    'auth' => [
        'login' => 'login',
        'logout' => 'logout',
        'register' => 'register',
        'forgot-password' => 'forgot-password',
        'reset-password' => 'reset-password/{token}',
        'verification-notice' => 'email/verify',
        'verification-verify' => 'email/verify/{id}/{hash}',
    ],
    'account' => [
        'settings' => 'settings',
        'profile' => 'settings/profile',
        'security' => 'settings/security',
    ],
    'admin' => [
        'dashboard' => 'admin',
        'games' => 'admin/games',
        'game-create' => 'admin/games/create',
        'game-edit' => 'admin/games/{record}/edit',
    ],
];
