<?php

return [

    'auth' => [
        'enabled' => (bool) env('APP_AUTH_ENABLED', false),
        'email' => env('APP_AUTH_EMAIL'),
        'password' => env('APP_AUTH_PASSWORD'),
    ],

];
