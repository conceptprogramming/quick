<?php
namespace Controllers;

use Database;
use Core\Session;

class PlansController
{

    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function index(): void
    {
        $userId = $this->session->userId();
        $db = Database::getInstance();

        $st = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $st->execute(['id' => $userId]);
        $user = $st->fetch();

        $currentPlan = PLANS[$user['plan']] ?? PLANS['free'];
        $month = date('Y-m');

        $st = $db->prepare("
            SELECT * FROM usage_counters
            WHERE user_id = :uid AND month = :month LIMIT 1
        ");
        $st->execute(['uid' => $userId, 'month' => $month]);
        $usage = $st->fetch() ?: [
            'pdfs_uploaded' => 0,
            'chat_messages' => 0,
            'summaries' => 0,
            'quizzes' => 0,
        ];

        // Last 5 credit ledger entries
        $st = $db->prepare("
            SELECT * FROM credit_ledger
            WHERE user_id = :uid
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $st->execute(['uid' => $userId]);
        $ledger = $st->fetchAll();

        $subscription = null;
        $st = $db->prepare("
            SELECT *
            FROM subscriptions
            WHERE user_id = :uid
              AND (
                    paypal_sub_id = :paypal_sub_id
                    OR :paypal_sub_id = ''
                  )
            ORDER BY updated_at DESC, id DESC
            LIMIT 1
        ");
        $st->execute([
            'uid' => $userId,
            'paypal_sub_id' => (string) ($user['paypal_subscription_id'] ?? ''),
        ]);
        $subscription = $st->fetch() ?: null;

        require __DIR__ . '/../../views/plans/index.php';
    }
}
