<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paddle API Key
    |--------------------------------------------------------------------------
    |
    | Create one under Developer Tools > Authentication in Paddle. Sandbox and
    | live keys are separate — a sandbox key only works with PADDLE_SANDBOX=true.
    |
    */
    'api_key' => env('PADDLE_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Sandbox
    |--------------------------------------------------------------------------
    */
    'sandbox' => (bool) env('PADDLE_SANDBOX', false),

    /*
    |--------------------------------------------------------------------------
    | Paddle API URL
    |--------------------------------------------------------------------------
    |
    | Derived from the sandbox flag; override only to point at a proxy.
    |
    */
    'base_url' => env('PADDLE_BASE_URL', env('PADDLE_SANDBOX', false)
        ? 'https://sandbox-api.paddle.com'
        : 'https://api.paddle.com'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret Key
    |--------------------------------------------------------------------------
    |
    | Shown once when you create a notification destination in Paddle. Required
    | only to verify incoming webhook signatures.
    |
    */
    'webhook_secret' => env('PADDLE_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Default Pagination Size
    |--------------------------------------------------------------------------
    |
    | Used as the default "per_page" for list endpoints when none is given.
    |
    */
    'default_per_page' => env('PADDLE_DEFAULT_PER_PAGE', 50),

];
