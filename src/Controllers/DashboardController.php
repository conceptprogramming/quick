<?php
namespace Controllers;

use Database;
use Core\Session;
use Services\CreditService;

class DashboardController
{
    private Session $session;
    private CreditService $creditService;

    public function __construct()
    {
        $this->session = new Session();
        $this->creditService = new CreditService();
    }

    public function index(): void
    {
        $userId = $this->session->userId();
        $db     = Database::getInstance();

        // ── Get user data ─────────────────────────────────────
        $st = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $st->execute(['id' => $userId]);
        $user = $st->fetch();

        // ── Get current month usage + bonus ───────────────────
        $month = date('Y-m');
        $st = $db->prepare("
            SELECT pdfs_uploaded, chat_messages, summaries, qa_questions, quizzes,
                   COALESCE(bonus_pdfs, 0)       AS bonus_pdfs,
                   COALESCE(bonus_chats, 0)      AS bonus_chats,
                   COALESCE(bonus_summaries, 0)  AS bonus_summaries,
                   COALESCE(bonus_quizzes, 0)    AS bonus_quizzes
            FROM usage_counters
            WHERE user_id = :uid AND month = :month
            LIMIT 1
        ");
        $st->execute(['uid' => $userId, 'month' => $month]);
        $usage = $st->fetch() ?: [
            'pdfs_uploaded'   => 0,
            'chat_messages'   => 0,
            'summaries'       => 0,
            'qa_questions'    => 0,
            'quizzes'         => 0,
            'bonus_pdfs'      => 0,
            'bonus_chats'     => 0,
            'bonus_summaries' => 0,
            'bonus_quizzes'   => 0,
        ];

        // ── Get plan details ──────────────────────────────────
        $userPlan    = $user['plan'] ?? 'free';
        $plan        = PLANS[$userPlan]     ?? PLANS['free'];
        $limits      = PDF_LIMITS[$userPlan] ?? PDF_LIMITS['free'];
        $planBenefits = $plan['benefits'];

        // ── Effective limits = plan limit + carried bonus ─────
        $effectiveLimits = [
    'pdfs_per_month' => $planBenefits['pdfs_per_month'] + (int)$usage['bonus_pdfs'],
    'chat_messages'  => $planBenefits['chat_messages']  + (int)$usage['bonus_chats'],
    'summaries'      => $planBenefits['summaries']      + (int)$usage['bonus_summaries'],
    'qa_questions'   => $planBenefits['qa_questions'],
    'quizzes'        => $planBenefits['quizzes']        + (int)$usage['bonus_quizzes'],
];

        $pdfReady = !empty($_SESSION['pdf_processed']);
        $walletAccessMessage = $this->creditService->getWalletAccessMessage($userId);


        require __DIR__ . '/../../views/dashboard/index.php';
    }
}
