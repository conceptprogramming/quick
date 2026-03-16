<?php
namespace Services;

use Database;

class CreditService
{

    private array $columnMap = [
        'pdfs_per_month' => 'pdfs_uploaded',
        'chat_messages' => 'chat_messages',
        'summaries' => 'summaries',
        'qa_questions' => 'qa_questions',
        'quizzes' => 'quizzes',
        'pdfs_uploaded' => 'pdfs_uploaded',
    ];

    private array $bonusMap = [
        'pdfs_per_month' => 'bonus_pdfs',
        'chat_messages' => 'bonus_chats',
        'summaries' => 'bonus_summaries',
        'quizzes' => 'bonus_quizzes',
    ];

    public function canUseWalletCredits(int $userId): bool
    {
        $db = Database::getInstance();

        $st = $db->prepare("SELECT plan FROM users WHERE id = :id LIMIT 1");
        $st->execute(['id' => $userId]);
        $plan = $st->fetchColumn() ?: 'free';

        if ($plan !== 'free') {
            return true;
        }

        $st = $db->prepare("
            SELECT renews_at, status
            FROM subscriptions
            WHERE user_id = :uid
            ORDER BY id DESC
            LIMIT 1
        ");
        $st->execute(['uid' => $userId]);
        $subscription = $st->fetch();

        if (!$subscription) {
            return true;
        }

        $renewsAt = $subscription['renews_at'] ?? null;
        $status = $subscription['status'] ?? '';

        if ($renewsAt && strtotime($renewsAt) > time() && in_array($status, ['active', 'cancelled'], true)) {
            return true;
        }

        $st = $db->prepare("
            SELECT COUNT(*)
            FROM payments
            WHERE user_id = :uid AND type = 'topup' AND status = 'completed'
        ");
        $st->execute(['uid' => $userId]);
        $hasTopupHistory = (int) $st->fetchColumn() > 0;

        if (!$hasTopupHistory) {
            return true;
        }

        $st = $db->prepare("SELECT credits FROM users WHERE id = :id LIMIT 1");
        $st->execute(['id' => $userId]);
        $balance = (int) $st->fetchColumn();

        return $balance <= 0;
    }

    public function getWalletAccessMessage(int $userId): ?string
    {
        return $this->canUseWalletCredits($userId)
            ? null
            : 'Your top-up credits are locked because your paid subscription has ended. Renew a paid plan to use wallet credits again.';
    }

    // ── Check credits ─────────────────────────────────────────
    public function hasCredits(int $userId, string $feature, int $qty = 1): bool
    {
        if (!$this->canUseWalletCredits($userId)) {
            return false;
        }

        $cost = (CREDIT_COSTS[$feature] ?? 1) * $qty;
        $db = Database::getInstance();
        $st = $db->prepare("SELECT credits FROM users WHERE id = :id LIMIT 1");
        $st->execute(['id' => $userId]);
        $user = $st->fetch();
        return $user && $user['credits'] >= $cost;
    }

    // ── Deduct credits ────────────────────────────────────────
    public function deduct(int $userId, string $feature, int $qty = 1): bool
    {
        $cost = (CREDIT_COSTS[$feature] ?? 1) * $qty;
        $db = Database::getInstance();

        $st = $db->prepare("
            UPDATE users SET credits = credits - :cost
            WHERE id = :id AND credits >= :cost2
        ");
        $st->bindValue(':cost', $cost, \PDO::PARAM_INT);
        $st->bindValue(':cost2', $cost, \PDO::PARAM_INT);
        $st->bindValue(':id', $userId, \PDO::PARAM_INT);
        $st->execute();

        if ($st->rowCount() === 0)
            return false;

        $st = $db->prepare("SELECT credits FROM users WHERE id = :id");
        $st->execute(['id' => $userId]);
        $balance = $st->fetchColumn();

        $db->prepare("
            INSERT INTO credit_ledger (user_id, credit_change, credit_balance, feature)
            VALUES (:uid, :change, :balance, :feature)
        ")->execute([
                    'uid' => $userId,
                    'change' => -$cost,
                    'balance' => $balance,
                    'feature' => $feature,
                ]);

        return true;
    }

    // ── Add credits ───────────────────────────────────────────
   public function add(int $userId, int $amount, string $feature, ?string $orderId = null): void
{
    $db = Database::getInstance();
    $db->prepare("UPDATE users SET credits = credits + :amt WHERE id = :id")
       ->execute(['amt' => $amount, 'id' => $userId]);

    $st = $db->prepare("SELECT credits FROM users WHERE id = :id");
    $st->execute(['id' => $userId]);
    $balance = $st->fetchColumn();

    $db->prepare("
        INSERT INTO credit_ledger (user_id, credit_change, credit_balance, feature, paypal_order_id)
        VALUES (:uid, :change, :balance, :feature, :oid)
    ")->execute([
        'uid'     => $userId,
        'change'  => $amount,
        'balance' => $balance,
        'feature' => $feature,
        'oid'     => $orderId,
    ]);
}


    // ── Check monthly plan limit ──────────────────────────────
    public function withinPlanLimit(int $userId, string $plan, string $feature): bool
    {
        $db = Database::getInstance();
        $month = date('Y-m');
        $column = $this->columnMap[$feature] ?? $feature;
        $bonusColumn = $this->bonusMap[$feature] ?? null;
        $baseLimit = PLANS[$plan]['benefits'][$feature] ?? 0;

        $select = "SELECT {$column}";
        if ($bonusColumn) {
            $select .= ", COALESCE({$bonusColumn}, 0) AS bonus_limit";
        }

        $st = $db->prepare("
            {$select}
            FROM usage_counters
            WHERE user_id = :uid AND month = :month
            LIMIT 1
        ");
        $st->execute(['uid' => $userId, 'month' => $month]);
        $row = $st->fetch();
        $used = $row ? ($row[$column] ?? 0) : 0;
        $bonus = $row ? (int) ($row['bonus_limit'] ?? 0) : 0;
        $effectiveLimit = $baseLimit + $bonus;

        return $used < $effectiveLimit;
    }

    // ── Increment usage ───────────────────────────────────────
    public function incrementUsage(int $userId, string $feature, int $qty = 1): void
    {
        $db = Database::getInstance();
        $month = date('Y-m');
        $column = $this->columnMap[$feature] ?? $feature;

        $st = $db->prepare("
            INSERT INTO usage_counters (user_id, month, {$column})
            VALUES (:uid, :month, :qty)
            ON DUPLICATE KEY UPDATE {$column} = {$column} + :qty2
        ");
        $st->bindValue(':uid', $userId, \PDO::PARAM_INT);
        $st->bindValue(':month', $month, \PDO::PARAM_STR);
        $st->bindValue(':qty', $qty, \PDO::PARAM_INT);
        $st->bindValue(':qty2', $qty, \PDO::PARAM_INT);
        $st->execute();
    }

    // ── Get monthly usage ─────────────────────────────────────
    public function getMonthlyUsage(int $userId): array
    {
        $db = Database::getInstance();
        $month = date('Y-m');

        $st = $db->prepare("
            SELECT * FROM usage_counters
            WHERE user_id = :uid AND month = :month
            LIMIT 1
        ");
        $st->execute(['uid' => $userId, 'month' => $month]);
        return $st->fetch() ?: [
            'pdfs_uploaded' => 0,
            'chat_messages' => 0,
            'summaries' => 0,
            'qa_questions' => 0,
            'quizzes' => 0,
        ];
    }
}
