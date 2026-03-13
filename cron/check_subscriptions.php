<?php


define('CRON_RUN', true);
require_once __DIR__ . '/../config/app.php'; // loads DB, constants, .env



$db  = Database::getInstance();
$log = fn(string $msg) => print('[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL);

$log('── Subscription sync started ──────────────────────────');

// ── 1. Get PayPal access token ────────────────────────────────────────────
$token = getPayPalAccessToken();
if (!$token) {
    $log('ERROR: Could not get PayPal access token. Aborting.');
    exit(1);
}

// ── 2. Fetch all active subscriptions from YOUR subscriptions table ───────
$st = $db->prepare("
    SELECT 
        s.id          AS sub_row_id,
        s.user_id,
        s.plan,
        s.status      AS db_status,
        s.paypal_sub_id,
        u.email,
        u.plan        AS user_plan
    FROM subscriptions s
    JOIN users u ON u.id = s.user_id
    WHERE s.status IN ('active', 'cancelled')
      AND s.paypal_sub_id IS NOT NULL
      AND s.paypal_sub_id != ''
");
$st->execute();
$subscriptions = $st->fetchAll(\PDO::FETCH_ASSOC);

$log('Active subscriptions to check: ' . count($subscriptions));

if (empty($subscriptions)) {
    $log('Nothing to do. Exiting.');
    exit;
}

// ── 3. Process each subscription ─────────────────────────────────────────
$counts = ['active' => 0, 'downgraded' => 0, 'renewed' => 0, 'errors' => 0];

foreach ($subscriptions as $sub) {
    $subId  = $sub['paypal_sub_id'];
    $userId = (int) $sub['user_id'];

    try {
        $ppData = getPayPalSubscription($token, $subId);

        if (!$ppData) {
            $log("WARN: Could not fetch PayPal data for sub {$subId} (user #{$userId})");
            $counts['errors']++;
            continue;
        }

        $ppStatus  = $ppData['status']                                       ?? 'UNKNOWN';
        $renewsAt  = $ppData['billing_info']['next_billing_time']            ?? null;
        $lastPayment = $ppData['billing_info']['last_payment']['time']       ?? null;

        $log("User #{$userId} ({$sub['email']}) | Sub: {$subId} | PayPal status: {$ppStatus}");

        // ── ACTIVE — update renews_at and sync ────────────────
        if (in_array($ppStatus, ['ACTIVE', 'APPROVED'], true)) {
            $db->prepare("
                UPDATE subscriptions
                SET status     = 'active',
                    renews_at  = :renews,
                    updated_at = UTC_TIMESTAMP()
                WHERE id = :id
            ")->execute([
                'renews' => $renewsAt ? date('Y-m-d H:i:s', strtotime($renewsAt)) : null,
                'id'     => $sub['sub_row_id'],
            ]);

            syncRenewalPayment($db, $sub, $ppData, $log, $counts);

            $counts['active']++;
            continue;
        }

        if ($ppStatus === 'CANCELLED') {
            $db->prepare("
                UPDATE subscriptions
                SET status = 'cancelled',
                    cancelled_at = COALESCE(cancelled_at, UTC_TIMESTAMP()),
                    updated_at = UTC_TIMESTAMP()
                WHERE id = :id
            ")->execute(['id' => $sub['sub_row_id']]);
            continue;
        }

        // ── EXPIRED / SUSPENDED — downgrade user ──────────────
        if (in_array($ppStatus, ['EXPIRED', 'SUSPENDED', 'INACTIVE'], true)) {
            downgradeUser($db, $userId, $sub, $ppStatus, $log);
            $counts['downgraded']++;
        }

    } catch (\Throwable $e) {
        $log("ERROR processing user #{$userId}: " . $e->getMessage());
        $counts['errors']++;
    }

    usleep(300000); // 300ms — avoid PayPal rate limits
}

$log('── Done ──────────────────────────────────────────────────');
$log("Active: {$counts['active']} | Renewed: {$counts['renewed']} | Downgraded: {$counts['downgraded']} | Errors: {$counts['errors']}");


// ── Downgrade user to free ────────────────────────────────────────────────
function downgradeUser(\PDO $db, int $userId, array $sub, string $reason, callable $log): void
{
    $month = date('Y-m');

    // 1. Update subscriptions table
    $db->prepare("
        UPDATE subscriptions
        SET status       = 'cancelled',
            cancelled_at = UTC_TIMESTAMP(),
            updated_at   = UTC_TIMESTAMP()
        WHERE id = :id
    ")->execute(['id' => $sub['sub_row_id']]);

    // 2. Downgrade users table
    $db->prepare("
        UPDATE users
        SET plan                   = 'free',
            paypal_subscription_id = NULL,
            updated_at             = UTC_TIMESTAMP()
        WHERE id = :id
    ")->execute(['id' => $userId]);

    // 3. Cap usage_counters to free plan limits + clear bonuses
    $freeLimits = PLANS['free']['benefits'];
    $db->prepare("
        UPDATE usage_counters
        SET pdfs_uploaded   = LEAST(pdfs_uploaded,   :pdfs),
            chat_messages   = LEAST(chat_messages,   :chat),
            summaries       = LEAST(summaries,        :sum),
            quizzes         = LEAST(quizzes,          :quiz),
            bonus_pdfs      = 0,
            bonus_chats     = 0,
            bonus_summaries = 0,
            bonus_quizzes   = 0,
            updated_at      = UTC_TIMESTAMP()
        WHERE user_id = :uid AND month = :month
    ")->execute([
        'pdfs'  => $freeLimits['pdfs_per_month'],
        'chat'  => $freeLimits['chat_messages'],
        'sum'   => $freeLimits['summaries'],
        'quiz'  => $freeLimits['quizzes'],
        'uid'   => $userId,
        'month' => $month,
    ]);

    // 4. Log in credit_ledger (feature = 'subscription_expired')
    $st = $db->prepare("SELECT credits FROM users WHERE id = :id");
    $st->execute(['id' => $userId]);
    $balance = (int) $st->fetchColumn();

    $db->prepare("
        INSERT INTO credit_ledger
            (user_id, credit_change, credit_balance, feature, note)
        VALUES
            (:uid, 0, :balance, 'subscription_expired', :note)
    ")->execute([
        'uid'     => $userId,
        'balance' => $balance,
        'note'    => "PayPal sub {$sub['paypal_sub_id']} — status: {$reason}",
    ]);

    $log("DOWNGRADED user #{$userId} ({$sub['email']}) — reason: {$reason}");
}


// ── PayPal helpers ────────────────────────────────────────────────────────
function getPayPalAccessToken(): ?string
{
    $ch = curl_init(PAYPAL_API_BASE . '/v1/oauth2/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
        CURLOPT_USERPWD        => PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $res['access_token'] ?? null;
}

function getPayPalSubscription(string $token, string $subId): ?array
{
    $ch = curl_init(PAYPAL_API_BASE . '/v1/billing/subscriptions/' . $subId);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ],
    ]);
    $res      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$res) return null;
    return json_decode($res, true);
}

function syncRenewalPayment(\PDO $db, array $sub, array $ppData, callable $log, array &$counts): void
{
    $lastPaymentTime = $ppData['billing_info']['last_payment']['time'] ?? null;
    if (!$lastPaymentTime) {
        return;
    }

    $paymentRef = buildSubscriptionPaymentRef($sub['paypal_sub_id'], $lastPaymentTime);
    if (paymentExists($db, $paymentRef) || promoteInitialSubscriptionPaymentRef($db, $sub['paypal_sub_id'], $paymentRef)) {
        return;
    }

    $plan = $sub['plan'];
    if (!isset(PLANS[$plan])) {
        return;
    }

    $credits  = (int) PLANS[$plan]['monthly_credits'];
    $amount   = (float) ($ppData['billing_info']['last_payment']['amount']['value'] ?? PLANS[$plan]['price']);
    $currency = $ppData['billing_info']['last_payment']['amount']['currency_code'] ?? 'USD';

    $db->beginTransaction();

    try {
        $db->prepare("
            UPDATE users
            SET plan = :plan,
                paypal_subscription_id = :sub,
                credits = credits + :credits,
                updated_at = UTC_TIMESTAMP()
            WHERE id = :id
        ")->execute([
            'plan'    => $plan,
            'sub'     => $sub['paypal_sub_id'],
            'credits' => $credits,
            'id'      => $sub['user_id'],
        ]);

        $st = $db->prepare("SELECT credits FROM users WHERE id = :id");
        $st->execute(['id' => $sub['user_id']]);
        $balance = (int) $st->fetchColumn();

        $db->prepare("
            INSERT INTO credit_ledger (user_id, credit_change, credit_balance, feature, note)
            VALUES (:uid, :change, :balance, 'subscription', :note)
        ")->execute([
            'uid'     => $sub['user_id'],
            'change'  => $credits,
            'balance' => $balance,
            'note'    => 'PayPal renewal ' . $sub['paypal_sub_id'],
        ]);

        $db->prepare("
            INSERT INTO payments (user_id, type, plan, credits_added, amount, currency, paypal_txn_id, status, created_at)
            VALUES (:uid, 'subscription', :plan, :credits, :amount, :currency, :txn, 'completed', UTC_TIMESTAMP())
        ")->execute([
            'uid'      => $sub['user_id'],
            'plan'     => $plan,
            'credits'  => $credits,
            'amount'   => number_format($amount, 2, '.', ''),
            'currency' => strtoupper($currency),
            'txn'      => $paymentRef,
        ]);

        $db->commit();
        $counts['renewed']++;
        $log("RENEWED user #{$sub['user_id']} ({$sub['email']}) +{$credits} credits");
    } catch (\Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

function paymentExists(\PDO $db, string $paymentRef): bool
{
    $st = $db->prepare("
        SELECT id FROM payments
        WHERE paypal_txn_id = :txn
        LIMIT 1
    ");
    $st->execute(['txn' => $paymentRef]);
    return (bool) $st->fetchColumn();
}

function buildSubscriptionPaymentRef(string $subId, string $paidAt): string
{
    return 'SUB:' . $subId . ':' . gmdate('YmdHis', strtotime($paidAt));
}

function promoteInitialSubscriptionPaymentRef(\PDO $db, string $subId, string $paymentRef): bool
{
    $initialRef = 'SUB:' . $subId;
    if ($paymentRef === $initialRef) {
        return paymentExists($db, $paymentRef);
    }

    $st = $db->prepare("
        SELECT id FROM payments
        WHERE paypal_txn_id = :txn
        LIMIT 1
    ");
    $st->execute(['txn' => $initialRef]);
    $rowId = $st->fetchColumn();

    if (!$rowId) {
        return false;
    }

    $db->prepare("
        UPDATE payments
        SET paypal_txn_id = :new_txn
        WHERE id = :id
    ")->execute([
        'new_txn' => $paymentRef,
        'id'      => $rowId,
    ]);

    return true;
}
