<?php
namespace Controllers;

use Core\RateLimiter;
use Core\Request;
use Database;

class AdminController
{
    private function currentPath(): string
    {
        $basePath = rtrim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/');
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH) ?? '/admin';
        $normalized = '/' . ltrim(str_replace($basePath, '', $requestPath), '/');

        return rtrim($normalized, '/') ?: '/';
    }

    private function routeFilter(): string
    {
        return match ($this->currentPath()) {
            '/admin/users/free' => 'free',
            '/admin/users/basic' => 'basic',
            '/admin/users/pro' => 'pro',
            '/admin/users/professional' => 'professional',
            default => trim($_GET['plan'] ?? ''),
        };
    }

    private function listingPath(string $filter = ''): string
    {
        return match ($filter) {
            'free' => APP_URL . '/admin/users/free',
            'basic' => APP_URL . '/admin/users/basic',
            'pro' => APP_URL . '/admin/users/pro',
            'professional' => APP_URL . '/admin/users/professional',
            default => APP_URL . '/admin/users',
        };
    }

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
        $currentPath = $this->currentPath();
        $isDashboard = $currentPath === '/admin';
        $sectionTitle = match ($currentPath) {
            '/admin/users/free' => 'Free Users',
            '/admin/users/basic' => 'Basic Users',
            '/admin/users/pro' => 'Pro Users',
            '/admin/users/professional' => 'Professional Users',
            '/admin/users' => 'All Users',
            default => 'Overview',
        };

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
        $search = trim($_GET['q'] ?? '');
        $filter = $this->routeFilter();

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
        $totalPages = max(1, (int) ceil($totalUsers / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;
        $listingPath = $this->listingPath($filter);
        $isFilteredRoute = in_array($this->currentPath(), [
            '/admin/users/free',
            '/admin/users/basic',
            '/admin/users/pro',
            '/admin/users/professional',
        ], true);

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
        $ip = Request::ip();
        if (RateLimiter::isBlocked($ip, 'admin_login_block')) {
            $_SESSION['admin_error'] = 'This IP is blocked for 24 hours after 3 incorrect admin login attempts.';
            header('Location: ' . APP_URL . '/admin');
            exit;
        }

        $password = $_POST['password'] ?? '';
        if (hash_equals(ADMIN_PASSWORD, $password)) {
            RateLimiter::clear($ip, 'admin_login');
            RateLimiter::clear($ip, 'admin_login_block');
            $_SESSION['admin_authed'] = true;
            header('Location: ' . APP_URL . '/admin');
            exit;
        }

        RateLimiter::check($ip, 'admin_login');
        $remaining = RateLimiter::remaining($ip, 'admin_login');

        if ($remaining === 0) {
            RateLimiter::block($ip, 'admin_login_block');
            $_SESSION['admin_error'] = 'Too many incorrect admin login attempts. This IP is blocked for 24 hours.';
        } else {
            $_SESSION['admin_error'] = "Invalid password. {$remaining} attempts remaining before this IP is blocked for 24 hours.";
        }
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
            VALUES (:uid, :change, :balance, 'topup', :note)
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
