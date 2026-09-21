<?php

declare(strict_types=1);

define(
    'APP_NAME',
    'Arna Tours & Travels'
);

define(
    'APP_URL',
    rtrim((string)(getenv('ARNA_APP_URL') ?: 'http://localhost/ARNA-TOURS-TRAVELS'), '/')
);

define(
    'TIMEZONE',
    'Asia/Kolkata'
);

// Public business contact and social links. Update these values when the
// official Arna social profiles are available.
define('ARNA_PHONE_DISPLAY', '+91 94800 01511');
define('ARNA_PHONE_E164', '+919480001511');
define('ARNA_WHATSAPP_NUMBER', '919480001511');
define('ARNA_INSTAGRAM_URL', 'https://www.instagram.com/');
define('ARNA_FACEBOOK_URL', 'https://www.facebook.com/');
define('ARNA_YOUTUBE_URL', 'https://www.youtube.com/');

date_default_timezone_set(TIMEZONE);