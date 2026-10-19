<?php

/*
| Copy that is the same on every product page. Per-product copy lives in products.content.
*/
return [
    'practitioner' => [
        'name' => 'Chaitanya K',
        'initials' => 'CK',
        'role' => "India's only energy-based astrologer · 14 years · 6,000+ sessions",
    ],

    // Shown under the price. Entries with 'consult' => true are only shown for products that include a consultation.
    'trust_cues' => [
        ['icon' => 'bolt', 'text' => 'Energy-aligned by Astro Chaitanya'],
        ['icon' => 'ban', 'text' => 'No birth chart. No kundli.'],
        ['icon' => 'sun', 'text' => 'Includes one private consultation with Chaitanya', 'consult' => true],
    ],

    'delivery' => [
        ['icon' => 'truck', 'text' => 'Free shipping'],
        ['icon' => 'lock', 'text' => 'Secure payment'],
        ['icon' => 'shield', 'text' => 'Consultation included', 'consult' => true],
    ],

    'comparison' => [
        ['Energisation', 'Personal Kriya by Chaitanya', 'Mass rituals or generic blessings'],
        ['Basis', 'Energy alignment — no zodiac logic', 'Zodiac or birth chart dependent'],
        ['Design intent', 'Minimalistic, intention-led', 'Decorative, commercially styled'],
        ['Dependency', 'Supports stability, not dependency', 'Often encourages ongoing purchases'],
        ['Birth date required', 'No — reads present energy', 'Usually required'],
    ],
];
