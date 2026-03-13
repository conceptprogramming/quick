<?php

// ── App ───────────────────────────────────────────────────────
define('APP_NAME', $_ENV['APP_NAME'] ?? 'QuickChatPDF');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/quickchatpdf');

define('APP_ENV', $_ENV['APP_ENV'] ?? 'local');
define('BASE_PATH', parse_url(APP_URL, PHP_URL_PATH));
define('TMP_BASE', dirname(__DIR__) . '/storage/tmp');
define('SESSION_LIFETIME', 60 * 60 * 24 * 30); // 30 days
define('ADMIN_PASSWORD', $_ENV['ADMIN_PASSWORD'] ?? 'changeme');



// ── PDF Limits per plan ───────────────────────────────────────
define('PDF_LIMITS', [
    'free' => ['pages' => 5, 'size_mb' => 5],
    'basic' => ['pages' => 10, 'size_mb' => 10],
    'pro' => ['pages' => 15, 'size_mb' => 15],
    'professional' => ['pages' => 25, 'size_mb' => 50],
]);

// ── Plans ─────────────────────────────────────────────────────
define('PLANS', [
    'free' => [
        'name' => 'Free',
        'price' => 0.00,
        'paypal_plan_id' => null,
        'monthly_credits' => 20,
        'benefits' => [
            'pdfs_per_month' => 3,
            'chat_messages' => 10,
            'summaries' => 2,
            'qa_questions' => 5,
            'quizzes' => 1,
        ],
    ],
    'basic' => [
        'name' => 'Basic',
        'price' => 4.99,
        'paypal_plan_id' => 'P-2R534910YD008524DNGT6AKI',
        'monthly_credits' => 100,
        'benefits' => [
            'pdfs_per_month' => 10,
            'chat_messages' => 100,
            'summaries' => 10,
            'qa_questions' => 30,
            'quizzes' => 10,
        ],
    ],
    'pro' => [
        'name' => 'Pro',
        'price' => 14.99,
        'paypal_plan_id' => 'P-5UK78815MJ053405RNGT6MZI',
        'monthly_credits' => 300,
        'benefits' => [
            'pdfs_per_month' => 50,
            'chat_messages' => 500,
            'summaries' => 50,
            'qa_questions' => 150,
            'quizzes' => 35,
        ],
    ],
    'professional' => [
        'name' => 'Professional',
        'price' => 24.99,
        'paypal_plan_id' => 'P-7HR35975WR4628948NGT6NDA',
        'monthly_credits' => 1000,
        'benefits' => [
            'pdfs_per_month' => 150,
            'chat_messages' => 2000,
            'summaries' => 100,
            'qa_questions' => 500,
            'quizzes' => 100,
        ],
    ],
]);

// ── Credit costs per feature ──────────────────────────────────
define('CREDIT_COSTS', [
    'chat' => 1,
    'summary' => 3,
    'qa' => 2,  // per question
    'quiz' => 2,  // per quiz
    'export' => 1,
]);

// ── Top-Up Packs ──────────────────────────────────────────────
define('TOPUP_PACKS', [
    'small' => ['credits' => 50, 'price' => 1.99],
    'medium' => ['credits' => 150, 'price' => 4.99],
    'large' => ['credits' => 400, 'price' => 9.99],
]);

// ── Rate Limits ───────────────────────────────────────────────
define('RATE_LIMITS', [
    'otp_send' => ['max' => 3, 'window' => 600],  // 3 per 10 min
    'otp_verify_block' => ['max' => 1, 'window' => 86400],  // block IP for 24 hours after OTP failures
    'ai_request' => ['max' => 10, 'window' => 60],   // 10 per min
    'upload' => ['max' => 5, 'window' => 60],   // 5 uploads per min
]);

// ── OTP ───────────────────────────────────────────────────────
define('OTP_EXPIRY', 600);  // 10 minutes
define('OTP_LENGTH', 6);
define('OTP_MAX_ATTEMPTS', 5);

// ── PayPal ────────────────────────────────────────────────────
// ── PayPal ────────────────────────────────────────────────────
define('PAYPAL_MODE',          $_ENV['PAYPAL_MODE']          ?? 'sandbox');  
define('PAYPAL_CLIENT_ID',     $_ENV['PAYPAL_CLIENT_ID']     ?? '');        
define('PAYPAL_CLIENT_SECRET', $_ENV['PAYPAL_CLIENT_SECRET'] ?? '');         
define('PAYPAL_WEBHOOK_ID',    $_ENV['PAYPAL_WEBHOOK_ID']    ?? '');         
define('PAYPAL_API_BASE',                                                      
    PAYPAL_MODE === 'live'
        ? 'https://api-m.paypal.com'
        : 'https://api-m.sandbox.paypal.com'
);
