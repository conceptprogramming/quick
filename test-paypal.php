
<?php
/**
 * PayPal Full Self-Contained Test
 * No constants.php needed — DELETE after testing!
 */

// ── PASTE YOUR VALUES HERE ────────────────────────────────────
$clientId     = 'AeM-CfXR0whTdb3Ac__1cnGgALR02p7FjM1EanNNVJsrIvEXPOxQSa_Bua4o26MR9L9x6dK02SrSR-8V';
$clientSecret = 'EAIgm_aZtzAy5W69OIyXAlX4K7PFMgew2bqdj6bmOP8Ep3QKUWXnwGsl0AJFKL14Eis4wGphtdRpa8N7';
$mode         = 'sandbox'; // 'sandbox' or 'live'

$planIds = [
    'Basic'        => 'P-2R534910YD008524DNGT6AKI',        // e.g. P-2RS34910YD008524DNGT6AKI
    'Pro'          => 'P-5UK78815MJ053405RNGT6MZI',
    'Professional' => 'P-7HR35975WR4628948NGT6NDA',
];

$topupPacks = [
    'Small'  => ['credits' => 50,  'price' => 1.99],
    'Medium' => ['credits' => 150, 'price' => 4.99],
    'Large'  => ['credits' => 400, 'price' => 9.99],
];
// ─────────────────────────────────────────────────────────────

$apiBase = $mode === 'live'
    ? 'https://api-m.paypal.com'
    : 'https://api-m.sandbox.paypal.com';

$results = [];

// ── TEST 1: PHP & cURL ────────────────────────────────────────
$results[] = [
    'section' => 'Environment',
    'label'   => 'PHP Version',
    'pass'    => version_compare(PHP_VERSION, '8.0', '>='),
    'detail'  => PHP_VERSION,
];
$results[] = [
    'section' => 'Environment',
    'label'   => 'cURL Extension',
    'pass'    => function_exists('curl_init'),
    'detail'  => function_exists('curl_init') ? 'Available — v' . curl_version()['version'] : 'MISSING — install php-curl',
];
$results[] = [
    'section' => 'Environment',
    'label'   => 'JSON Extension',
    'pass'    => function_exists('json_decode'),
    'detail'  => 'Available',
];

// ── TEST 2: Credentials filled ────────────────────────────────
$clientIdFilled     = $clientId     !== 'PASTE_YOUR_CLIENT_ID_HERE'     && !empty($clientId);
$clientSecretFilled = $clientSecret !== 'PASTE_YOUR_CLIENT_SECRET_HERE' && !empty($clientSecret);

$results[] = [
    'section' => 'Configuration',
    'label'   => 'Client ID set',
    'pass'    => $clientIdFilled,
    'detail'  => $clientIdFilled ? substr($clientId, 0, 14) . '...' : '❌ Still placeholder — paste your Client ID',
];
$results[] = [
    'section' => 'Configuration',
    'label'   => 'Client Secret set',
    'pass'    => $clientSecretFilled,
    'detail'  => $clientSecretFilled ? substr($clientSecret, 0, 8) . '...' : '❌ Still placeholder — paste your Secret',
];
$results[] = [
    'section' => 'Configuration',
    'label'   => 'Mode',
    'pass'    => in_array($mode, ['sandbox', 'live']),
    'detail'  => strtoupper($mode) . ' → ' . $apiBase,
];

// ── TEST 3: Access Token ──────────────────────────────────────
$token    = null;
$tokenErr = null;

if ($clientIdFilled && $clientSecretFilled) {
    $ch = curl_init($apiBase . '/v1/oauth2/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
        CURLOPT_USERPWD        => $clientId . ':' . $clientSecret,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $res      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    $tokenData = json_decode($res, true);
    $token     = $tokenData['access_token'] ?? null;
    $tokenOk   = $httpCode === 200 && $token;

    if (!$tokenOk) {
        $tokenErr = $tokenData['error_description'] ?? $tokenData['error'] ?? $curlErr ?? 'Unknown error';
    }

    $results[] = [
        'section' => 'PayPal API',
        'label'   => 'Access Token',
        'pass'    => $tokenOk,
        'detail'  => $tokenOk
            ? '✅ Received — type: ' . $tokenData['token_type'] . ', expires: ' . $tokenData['expires_in'] . 's, app_id: ' . ($tokenData['app_id'] ?? '?')
            : '❌ HTTP ' . $httpCode . ' — ' . $tokenErr,
    ];
} else {
    $results[] = [
        'section' => 'PayPal API',
        'label'   => 'Access Token',
        'pass'    => false,
        'detail'  => '⏭ Skipped — fill in Client ID and Secret first',
    ];
}

// ── TEST 4: Plan IDs ──────────────────────────────────────────
foreach ($planIds as $planName => $planId) {
    $isPlaceholder = str_starts_with($planId, 'PASTE_') || empty($planId);

    if ($isPlaceholder) {
        $results[] = [
            'section' => 'Plan IDs',
            'label'   => $planName . ' Plan',
            'pass'    => false,
            'detail'  => '⚠️ Placeholder — paste real Plan ID from sandbox.paypal.com/billing/plans',
        ];
        continue;
    }

    if (!$token) {
        $results[] = [
            'section' => 'Plan IDs',
            'label'   => $planName . ' Plan',
            'pass'    => false,
            'detail'  => '⏭ Skipped — token failed',
        ];
        continue;
    }

    $ch = curl_init($apiBase . '/v1/billing/plans/' . $planId);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ],
    ]);
    $r    = json_decode(curl_exec($ch), true);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $isActive = $code === 200 && ($r['status'] ?? '') === 'ACTIVE';

    $results[] = [
        'section' => 'Plan IDs',
        'label'   => $planName . ' Plan',
        'pass'    => $isActive,
        'detail'  => $code === 200
            ? ($isActive
                ? '✅ ACTIVE — "' . ($r['name'] ?? '?') . '" on product ' . ($r['product_id'] ?? '?')
                : '⚠️ Status is "' . ($r['status'] ?? '?') . '" — must be ACTIVE')
            : '❌ HTTP ' . $code . ' — ' . ($r['message'] ?? 'Plan not found / wrong environment'),
    ];
}

// ── TEST 5: Top-Up Packs config ───────────────────────────────
foreach ($topupPacks as $name => $pack) {
    $ok = $pack['credits'] > 0 && $pack['price'] > 0;
    $results[] = [
        'section' => 'Top-Up Packs',
        'label'   => $name . ' Pack',
        'pass'    => $ok,
        'detail'  => $pack['credits'] . ' credits for $' . number_format($pack['price'], 2)
            . ' ($' . number_format($pack['price'] / $pack['credits'], 5) . '/credit)',
    ];
}

// ── TEST 6: PayPal SDK URL reachable ──────────────────────────
$sdkUrl  = $mode === 'live'
    ? 'https://www.paypal.com/sdk/js'
    : 'https://www.sandbox.paypal.com/sdk/js';
$ch = curl_init($sdkUrl . '?client-id=' . $clientId . '&vault=true&intent=subscription');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_NOBODY         => true,
    CURLOPT_TIMEOUT        => 8,
    CURLOPT_FOLLOWLOCATION => true,
]);
curl_exec($ch);
$sdkCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$results[] = [
    'section' => 'SDK',
    'label'   => 'PayPal JS SDK URL',
    'pass'    => $sdkCode === 200,
    'detail'  => 'HTTP ' . $sdkCode . ' — ' . $sdkUrl,
];

// ── Summary ───────────────────────────────────────────────────
$total   = count($results);
$passed  = count(array_filter($results, fn($r) => $r['pass']));
$failed  = $total - $passed;
$allGood = $failed === 0;

// ── Group by section ──────────────────────────────────────────
$grouped = [];
foreach ($results as $r) {
    $grouped[$r['section']][] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PayPal Test — QuickChatPDF</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: #f1f5f9; padding: 36px 20px; color: #0f172a;
}
.wrap { max-width: 740px; margin: 0 auto; }
h1 { font-size: 1.5rem; font-weight: 800; margin-bottom: 4px; }
.sub { color: #64748b; font-size: .88rem; margin-bottom: 24px; }

.summary {
    padding: 16px 20px; border-radius: 12px; margin-bottom: 24px;
    font-weight: 700; font-size: 1rem;
    display: flex; align-items: center; gap: 10px;
}
.summary.ok   { background:#dcfce7; color:#16a34a; border:1px solid #86efac; }
.summary.fail { background:#fee2e2; color:#dc2626; border:1px solid #fca5a5; }

.progress {
    height: 8px; border-radius: 100px;
    background: #e2e8f0; overflow: hidden; margin-bottom: 28px;
}
.progress-bar {
    height: 100%; border-radius: 100px;
    background: <?= $allGood ? '#22c55e' : '#ef4444' ?>;
    width: <?= round(($passed / $total) * 100) ?>%;
    transition: width .5s;
}

.section-title {
    font-size: .75rem; font-weight: 700; letter-spacing: .08em;
    text-transform: uppercase; color: #94a3b8;
    margin: 20px 0 8px;
}
.card {
    background: #fff; border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden; margin-bottom: 8px;
}
.row {
    display: flex; align-items: flex-start;
    gap: 12px; padding: 12px 16px;
    border-bottom: 1px solid #f8fafc;
    font-size: .875rem;
}
.row:last-child { border-bottom: none; }
.dot {
    width: 20px; height: 20px; border-radius: 50%;
    flex-shrink: 0; margin-top: 1px;
    display: flex; align-items: center; justify-content: center;
    font-size: .7rem; font-weight: 900;
}
.dot.pass { background: #dcfce7; color: #16a34a; }
.dot.fail { background: #fee2e2; color: #dc2626; }
.label { font-weight: 600; min-width: 160px; flex-shrink: 0; }
.detail { color: #475569; word-break: break-all; }
.detail.pass-text { color: #16a34a; }
.detail.fail-text { color: #dc2626; }

.warn {
    background: #fffbeb; border: 1px solid #fcd34d;
    border-radius: 10px; padding: 12px 16px;
    font-size: .82rem; color: #92400e;
    margin-top: 24px; font-weight: 500;
}
</style>
</head>
<body>
<div class="wrap">

    <h1>🧪 PayPal Integration Test</h1>
    <p class="sub">QuickChatPDF · <?= strtoupper($mode) ?> · <?= date('d M Y, H:i:s') ?></p>

    <div class="summary <?= $allGood ? 'ok' : 'fail' ?>">
        <?= $allGood
            ? '✅ All ' . $total . ' tests passed — ready to go!'
            : '❌ ' . $failed . ' test' . ($failed > 1 ? 's' : '') . ' failed — fix highlighted items below'
        ?>
    </div>

    <div class="progress"><div class="progress-bar"></div></div>

    <?php foreach ($grouped as $section => $items): ?>
    <div class="section-title"><?= $section ?></div>
    <div class="card">
        <?php foreach ($items as $item): ?>
        <div class="row">
            <div class="dot <?= $item['pass'] ? 'pass' : 'fail' ?>">
                <?= $item['pass'] ? '✓' : '✗' ?>
            </div>
            <div class="label"><?= htmlspecialchars($item['label']) ?></div>
            <div class="detail <?= $item['pass'] ? 'pass-text' : 'fail-text' ?>">
                <?= htmlspecialchars($item['detail']) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <div class="warn">
        ⚠️ <strong>Security:</strong> Delete <code>test.php</code> immediately after testing.
        It contains your PayPal credentials — never leave on a live server!
    </div>

</div>
</body>
</html>

