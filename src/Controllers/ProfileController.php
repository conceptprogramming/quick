<?php
namespace Controllers;

use Database;
use Core\Session;

class ProfileController
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function index(): void
    {
        $userId = $this->session->userId();
        $db     = Database::getInstance();

        // ── User ──────────────────────────────────────────────
        $st = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $st->execute(['id' => $userId]);
        $user = $st->fetch();

        // ── Plan details ──────────────────────────────────────
        $userPlanKey = $user['plan'] ?? 'free';
        $plan        = PLANS[$userPlanKey]      ?? PLANS['free'];
        $limits      = PDF_LIMITS[$userPlanKey] ?? PDF_LIMITS['free'];

        // ── Monthly usage + bonus ─────────────────────────────
        $month = date('Y-m');
        $st = $db->prepare("
            SELECT pdfs_uploaded, chat_messages, summaries, qa_questions, quizzes,
                   COALESCE(bonus_pdfs, 0)       AS bonus_pdfs,
                   COALESCE(bonus_chats, 0)      AS bonus_chats,
                   COALESCE(bonus_summaries, 0)  AS bonus_summaries,
                   COALESCE(bonus_quizzes, 0)    AS bonus_quizzes
            FROM usage_counters
            WHERE user_id = :uid AND month = :month LIMIT 1
        ");
        $st->execute(['uid' => $userId, 'month' => $month]);
        $usage = $st->fetch() ?: [
            'pdfs_uploaded'   => 0, 'chat_messages'   => 0,
            'summaries'       => 0, 'qa_questions'    => 0,
            'quizzes'         => 0, 'bonus_pdfs'      => 0,
            'bonus_chats'     => 0, 'bonus_summaries' => 0,
            'bonus_quizzes'   => 0,
        ];

        // ── Effective limits = plan limit + carried bonus ─────
        $effectiveLimits = [
            'pdfs_per_month' => $plan['benefits']['pdfs_per_month'] + (int)$usage['bonus_pdfs'],
            'chat_messages'  => $plan['benefits']['chat_messages']  + (int)$usage['bonus_chats'],
            'summaries'      => $plan['benefits']['summaries']      + (int)$usage['bonus_summaries'],
            'quizzes'        => $plan['benefits']['quizzes']        + (int)$usage['bonus_quizzes'],
        ];

        // ── Credit ledger — last 20 entries ───────────────────
        $st = $db->prepare("
            SELECT * FROM credit_ledger
            WHERE user_id = :uid
            ORDER BY created_at DESC
            LIMIT 20
        ");
        $st->execute(['uid' => $userId]);
        $ledger = $st->fetchAll();

        // ── All-time stats ────────────────────────────────────
        $st = $db->prepare("
            SELECT
                SUM(CASE WHEN credit_change < 0 THEN ABS(credit_change) ELSE 0 END) AS total_spent,
                SUM(CASE WHEN credit_change > 0 THEN credit_change ELSE 0 END)       AS total_earned,
                COUNT(*)                                                               AS total_transactions
            FROM credit_ledger
            WHERE user_id = :uid
        ");
        $st->execute(['uid' => $userId]);
        $stats = $st->fetch();

        $st = $db->prepare("
            SELECT *
            FROM subscriptions
            WHERE user_id = :uid
            ORDER BY created_at DESC, id DESC
            LIMIT 1
        ");
        $st->execute(['uid' => $userId]);
        $subscription = $st->fetch() ?: null;

        require __DIR__ . '/../../views/profile/index.php';
    }
}
