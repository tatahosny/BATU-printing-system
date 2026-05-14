<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | هنا يتم تحديد الدومينات المسموح لها بالوصول إلى الـ API
    |
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',
        'register',
        '*',
    ],

    'allowed_methods' => [
        '*',
    ],

    'allowed_origins' => [
        '*',
        // أو تقدر تحط الدومين بتاعك بدل *
        // 'https://batusystem.infinityfree.me',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        '*',
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];