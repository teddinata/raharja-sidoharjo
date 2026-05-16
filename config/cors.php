<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://sidoharjo-raharja.com',
        'https://www.sidoharjo-raharja.com',
        'http://localhost:5173',    // local dev
        'http://localhost:8080',    // local dev alt
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Content-Disposition', 'Content-Type'],

    'max_age' => 0,

    'supports_credentials' => true,
];