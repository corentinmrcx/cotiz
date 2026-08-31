<?php

return [

    'auth' => [
        'enabled' => (bool) env('APP_AUTH_ENABLED', false),
        'email' => env('APP_AUTH_EMAIL'),
        'password' => env('APP_AUTH_PASSWORD'),
    ],

    'chrome_path' => env('CHROME_PATH', '/usr/bin/chromium'),

    'carte' => [
        'largeur' => 1050,
        'hauteur' => 600,
        'facteur_echelle' => 2,
    ],

];
