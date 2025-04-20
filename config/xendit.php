<?php
// config/xendit.php
return [
    'secret_key' => env('XENDIT_SECRET_KEY',null),
    // Tambahkan public key jika diperlukan oleh SDK/API tertentu
    // Tambahkan webhook verification token Anda dari dashboard Xendit
    'webhook_token' => env('XENDIT_WEBHOOK_TOKEN',null),
];