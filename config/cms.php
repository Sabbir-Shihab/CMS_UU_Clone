<?php

return [
    'admin_email' => env('ADMIN_EMAIL', 'admin@uttara.local'),
    'admin_password_hash' => base64_decode(env('ADMIN_PASSWORD_HASH_B64', '')),
    'static_html_path' => env('STATIC_HTML_PATH', base_path('legacy')),
];
