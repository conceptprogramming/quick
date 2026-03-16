<?php
namespace Controllers;

use Database;
use Core\Session;
use Services\MailService;

class PaymentController
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function confirm(): void
    {
        header('Content-Type: application/json');

        try {
            $userId = $this->session->userId();
            if (!$userId) {
                $this->respond(['success' => false, 'message' => 'Not authenticated']);
            }

            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $type = $body['type'] ?? '';

            if ($type === 'subscription') {
                $this->handleSubscription($userId, $body);
                return;
            }

            if ($type === 'topup') {
                $this->handleTopup($userId, $body);
                return;
            }

            $this->respond(['success' => false, 'message' => 'Invalid payment type']);
        } catch (\Throwable $e) {
            error_log('[Payment Confirm] ' . $e->getMessage());
            $message = APP_ENV === 'local'
                ? 'Payment processing failed: ' . $e->getMessage()
                : 'Payment processing failed after PayPal approval. Please contact support if credits do not appear shortly.';
            $this->respond(['success' => false, 'message' => $message]);
        }
    }

    private function handleSubscription(int $userId, array $body): void
    {
        $plan  = $body['plan'] ?? '';
        $subId = $body['subscription_id'] ?? '';

        if (!isset(PLANS[$plan]) || !$subId) {
            $this->respond(['success' => false, 'message' => 'Invalid plan or subscription ID']);
        }

        // The browser approval is authoritative for the initial upgrade.
        // PayPal API hydration is best-effort here; webhook/cron will reconcile metadata later.
        $token = $this->getPayPalAccessToken();
        $paypalSub = $token ? $this->getPayPalSubscription($token, $subId) : null;

        $paypalPlanId = $paypalSub['plan_id'] ?? '';
        $expectedPlan = PLANS[$plan]['paypal_plan_id'] ?? '';
        if ($expectedPlan && $paypalPlanId && $paypalPlanId !== $expectedPlan) {
            $this->respond(['success' => false, 'message' => 'Selected plan does not match PayPal subscription.']);
        }

        $db   = Database::getInstance();
        $user = $this->getUser($db, $userId);
        if (!$user) {
            $this->respond(['success' => false, 'message' => 'User not found']);
        }

        $paidAt     = $paypalSub['billing_info']['last_payment']['time'] ?? $paypalSub['create_time'] ?? null;
        $paymentRef = $this->buildSubscriptionPaymentRef($subId, $paidAt);
        $renewsAt   = $this->formatPayPalTime($paypalSub['billing_info']['next_billing_time'] ?? null);
        $startedAt  = $this->formatPayPalTime($paypalSub['start_time'] ?? null) ?? gmdate('Y-m-d H:i:s');
        $amount     = (float) ($paypalSub['billing_info']['last_payment']['amount']['value'] ?? PLANS[$plan]['price']);
        $currency   = $paypalSub['billing_info']['last_payment']['amount']['currency_code'] ?? 'USD';
        $creditGain = (int) PLANS[$plan]['monthly_credits'];

        $alreadyProcessed = $this->paymentExists($db, $paymentRef)
            || $this->promoteInitialSubscriptionPaymentRef($db, $subId, $paymentRef);
        $db->beginTransaction();

        try {
            $this->cancelOtherSubscriptions($db, $userId, $subId);
            $this->upsertSubscriptionRow($db, $userId, $plan, $subId, 'active', $startedAt, $renewsAt);

            if (!$alreadyProcessed) {
                $this->applySubscriptionChange($db, $user, $plan, $subId, $creditGain);
                $this->recordPayment(
                    $db,
                    $userId,
                    'subscription',
                    $plan,
                    $creditGain,
                    $amount > 0 ? $amount : (float) PLANS[$plan]['price'],
                    $currency,
                    $paymentRef
                );
            } else {
                $this->syncUserSubscription($db, $userId, $plan, $subId);
            }

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        $message = $alreadyProcessed
            ? 'Subscription is active.'
            : 'Subscribed to ' . PLANS[$plan]['name'] . ' plan! ' . number_format($creditGain) . ' credits added.';

        if (!$alreadyProcessed) {
            $this->sendSubscriptionEmail($user['email'] ?? '', PLANS[$plan]['name'], $creditGain, $amount > 0 ? $amount : (float) PLANS[$plan]['price'], $currency);
        }

        $this->respond([
            'success' => true,
            'message' => $message,
        ]);
    }

    private function handleTopup(int $userId, array $body): void
    {
        $pack    = $body['pack'] ?? '';
        $orderId = $body['order_id'] ?? '';

        if (!isset(TOPUP_PACKS[$pack]) || !$orderId) {
            $this->respond(['success' => false, 'message' => 'Invalid pack or order ID']);
        }

        $db   = Database::getInstance();
        $user = $this->getUser($db, $userId);
        if (!$user) {
            $this->respond(['success' => false, 'message' => 'User not found']);
        }

        if (($user['plan'] ?? 'free') === 'free' || empty($user['paypal_subscription_id'])) {
            $this->respond(['success' => false, 'message' => 'Top-ups are available only for active paid subscribers.']);
        }

        $token = $this->getPayPalAccessToken();
        if (!$token) {
            $this->respond(['success' => false, 'message' => 'Could not verify payment with PayPal.']);
        }

        $order = $this->getPayPalOrder($token, $orderId);
        if (!$order) {
            $this->respond(['success' => false, 'message' => 'Could not fetch order details from PayPal.']);
        }

        $status = strtoupper($order['status'] ?? '');
        if ($status !== 'COMPLETED') {
            $this->respond(['success' => false, 'message' => 'Payment not completed']);
        }

        $capture  = $order['purchase_units'][0]['payments']['captures'][0] ?? [];
        $txnId    = $capture['id'] ?? $orderId;
        $paid     = (float) ($capture['amount']['value'] ?? $order['purchase_units'][0]['amount']['value'] ?? 0);
        $currency = $capture['amount']['currency_code'] ?? $order['purchase_units'][0]['amount']['currency_code'] ?? 'USD';
        $expected = (float) TOPUP_PACKS[$pack]['price'];

        if ($paid < $expected) {
            $this->respond(['success' => false, 'message' => 'Payment amount mismatch']);
        }

        if ($this->paymentExists($db, $txnId)) {
            $this->respond(['success' => true, 'message' => 'Top-up already processed.']);
        }

        $packConfig = TOPUP_PACKS[$pack];
        $units = (int) $packConfig['units'];
        $db->beginTransaction();

        try {
            if (($packConfig['type'] ?? 'credits') === 'credits') {
                $this->addCreditsWithLedger($db, $userId, $units, 'topup', $orderId, 'Top-up pack: ' . $packConfig['name']);
            } else {
                $this->addUsageBonus($db, $userId, $packConfig['type'], $units);
                $this->insertLedgerEntry($db, $userId, 0, 'topup', $orderId, $packConfig['name'] . ' +' . $units . ' ' . $packConfig['unit_label']);
            }

            $this->recordPayment($db, $userId, 'topup', $pack, $units, $paid, $currency, $txnId);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        $this->sendTopupEmail(
            $user['email'] ?? '',
            $packConfig['name'],
            number_format($units) . ' ' . $packConfig['unit_label'],
            $paid,
            $currency
        );

        $this->respond([
            'success' => true,
            'message' => '+' . number_format($units) . ' ' . $packConfig['unit_label'] . ' added!',
        ]);
    }

    public function webhook(): void
    {
        http_response_code(200);
        header('Content-Type: application/json');

        try {
            $body    = file_get_contents('php://input');
            $headers = function_exists('getallheaders') ? getallheaders() : [];

            if (!$this->verifyWebhookSignature($headers, $body)) {
                error_log('[PayPal Webhook] Signature verification failed');
                echo json_encode(['status' => 'ignored']);
                return;
            }

            $event = json_decode($body, true) ?? [];
            $type  = $event['event_type'] ?? '';

            switch ($type) {
                case 'BILLING.SUBSCRIPTION.ACTIVATED':
                case 'BILLING.SUBSCRIPTION.UPDATED':
                    $this->syncSubscriptionWebhook($event);
                    break;

                case 'BILLING.SUBSCRIPTION.CANCELLED':
                    $this->markSubscriptionCancelled($event);
                    break;

                case 'BILLING.SUBSCRIPTION.EXPIRED':
                case 'BILLING.SUBSCRIPTION.SUSPENDED':
                    $this->downgradeFromWebhook($event, strtolower(substr(strrchr($type, '.'), 1)));
                    break;

                case 'PAYMENT.SALE.COMPLETED':
                case 'BILLING.SUBSCRIPTION.PAYMENT.COMPLETED':
                    $this->processRecurringPaymentWebhook($event);
                    break;
            }

            echo json_encode(['status' => 'ok']);
        } catch (\Throwable $e) {
            error_log('[PayPal Webhook] Error: ' . $e->getMessage());
            echo json_encode(['status' => 'error']);
        }
    }

    public function webhookInfo(): void
    {
        header('Content-Type: text/html; charset=UTF-8');
        http_response_code(200);

        echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<title>PayPal Webhook Endpoint</title>';
        echo '<style>
            body{margin:0;font-family:Inter,system-ui,sans-serif;background:#f8fafc;color:#0f172a;display:flex;min-height:100vh;align-items:center;justify-content:center;padding:24px}
            .card{max-width:720px;background:#fff;border:1px solid #e2e8f0;border-radius:24px;padding:32px;box-shadow:0 24px 60px rgba(15,23,42,.08)}
            .pill{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border-radius:999px;background:#eef2ff;color:#4f46e5;font-weight:700;font-size:.82rem}
            h1{margin:16px 0 10px;font-size:2rem;line-height:1.1}
            p{margin:0 0 12px;color:#475569;line-height:1.6}
            code{background:#f1f5f9;border-radius:8px;padding:2px 8px}
        </style></head><body><div class="card">';
        echo '<div class="pill">PayPal Webhook Endpoint</div>';
        echo '<h1>This endpoint is available.</h1>';
        echo '<p>Use <code>POST</code> requests from PayPal for live webhook delivery. Opening this URL in a browser with <code>GET</code> is only a manual health check.</p>';
        echo '<p>Expected route: <code>/payment/webhook</code></p>';
        echo '</div></body></html>';
        exit;
    }

    public function cancelSubscription(): void
    {
        header('Content-Type: application/json');

        try {
            $userId = $this->session->userId();
            if (!$userId) {
                $this->respond(['success' => false, 'message' => 'Not authenticated']);
            }

            $db = Database::getInstance();
            $user = $this->getUser($db, $userId);
            $subId = $user['paypal_subscription_id'] ?? '';
            if (!$subId || ($user['plan'] ?? 'free') === 'free') {
                $this->respond(['success' => false, 'message' => 'No active subscription found.']);
            }

            $token = $this->getPayPalAccessToken();
            if (!$token) {
                $this->respond(['success' => false, 'message' => 'Could not connect to PayPal.']);
            }

            $paypalSub = $this->getPayPalSubscription($token, $subId);
            if ($paypalSub) {
                $startedAt = $this->formatPayPalTime($paypalSub['start_time'] ?? null) ?? gmdate('Y-m-d H:i:s');
                $renewsAt = $this->formatPayPalTime($paypalSub['billing_info']['next_billing_time'] ?? null);
                $this->upsertSubscriptionRow(
                    $db,
                    $userId,
                    (string) ($user['plan'] ?? 'free'),
                    $subId,
                    'cancelled',
                    $startedAt,
                    $renewsAt
                );
            }

            if (!$this->cancelPayPalSubscription($token, $subId)) {
                $this->respond(['success' => false, 'message' => 'PayPal could not cancel the subscription.']);
            }

            $db->prepare("
                UPDATE subscriptions
                SET status = 'cancelled',
                    cancelled_at = COALESCE(cancelled_at, UTC_TIMESTAMP()),
                    updated_at = UTC_TIMESTAMP()
                WHERE user_id = :uid
                  AND (
                        paypal_sub_id = :sub
                        OR status = 'active'
                      )
            ")->execute([
                'uid' => $userId,
                'sub' => $subId,
            ]);

            $this->respond([
                'success' => true,
                'message' => 'Subscription cancelled. Your current plan stays active until the end date of the paid period.',
            ]);
        } catch (\Throwable $e) {
            error_log('[Subscription Cancel] ' . $e->getMessage());
            $this->respond(['success' => false, 'message' => 'Could not cancel subscription right now.']);
        }
    }

    private function syncSubscriptionWebhook(array $event): void
    {
        $subId = $event['resource']['id'] ?? '';
        if (!$subId) {
            return;
        }

        $token = $this->getPayPalAccessToken();
        if (!$token) {
            return;
        }

        $paypalSub = $this->getPayPalSubscription($token, $subId);
        if (!$paypalSub) {
            return;
        }

        $db = Database::getInstance();
        $st = $db->prepare("
            SELECT user_id, plan
            FROM subscriptions
            WHERE paypal_sub_id = :sub
            ORDER BY id DESC
            LIMIT 1
        ");
        $st->execute(['sub' => $subId]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return;
        }

        $db->beginTransaction();

        try {
            $this->upsertSubscriptionRow(
                $db,
                (int) $row['user_id'],
                $row['plan'],
                $subId,
                'active',
                $this->formatPayPalTime($paypalSub['start_time'] ?? null) ?? gmdate('Y-m-d H:i:s'),
                $this->formatPayPalTime($paypalSub['billing_info']['next_billing_time'] ?? null)
            );
            $this->syncUserSubscription($db, (int) $row['user_id'], $row['plan'], $subId);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    private function processRecurringPaymentWebhook(array $event): void
    {
        $resource = $event['resource'] ?? [];
        $subId = $resource['billing_agreement_id']
            ?? $resource['subscription_id']
            ?? $resource['supplementary_data']['related_ids']['subscription_id']
            ?? '';

        if (!$subId) {
            return;
        }

        $token = $this->getPayPalAccessToken();
        if (!$token) {
            return;
        }

        $paypalSub = $this->getPayPalSubscription($token, $subId);
        if (!$paypalSub) {
            return;
        }

        $db = Database::getInstance();
        $st = $db->prepare("
            SELECT user_id, plan
            FROM subscriptions
            WHERE paypal_sub_id = :sub
            ORDER BY id DESC
            LIMIT 1
        ");
        $st->execute(['sub' => $subId]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);

        if (!$row || !isset(PLANS[$row['plan']])) {
            return;
        }

        $paidAt = $paypalSub['billing_info']['last_payment']['time'] ?? null;
        if (!$paidAt) {
            return;
        }

        $paymentRef = $this->buildSubscriptionPaymentRef($subId, $paidAt);
        if ($this->paymentExists($db, $paymentRef) || $this->promoteInitialSubscriptionPaymentRef($db, $subId, $paymentRef)) {
            return;
        }

        $creditGain = (int) PLANS[$row['plan']]['monthly_credits'];
        $amount     = (float) ($paypalSub['billing_info']['last_payment']['amount']['value'] ?? PLANS[$row['plan']]['price']);
        $currency   = $paypalSub['billing_info']['last_payment']['amount']['currency_code'] ?? 'USD';
        $renewsAt   = $this->formatPayPalTime($paypalSub['billing_info']['next_billing_time'] ?? null);

        $db->beginTransaction();

        try {
            $this->upsertSubscriptionRow(
                $db,
                (int) $row['user_id'],
                $row['plan'],
                $subId,
                'active',
                $this->formatPayPalTime($paypalSub['start_time'] ?? null) ?? gmdate('Y-m-d H:i:s'),
                $renewsAt
            );
            $this->syncUserSubscription($db, (int) $row['user_id'], $row['plan'], $subId);
            $this->addCreditsWithLedger($db, (int) $row['user_id'], $creditGain, 'subscription', null, 'PayPal renewal ' . $subId);
            $this->recordPayment(
                $db,
                (int) $row['user_id'],
                'subscription',
                $row['plan'],
                $creditGain,
                $amount > 0 ? $amount : (float) PLANS[$row['plan']]['price'],
                $currency,
                $paymentRef
            );
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    private function downgradeFromWebhook(array $event, string $status): void
    {
        $subId = $event['resource']['id'] ?? '';
        if (!$subId) {
            return;
        }

        $db = Database::getInstance();
        $st = $db->prepare("
            SELECT s.id AS sub_row_id, s.user_id, s.paypal_sub_id, u.email
            FROM subscriptions s
            JOIN users u ON u.id = s.user_id
            WHERE s.paypal_sub_id = :sub
            ORDER BY s.id DESC
            LIMIT 1
        ");
        $st->execute(['sub' => $subId]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return;
        }

        $db->beginTransaction();

        try {
            $this->downgradeUser($db, (int) $row['user_id'], $row, strtoupper($status));
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    private function markSubscriptionCancelled(array $event): void
    {
        $subId = $event['resource']['id'] ?? '';
        if (!$subId) {
            return;
        }

        $db = Database::getInstance();
        $db->prepare("
            UPDATE subscriptions
            SET status = 'cancelled',
                cancelled_at = COALESCE(cancelled_at, UTC_TIMESTAMP()),
                updated_at = UTC_TIMESTAMP()
            WHERE paypal_sub_id = :sub
        ")->execute(['sub' => $subId]);
    }

    private function downgradeUser(\PDO $db, int $userId, array $sub, string $reason): void
    {
        $month = date('Y-m');
        $freeLimits = PLANS['free']['benefits'];

        $db->prepare("
            UPDATE subscriptions
            SET status = 'cancelled',
                cancelled_at = UTC_TIMESTAMP(),
                updated_at = UTC_TIMESTAMP()
            WHERE id = :id
        ")->execute(['id' => $sub['sub_row_id']]);

        $db->prepare("
            UPDATE users
            SET plan = 'free',
                paypal_subscription_id = NULL,
                updated_at = UTC_TIMESTAMP()
            WHERE id = :id
        ")->execute(['id' => $userId]);

        $db->prepare("
            UPDATE usage_counters
            SET pdfs_uploaded   = LEAST(pdfs_uploaded, :pdfs),
                chat_messages   = LEAST(chat_messages, :chat),
                summaries       = LEAST(summaries, :sum),
                quizzes         = LEAST(quizzes, :quiz),
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

        $st = $db->prepare("SELECT credits FROM users WHERE id = :id");
        $st->execute(['id' => $userId]);
        $balance = (int) $st->fetchColumn();

        $db->prepare("
            INSERT INTO credit_ledger (user_id, credit_change, credit_balance, feature, note)
            VALUES (:uid, 0, :balance, 'subscription_expired', :note)
        ")->execute([
            'uid'     => $userId,
            'balance' => $balance,
            'note'    => 'PayPal sub ' . ($sub['paypal_sub_id'] ?? '') . ' — status: ' . $reason,
        ]);
    }

    private function applySubscriptionChange(\PDO $db, array $user, string $plan, string $subId, int $creditGain): void
    {
        $userId       = (int) $user['id'];
        $previousPlan = $user['plan'] ?? 'free';

        $db->prepare("
            UPDATE users
            SET plan = :plan,
                paypal_subscription_id = :sub,
                credits = credits + :credits,
                updated_at = UTC_TIMESTAMP()
            WHERE id = :id
        ")->execute([
            'plan'    => $plan,
            'sub'     => $subId,
            'credits' => $creditGain,
            'id'      => $userId,
        ]);

        $this->syncUsageBonuses($db, $userId, $previousPlan, $plan);
        $this->insertLedgerEntry($db, $userId, $creditGain, 'subscription', null, 'PayPal subscription ' . $subId);
    }

    private function syncUserSubscription(\PDO $db, int $userId, string $plan, string $subId): void
    {
        $db->prepare("
            UPDATE users
            SET plan = :plan,
                paypal_subscription_id = :sub,
                updated_at = UTC_TIMESTAMP()
            WHERE id = :id
        ")->execute([
            'plan' => $plan,
            'sub'  => $subId,
            'id'   => $userId,
        ]);
    }

    private function syncUsageBonuses(\PDO $db, int $userId, string $previousPlan, string $newPlan): void
    {
        if ($previousPlan === $newPlan || !isset(PLANS[$previousPlan], PLANS[$newPlan])) {
            return;
        }

        $month = date('Y-m');
        $st = $db->prepare("
            SELECT pdfs_uploaded, chat_messages, summaries, quizzes
            FROM usage_counters
            WHERE user_id = :id AND month = :month
            LIMIT 1
        ");
        $st->execute(['id' => $userId, 'month' => $month]);
        $usage = $st->fetch(\PDO::FETCH_ASSOC) ?: [
            'pdfs_uploaded' => 0,
            'chat_messages' => 0,
            'summaries' => 0,
            'quizzes' => 0,
        ];

        $prevPrice = (float) (PLANS[$previousPlan]['price'] ?? 0);
        $newPrice  = (float) (PLANS[$newPlan]['price'] ?? 0);

        if ($newPrice > $prevPrice) {
            $oldLimits      = PLANS[$previousPlan]['benefits'];
            $bonusPdfs      = max(0, $oldLimits['pdfs_per_month'] - (int) $usage['pdfs_uploaded']);
            $bonusChats     = max(0, $oldLimits['chat_messages'] - (int) $usage['chat_messages']);
            $bonusSummaries = max(0, $oldLimits['summaries'] - (int) $usage['summaries']);
            $bonusQuizzes   = max(0, $oldLimits['quizzes'] - (int) $usage['quizzes']);

            $db->prepare("
                INSERT INTO usage_counters
                    (user_id, month, pdfs_uploaded, chat_messages, summaries, quizzes,
                     bonus_pdfs, bonus_chats, bonus_summaries, bonus_quizzes)
                VALUES
                    (:id, :month, :pdfs, :chat, :sum, :quiz, :bpdfs, :bchats, :bsum, :bquiz)
                ON DUPLICATE KEY UPDATE
                    bonus_pdfs      = :upd_bpdfs,
                    bonus_chats     = :upd_bchats,
                    bonus_summaries = :upd_bsum,
                    bonus_quizzes   = :upd_bquiz,
                    updated_at      = UTC_TIMESTAMP()
            ")->execute([
                'id'     => $userId,
                'month'  => $month,
                'pdfs'   => (int) $usage['pdfs_uploaded'],
                'chat'   => (int) $usage['chat_messages'],
                'sum'    => (int) $usage['summaries'],
                'quiz'   => (int) $usage['quizzes'],
                'bpdfs'  => $bonusPdfs,
                'bchats' => $bonusChats,
                'bsum'   => $bonusSummaries,
                'bquiz'  => $bonusQuizzes,
                'upd_bpdfs'  => $bonusPdfs,
                'upd_bchats' => $bonusChats,
                'upd_bsum'   => $bonusSummaries,
                'upd_bquiz'  => $bonusQuizzes,
            ]);
            return;
        }

        $newLimits = PLANS[$newPlan]['benefits'];
        $db->prepare("
            UPDATE usage_counters
            SET pdfs_uploaded   = LEAST(pdfs_uploaded, :pdfs),
                chat_messages   = LEAST(chat_messages, :chat),
                summaries       = LEAST(summaries, :sum),
                quizzes         = LEAST(quizzes, :quiz),
                bonus_pdfs      = 0,
                bonus_chats     = 0,
                bonus_summaries = 0,
                bonus_quizzes   = 0,
                updated_at      = UTC_TIMESTAMP()
            WHERE user_id = :id AND month = :month
        ")->execute([
            'pdfs'  => $newLimits['pdfs_per_month'],
            'chat'  => $newLimits['chat_messages'],
            'sum'   => $newLimits['summaries'],
            'quiz'  => $newLimits['quizzes'],
            'id'    => $userId,
            'month' => $month,
        ]);
    }

    private function cancelOtherSubscriptions(\PDO $db, int $userId, string $activeSubId): void
    {
        $db->prepare("
            UPDATE subscriptions
            SET status = 'cancelled',
                cancelled_at = UTC_TIMESTAMP(),
                updated_at = UTC_TIMESTAMP()
            WHERE user_id = :uid
              AND paypal_sub_id <> :sub
              AND status = 'active'
        ")->execute([
            'uid' => $userId,
            'sub' => $activeSubId,
        ]);
    }

    private function upsertSubscriptionRow(
        \PDO $db,
        int $userId,
        string $plan,
        string $subId,
        string $status,
        string $startedAt,
        ?string $renewsAt
    ): void {
        $st = $db->prepare("
            SELECT id FROM subscriptions
            WHERE paypal_sub_id = :sub
            LIMIT 1
        ");
        $st->execute(['sub' => $subId]);
        $existingId = $st->fetchColumn();

        if ($existingId) {
            $db->prepare("
                UPDATE subscriptions
                SET user_id = :uid,
                    plan = :plan,
                    status = :status,
                    renews_at = :renews,
                    cancelled_at = CASE
                        WHEN :status_cancelled = 'cancelled' THEN COALESCE(cancelled_at, UTC_TIMESTAMP())
                        ELSE NULL
                    END,
                    updated_at = UTC_TIMESTAMP()
                WHERE id = :id
            ")->execute([
                'uid'    => $userId,
                'plan'   => $plan,
                'status' => $status,
                'renews' => $renewsAt,
                'status_cancelled' => $status,
                'id'     => $existingId,
            ]);
            return;
        }

        $db->prepare("
            INSERT INTO subscriptions
                (user_id, plan, status, paypal_sub_id, started_at, renews_at, cancelled_at, created_at, updated_at)
            VALUES
                (:uid, :plan, :status, :sub, :started, :renews, :cancelled_at, UTC_TIMESTAMP(), UTC_TIMESTAMP())
        ")->execute([
            'uid'     => $userId,
            'plan'    => $plan,
            'status'  => $status,
            'sub'     => $subId,
            'started' => $startedAt,
            'renews'  => $renewsAt,
            'cancelled_at' => $status === 'cancelled' ? gmdate('Y-m-d H:i:s') : null,
        ]);
    }

    private function addCreditsWithLedger(
        \PDO $db,
        int $userId,
        int $amount,
        string $feature,
        ?string $orderId = null,
        ?string $note = null
    ): void {
        $db->prepare("
            UPDATE users
            SET credits = credits + :amt,
                updated_at = UTC_TIMESTAMP()
            WHERE id = :id
        ")->execute([
            'amt' => $amount,
            'id'  => $userId,
        ]);

        $this->insertLedgerEntry($db, $userId, $amount, $feature, $orderId, $note);
    }

    private function addUsageBonus(\PDO $db, int $userId, string $bonusType, int $amount): void
    {
        $columnMap = [
            'bonus_pdfs' => 'bonus_pdfs',
            'bonus_chats' => 'bonus_chats',
            'bonus_summaries' => 'bonus_summaries',
            'bonus_quizzes' => 'bonus_quizzes',
        ];

        $column = $columnMap[$bonusType] ?? null;
        if (!$column) {
            throw new \InvalidArgumentException('Invalid top-up type');
        }

        $month = date('Y-m');
        $db->prepare("
            INSERT INTO usage_counters (user_id, month, {$column})
            VALUES (:uid, :month, :amount)
            ON DUPLICATE KEY UPDATE {$column} = {$column} + :update_amount,
                                    updated_at = UTC_TIMESTAMP()
        ")->execute([
            'uid' => $userId,
            'month' => $month,
            'amount' => $amount,
            'update_amount' => $amount,
        ]);
    }

    private function insertLedgerEntry(
        \PDO $db,
        int $userId,
        int $change,
        string $feature,
        ?string $orderId = null,
        ?string $note = null
    ): void {
        $st = $db->prepare("SELECT credits FROM users WHERE id = :id");
        $st->execute(['id' => $userId]);
        $balance = (int) $st->fetchColumn();

        $db->prepare("
            INSERT INTO credit_ledger (user_id, credit_change, credit_balance, feature, note, paypal_order_id)
            VALUES (:uid, :change, :balance, :feature, :note, :oid)
        ")->execute([
            'uid'     => $userId,
            'change'  => $change,
            'balance' => $balance,
            'feature' => $feature,
            'note'    => $note,
            'oid'     => $orderId,
        ]);
    }

    private function recordPayment(
        \PDO $db,
        int $userId,
        string $type,
        ?string $plan,
        int $creditsAdded,
        float $amount,
        string $currency,
        string $txnId
    ): void {
        $db->prepare("
            INSERT INTO payments (user_id, type, plan, credits_added, amount, currency, paypal_txn_id, status, created_at)
            VALUES (:uid, :type, :plan, :credits, :amount, :currency, :txn, 'completed', UTC_TIMESTAMP())
        ")->execute([
            'uid'      => $userId,
            'type'     => $type,
            'plan'     => $plan,
            'credits'  => $creditsAdded,
            'amount'   => number_format($amount, 2, '.', ''),
            'currency' => strtoupper($currency ?: 'USD'),
            'txn'      => $txnId,
        ]);
    }

    private function paymentExists(\PDO $db, string $txnId): bool
    {
        $st = $db->prepare("
            SELECT id FROM payments
            WHERE paypal_txn_id = :txn
            LIMIT 1
        ");
        $st->execute(['txn' => $txnId]);
        return (bool) $st->fetchColumn();
    }

    private function promoteInitialSubscriptionPaymentRef(\PDO $db, string $subId, string $paymentRef): bool
    {
        $initialRef = 'SUB:' . $subId;
        if ($paymentRef === $initialRef) {
            return $this->paymentExists($db, $paymentRef);
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

    private function getUser(\PDO $db, int $userId): ?array
    {
        $st = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $st->execute(['id' => $userId]);
        return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    private function buildSubscriptionPaymentRef(string $subId, ?string $paidAt): string
    {
        if (!$paidAt) {
            return 'SUB:' . $subId;
        }

        return 'SUB:' . $subId . ':' . gmdate('YmdHis', strtotime($paidAt));
    }

    private function formatPayPalTime(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $timestamp = strtotime($value);
        return $timestamp ? gmdate('Y-m-d H:i:s', $timestamp) : null;
    }

    private function respond(array $payload): void
    {
        echo json_encode($payload);
        exit;
    }

    private function sendSubscriptionEmail(string $email, string $planName, int $creditsAdded, float $amount, string $currency): void
    {
        if ($email === '') {
            return;
        }

        try {
            (new MailService())->sendSubscriptionReceipt($email, $planName, $creditsAdded, $amount, $currency);
        } catch (\Throwable $e) {
            error_log('[Mail Subscription] ' . $e->getMessage());
        }
    }

    private function sendTopupEmail(string $email, string $packName, string $unitsLabel, float $amount, string $currency): void
    {
        if ($email === '') {
            return;
        }

        try {
            (new MailService())->sendTopupReceipt($email, $packName, $unitsLabel, $amount, $currency);
        } catch (\Throwable $e) {
            error_log('[Mail Topup] ' . $e->getMessage());
        }
    }

    private function verifyWebhookSignature(array $headers, string $body): bool
    {
        $normalized = [];
        foreach ($headers as $key => $value) {
            $normalized[strtoupper($key)] = $value;
        }

        $transmissionId   = $normalized['PAYPAL-TRANSMISSION-ID'] ?? '';
        $transmissionTime = $normalized['PAYPAL-TRANSMISSION-TIME'] ?? '';
        $certUrl          = $normalized['PAYPAL-CERT-URL'] ?? '';
        $authAlgo         = $normalized['PAYPAL-AUTH-ALGO'] ?? '';
        $signature        = $normalized['PAYPAL-TRANSMISSION-SIG'] ?? '';

        if (!$transmissionId || !$transmissionTime || !$certUrl || !$signature) {
            return false;
        }

        $token = $this->getPayPalAccessToken();
        if (!$token) {
            return false;
        }

        $payload = json_encode([
            'auth_algo'         => $authAlgo,
            'cert_url'          => $certUrl,
            'transmission_id'   => $transmissionId,
            'transmission_sig'  => $signature,
            'transmission_time' => $transmissionTime,
            'webhook_id'        => PAYPAL_WEBHOOK_ID,
            'webhook_event'     => json_decode($body, true),
        ]);

        $ch = curl_init(PAYPAL_API_BASE . '/v1/notifications/verify-webhook-signature');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
        ]);
        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return ($res['verification_status'] ?? '') === 'SUCCESS';
    }

    private function getPayPalAccessToken(): ?string
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

    private function getPayPalSubscription(string $token, string $subId): ?array
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
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$res) {
            return null;
        }

        return json_decode($res, true);
    }

    private function getPayPalOrder(string $token, string $orderId): ?array
    {
        $ch = curl_init(PAYPAL_API_BASE . '/v2/checkout/orders/' . $orderId);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
        ]);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$res) {
            return null;
        }

        return json_decode($res, true);
    }

    private function cancelPayPalSubscription(string $token, string $subId): bool
    {
        $subscription = $this->getPayPalSubscription($token, $subId);
        $currentStatus = strtoupper((string) ($subscription['status'] ?? ''));

        if (in_array($currentStatus, ['CANCELLED', 'EXPIRED'], true)) {
            return true;
        }

        $payload = json_encode(['reason' => 'Cancelled by subscriber from QuickChatPDF']);
        $ch = curl_init(PAYPAL_API_BASE . '/v1/billing/subscriptions/' . $subId . '/cancel');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
        ]);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 204) {
            return true;
        }

        $latest = $this->getPayPalSubscription($token, $subId);
        $latestStatus = strtoupper((string) ($latest['status'] ?? ''));

        if (in_array($latestStatus, ['CANCELLED', 'EXPIRED'], true)) {
            return true;
        }

        $errorSummary = 'HTTP ' . $httpCode;
        if ($curlError) {
            $errorSummary .= ' cURL: ' . $curlError;
        }
        if ($res) {
            $decoded = json_decode($res, true);
            if (is_array($decoded)) {
                $name = $decoded['name'] ?? '';
                $message = $decoded['message'] ?? '';
                $details = $decoded['details'][0]['description'] ?? '';
                $errorSummary .= ' PayPal: ' . trim(implode(' | ', array_filter([$name, $message, $details])));
            } else {
                $errorSummary .= ' Response: ' . substr(trim($res), 0, 300);
            }
        }

        error_log('[PayPal Cancel Subscription] ' . $subId . ' => ' . $errorSummary);

        return false;
    }
}
