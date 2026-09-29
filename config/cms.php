<?php

return [
    // Currencies an admin can pick for a country / event: code => symbol.
    'currencies' => [
        'INR' => '₹',
        'USD' => '$',
        'GBP' => '£',
        'EUR' => '€',
        'AUD' => 'A$',
        'CAD' => 'C$',
        'SGD' => 'S$',
        'AED' => 'AED ',
    ],

    // Slugs that must never be used for a country or a page (they would shadow real routes).
    'reserved_slugs' => [
        'admin', 'e', 'api', 'login', 'logout', 'register', 'profile', 'payment', 'user', 'welcome', 'test',
        'followup', 'create-order', 'verify-payment', 'validate-promo', 'forgot-password', 'reset-password',
        'verify-email', 'confirm-password', 'email', 'dashboard', 'storage', 'vendor', 'build', 'images',
        'css', 'js', 'admins', 'cms', 'auth', 'sanctum', 'up',
    ],

    // Visitor-country lookup by IP (used when no CDN country header is present).
    'geo' => [
        'enabled' => env('GEO_LOOKUP_ENABLED', true),
        'url' => 'https://free.freeipapi.com/api/v1/json/',  // free tier: 60 req/min, no key
        'timeout' => 1.5,          // seconds; a slow lookup must never hold up a page
        'ttl' => 60 * 60 * 24,     // cache a successful lookup per IP for a day
        'failure_ttl' => 60 * 5,   // after a failure/rate-limit, don't retry that IP for 5 minutes
    ],
];
