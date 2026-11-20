<?php
/**
 * WhatsApp + receipt settings.
 * Keep this file private. Do NOT commit real tokens to Git.
 * Values can also be set as server environment variables.
 */
return [
    // --- Meta WhatsApp Cloud API ---------------------------------------
    'access_token'    => getenv('WA_ACCESS_TOKEN')    ?: 'PUT_YOUR_NEW_PERMANENT_TOKEN_HERE',
    'phone_number_id' => getenv('WA_PHONE_NUMBER_ID') ?: '1459771103876106',
    'api_version'     => 'v20.0',

    // Approved message template (see README_WHATSAPP.md). Leave '' to send a
    // plain document message (only delivered if the customer messaged you in the last 24h).
    'template_name'   => getenv('WA_TEMPLATE_NAME') ?: 'payment_receipt',
    'template_lang'   => 'en',

    // Default country code for numbers written like 0803...
    'country_code'    => '234',

    // Pause between messages (microseconds) to stay under rate limits.
    'delay_us'        => 400000,

    // --- Receipt -------------------------------------------------------
    // Secret used to sign receipt links so they cannot be guessed. CHANGE THIS to any long random text.
    'link_secret'     => getenv('RECEIPT_LINK_SECRET') ?: 'change-this-to-a-long-random-secret',

    'business' => [
        'name'    => 'BASHIR MADAKI TRANSPORT',
        'tagline' => 'Livestock Transportation Services',
        'phone'   => '',   // e.g. '0803 000 0000' – shown in receipt footer
        'address' => '',   // e.g. 'Kano, Nigeria'
    ],
];
