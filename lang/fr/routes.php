<?php

return [
    'home' => '',
    'dashboard' => 'mon-espace',
    'games' => [
        'show' => '{game}',
        'expansion' => '{game}/{catalogKey}/{expansionKey}',
    ],
    'auth' => [
        'login' => 'connexion',
        'logout' => 'deconnexion',
        'register' => 'inscription',
        'forgot-password' => 'mot-de-passe-oublie',
        'reset-password' => 'reinitialiser-mot-de-passe/{token}',
        'verification-notice' => 'email/verifier',
        'verification-verify' => 'email/verifier/{id}/{hash}',
    ],
    'account' => [
        'settings' => 'parametres',
        'profile' => 'parametres/profil',
        'security' => 'parametres/securite',
    ],
    'admin' => [
        'dashboard' => 'admin',
        'games' => 'admin/jeux',
        'game-create' => 'admin/jeux/creer',
        'game-edit' => 'admin/jeux/{record}/modifier',
    ],
];
