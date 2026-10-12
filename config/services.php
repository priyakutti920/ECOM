<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'shiprocket' => [
        'email' => env('SHIPROCKET_EMAIL', ''),
        'password' => env('SHIPROCKET_PASSWORD', ''),
        'pickup_location' => env('SHIPROCKET_PICKUP_LOCATION', 'Primary'),
        'pickup_pincode' => env('SHIPROCKET_PICKUP_PINCODE', '641001'), // Default store hub (Coimbatore / Tamil Nadu)
        'webhook_token' => env('SHIPROCKET_WEBHOOK_TOKEN', null),
    ],

    'razorpay' => [
        'key_id' => env('RAZORPAY_KEY_ID', ''),
        'key_secret' => env('RAZORPAY_KEY_SECRET', ''),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET', ''),
    ],

    'cashfree' => [
        'app_id' => env('CASHFREE_APP_ID', ''),
        'secret_key' => env('CASHFREE_SECRET_KEY', ''),
        'mode' => env('CASHFREE_MODE', 'sandbox'),
    ],

    'whatsapp' => [
        'api_url' => env('WA_API_URL', 'https://www.fast2sms.com/dev/whatsapp'),
        'fast2sms_auth_key' => env('WA_FAST2SMS_AUTH_KEY', env('FAST2SMS_DEV_AUTH_KEY', env('FAST2SMS_AUTH_KEY', ''))),
        'phone_number_id' => env('WA_PHONE_NUMBER_ID', env('FAST2SMS_PHONE_NUMBER_ID', '')),
        'sender_number' => env('WA_SENDER_NUMBER', ''),
        'admin_phone' => env('WA_ADMIN_PHONE', ''),
        'customer_notify_enabled' => filter_var(env('WA_CUSTOMER_NOTIFY_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'engine_disabled' => filter_var(env('WA_ENGINE_DISABLED', false), FILTER_VALIDATE_BOOLEAN),
        'message_id' => env('WA_MESSAGE_ID', '24059'),
        'template_name' => env('WA_TEMPLATE_NAME', 'order_notify'),
        // Cloud API compatibility
        'token' => env('WA_API_TOKEN', ''),
        // Separate UPI Gateway Key & URLs for WhatsApp Wallet Recharge / Add Amount
        'upi_key' => env('WA_UPI_KEY', env('WA_UPI_API_KEY', '')),
        'upi_create_order_url' => env('WA_UPI_CREATE_ORDER_URL', 'https://upicheckout.online/api/create-order'),
        'upi_status_check_url' => env('WA_UPI_STATUS_CHECK_URL', 'https://upicheckout.online/api/check-order-status'),
    ],
];
