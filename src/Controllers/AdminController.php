<?php
namespace Controllers;

use Database;

class AdminController
{

    private function isAuthed(): bool
    {
        return !empty($_SESSION['admin_authed']);
    }

    private function requireAuth(): void
    {
        if (!$this->isAuthed()) {
            header('Location: ' . APP_URL . '/admin?auth=1');
            exit;
        }
    }

    private function flash(string $msg, string $type = 'success'): void
    {
        $_SESSION['flash'] = ['message' => $msg, 'type' => $type];
    }

    // ── GET /admin ────────────────────────────────────────
    public function index(): void
    {
        if (!$this->isAuthed()) {
            require __DIR__ . '/../../views/admin/login.php';
            return;
        }

        $db = Database::getInstance();
        $month = date('Y-m');

        // ── Stats ──────────────────────────────────────────────
        $stats = [];

        $st = $db->query("SELECT COUNT(*) FROM users");
        $stats['total_users'] = (int) $st->fetchColumn();

        $st = $db->query("SELECT COUNT(*) FROM users WHERE plan != 'free'");
        $stats['paid_users'] = (int) $st->fetchColumn();

        $st = $db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()");
        $stats['new_today'] = (int) $st->fetchColumn();

        $st = $db->query("SELECT COUNT(*) FROM users WHERE status = 'suspended'");
        $stats['suspended'] = (int) $st->fetchColumn();

        $st = $db->query("
        SELECT COALESCE(SUM(credit_change),0) FROM credit_ledger
        WHERE credit_change > 0
          AND feature IN ('topup','subscription')
    ");
        $stats['total_credits_sold'] = (int) $st->fetchColumn();

        // ── Monthly usage totals ───────────────────────────────
        $st = $db->prepare("
        SELECT
            COALESCE(SUM(pdfs_uploaded),0) AS pdfs,
            COALESCE(SUM(chat_messages),0) AS chats,
            COALESCE(SUM(summaries),0)     AS summaries,
            COALESCE(SUM(quizzes),0)       AS quizzes
        FROM usage_counters WHERE month = :month
    ");
        $st->execute(['month' => $month]);
        $monthlyUsage = $st->fetch();

        // ── Plan breakdown ─────────────────────────────────────
        $st = $db->query("SELECT plan, COUNT(*) as cnt FROM users GROUP BY plan");
        $planBreakdown = $st->fetchAll(\PDO::FETCH_KEY_PAIR);

        // ── Daily signups last 14 days ─────────────────────────
        $st = $db->query("
        SELECT DATE(created_at) as day, COUNT(*) as cnt
        FROM users
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
        GROUP BY DATE(created_at)
        ORDER BY day ASC
    ");
        $dailySignups = $st->fetchAll();

        // ── User list — paginated ──────────────────────────────
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $search = trim($_GET['q'] ?? '');
        $filter = trim($_GET['plan'] ?? '');

        // Build WHERE cleanly — no mixing of named params
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = 'email LIKE :q';
            $params[':q'] = '%' . $search . '%';
        }
        if ($filter !== '') {
            $conditions[] = 'plan = :plan';
            $params[':plan'] = $filter;
        }

        $whereSQL = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // Count total
        $st = $db->prepare("SELECT COUNT(*) FROM users $whereSQL");
        $st->execute($params);
        $totalUsers = (int) $st->fetchColumn();
        $totalPages = (int) ceil($totalUsers / $perPage);

        // Fetch page
        $st = $db->prepare("
        SELECT * FROM users $whereSQL
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ");

        // Bind search/filter params
        foreach ($params as $key => $val) {
            $st->bindValue($key, $val);
        }

        // Bind pagination as integers
        $st->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $st->execute();
        $users = $st->fetchAll();

        require __DIR__ . '/../../views/admin/index.php';
    }


    // ── GET /admin/user?id=X ──────────────────────────────
    public function user(): void
    {
        $this->requireAuth();
        $db = Database::getInstance();
        $userId = (int) ($_GET['id'] ?? 0);

        $st = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $st->execute(['id' => $userId]);
        $user = $st->fetch();
        if (!$user) {
            header('Location: ' . APP_URL . '/admin');
            exit;
        }

        $plan = PLANS[$user['plan']] ?? PLANS['free'];
        $limits = PDF_LIMITS[$user['plan']] ?? PDF_LIMITS['free'];

        // All months usage
        $st = $db->prepare("
            SELECT * FROM usage_counters
            WHERE user_id = :uid ORDER BY month DESC LIMIT 6
        ");
        $st->execute(['uid' => $userId]);
        $usageHistory = $st->fetchAll();

        // Ledger
        $st = $db->prepare("
            SELECT * FROM credit_ledger
            WHERE user_id = :uid ORDER BY created_at DESC LIMIT 30
        ");
        $st->execute(['uid' => $userId]);
        $ledger = $st->fetchAll();

        $csrfToken = \Middleware\CSRFMiddleware::generate();
        require __DIR__ . '/../../views/admin/user.php';
    }

    // ── POST /admin/login ─────────────────────────────────
    public function login(): void
    {
        $password = $_POST['password'] ?? '';
        if (hash_equals(ADMIN_PASSWORD, $password)) {
            $_SESSION['admin_authed'] = true;
            header('Location: ' . APP_URL . '/admin');
            exit;
        }
        $_SESSION['admin_error'] = 'Invalid password.';
        header('Location: ' . APP_URL . '/admin');
        exit;
    }

    // ── POST /admin/logout ────────────────────────────────
    public function adminLogout(): void
    {
        unset($_SESSION['admin_authed']);
        header('Location: ' . APP_URL . '/admin');
        exit;
    }

    // ── POST /admin/credits ───────────────────────────────
    public function adjustCredits(): void
    {
        $this->requireAuth();
        \Middleware\CSRFMiddleware::verify();

        $userId = (int) ($_POST['user_id'] ?? 0);
        $amount = (int) ($_POST['amount'] ?? 0);
        $note = trim($_POST['note'] ?? '');

        if (!$userId || $amount === 0) {
            $this->flash('Invalid input.', 'danger');
            header('Location: ' . APP_URL . '/admin/user?id=' . $userId);
            exit;
        }

        $db = Database::getInstance();

        // Clamp deduction to not go below 0
        if ($amount < 0) {
            $st = $db->prepare("SELECT credits FROM users WHERE id = :id");
            $st->execute(['id' => $userId]);
            $current = (int) $st->fetchColumn();
            $amount = max($amount, -$current);
        }

        $db->prepare("
            UPDATE users SET credits = GREATEST(0, credits + :amt) WHERE id = :id
        ")->execute(['amt' => $amount, 'id' => $userId]);

        $st = $db->prepare("SELECT credits FROM users WHERE id = :id");
        $st->execute(['id' => $userId]);
        $balance = (int) $st->fetchColumn();

        $db->prepare("
            INSERT INTO credit_ledger (user_id, credit_change, credit_balance, feature, note)
            VALUES (:uid, :change, :balance, 'admin', :note)
        ")->execute([
                    'uid' => $userId,
                    'change' => $amount,
                    'balance' => $balance,
                    'note' => $note ?: ($amount > 0 ? 'Admin credit grant' : 'Admin credit deduction'),
                ]);

        $this->flash(
            ($amount > 0 ? '+' : '') . number_format($amount) . ' credits adjusted for user #' . $userId,
            'success'
        );
        header('Location: ' . APP_URL . '/admin/user?id=' . $userId);
        exit;
    }

    // ── POST /admin/status ────────────────────────────────
    public function toggleStatus(): void
    {
        $this->requireAuth();
        \Middleware\CSRFMiddleware::verify();

        $userId = (int) ($_POST['user_id'] ?? 0);
        $db = Database::getInstance();

        $st = $db->prepare("SELECT status FROM users WHERE id = :id");
        $st->execute(['id' => $userId]);
        $current = $st->fetchColumn();

        $new = $current === 'active' ? 'suspended' : 'active';
        $db->prepare("UPDATE users SET status = :s WHERE id = :id")
            ->execute(['s' => $new, 'id' => $userId]);

        $this->flash('User ' . $new . ' successfully.', $new === 'active' ? 'success' : 'warning');
        header('Location: ' . APP_URL . '/admin/user?id=' . $userId);
        exit;
    }
}
