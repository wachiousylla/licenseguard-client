<?php

return [

    // === Les 3 seules variables à renseigner dans .env ===
    'api_url' => env('LICENSE_API_URL'),
    'key'     => env('LICENSE_KEY'),
    'secret'  => env('LICENSE_SECRET'),

    // === Options (valeurs par défaut sûres — facultatives) ===

    // Active/désactive la vérification.
    'enabled' => env('LICENSE_ENABLED', true),

    // Durée de mise en cache d'un résultat AUTORISÉ (heures).
    'cache_hours' => (int) env('LICENSE_CACHE_HOURS', 6),

    // Si le serveur est injoignable : true => laisser passer (fail-open).
    'fail_open' => (bool) env('LICENSE_FAIL_OPEN', true),

    // Délai d'attente des appels (secondes).
    'timeout' => (int) env('LICENSE_TIMEOUT', 5),

    // Code HTTP de la page de blocage.
    'block_status' => (int) env('LICENSE_BLOCK_STATUS', 503),

    // Routes/préfixes JAMAIS bloqués (sécurité : on garde l'accès au login).
    'except' => [
        'login', 'logout', 'register',
        'password/*', 'forgot-password', 'reset-password',
        'health-check', 'up', 'webhooks/*', 'assets/*',
    ],
];
