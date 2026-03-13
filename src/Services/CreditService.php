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

    // ── Check credits ─────────────────────────────────────────
    public function hasCredits(int $userId, string $feature, int $qty = 1): bool
    {
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
        $limit = PLANS[$plan]['benefits'][$feature] ?? 0;

        $st = $db->prepare("
            SELECT {$column} FROM usage_counters
            WHERE user_id = :uid AND month = :month
            LIMIT 1
        ");
        $st->execute(['uid' => $userId, 'month' => $month]);
        $row = $st->fetch();
        $used = $row ? ($row[$column] ?? 0) : 0;

        return $used < $limit;
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
