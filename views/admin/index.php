<?php
$appUrl = APP_URL;
$search = htmlspecialchars($search ?? ($_GET['q'] ?? ''));
$filter = htmlspecialchars($filter ?? ($_GET['plan'] ?? ''));
$listingPath = $listingPath ?? ($appUrl . '/admin/users');
$basePath = rtrim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/');
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH) ?? '/admin';
$currentPath = '/' . ltrim(str_replace($basePath, '', $requestPath), '/');
$currentPath = rtrim($currentPath, '/') ?: '/';
$isDashboard = $isDashboard ?? ($currentPath === '/admin');
$sectionTitle = $sectionTitle ?? 'Overview';

// Plan colors
$planColors = [
    'free' => 'secondary',
    'basic' => 'primary',
    'pro' => 'purple',
    'professional' => 'success',
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Panel — QuickChatPDF</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --qcp-primary: #6c47ff;
            --qcp-gradient: linear-gradient(135deg, #6c47ff 0%, #a855f7 100%);
            --qcp-border: #e2e8f0;
            --qcp-bg: #f8f9fc;
            --font-body: 'Inter', sans-serif;
            --font-heading: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background: var(--qcp-bg);
            font-family: var(--font-body);
            font-size: 14px;
            color: #0f172a;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: var(--font-heading);
        }

        /* Sidebar */
        .admin-sidebar {
            width: 220px;
            flex-shrink: 0;
            background: #fff;
            border-right: 1px solid var(--qcp-border);
            min-height: 100vh;
            padding: 24px 16px;
            position: sticky;
            top: 0;
        }

        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 10px;
            color: #475569;
            font-size: .85rem;
            font-weight: 500;
            text-decoration: none;
            transition: all .2s;
            margin-bottom: 2px;
        }

        .admin-nav-link:hover,
        .admin-nav-link.active {
            background: rgba(108, 71, 255, .08);
            color: var(--qcp-primary);
        }

        /* Main */
        .admin-main {
            flex: 1;
            padding: 32px;
            overflow-x: hidden;
        }

        /* Cards */
        .admin-card {
            background: #fff;
            border: 1px solid var(--qcp-border);
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .admin-stat-value {
            font-family: var(--font-heading);
            font-size: 1.6rem;
            font-weight: 800;
            color: #0f172a;
        }

        /* Table */
        .admin-table {
            font-size: .84rem;
        }

        .admin-table th {
            background: #f8f9fc;
            font-weight: 600;
            color: #64748b;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            border-bottom: 1px solid var(--qcp-border);
            padding: 10px 16px;
        }

        .admin-table td {
            padding: 12px 16px;
            vertical-align: middle;
        }

        .admin-table tbody tr:hover {
            background: #f8f9fc;
        }

        .admin-table tbody tr {
            border-bottom: 1px solid #f1f4f9;
            cursor: pointer;
        }

        /* Badge */
        .badge-purple {
            background: rgba(108, 71, 255, .1);
            color: #6c47ff;
        }

        /* Search bar */
        .admin-search {
            background: #f8f9fc;
            border: 1.5px solid var(--qcp-border);
            border-radius: 10px;
            padding: 8px 14px;
            font-size: .85rem;
            transition: all .2s;
        }

        .admin-search:focus {
            border-color: var(--qcp-primary);
            box-shadow: 0 0 0 3px rgba(108, 71, 255, .1);
            outline: none;
            background: #fff;
        }
    </style>
</head>

<body>
    <div class="d-flex">

        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="d-flex align-items-center gap-2 mb-4">
                <div
                    style="width:32px;height:32px;background:var(--qcp-gradient);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.9rem">
                    <i class="bi bi-shield-fill"></i>
                </div>
                <span class="fw-800" style="font-size:.95rem">Admin Panel</span>
            </div>

            <a href="<?= $appUrl ?>/admin" class="admin-nav-link <?= $currentPath === '/admin' ? 'active' : '' ?>">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
            <a href="<?= $appUrl ?>/admin/users" class="admin-nav-link <?= $currentPath === '/admin/users' ? 'active' : '' ?>">
                <i class="bi bi-people-fill"></i> All Users
            </a>
            <a href="<?= $appUrl ?>/admin/users/free"
                class="admin-nav-link <?= $currentPath === '/admin/users/free' ? 'active' : '' ?>">
                <i class="bi bi-people-fill"></i> Free Users
            </a>
            <a href="<?= $appUrl ?>/admin/users/basic"
                class="admin-nav-link <?= $currentPath === '/admin/users/basic' ? 'active' : '' ?>">
                <i class="bi bi-lightning-charge-fill"></i> Basic Users
            </a>
            <a href="<?= $appUrl ?>/admin/users/pro"
                class="admin-nav-link <?= $currentPath === '/admin/users/pro' ? 'active' : '' ?>">
                <i class="bi bi-star-fill"></i> Pro Users
            </a>
            <a href="<?= $appUrl ?>/admin/users/professional"
                class="admin-nav-link <?= $currentPath === '/admin/users/professional' ? 'active' : '' ?>">
                <i class="bi bi-building-fill"></i> Professional Users
            </a>

            <hr style="border-color:var(--qcp-border);margin:16px 0">

            <a href="<?= $appUrl ?>/dashboard" class="admin-nav-link">
                <i class="bi bi-box-arrow-left"></i> Back to App
            </a>
            <form action="<?= $appUrl ?>/admin/logout" method="POST" class="mt-1">
                <button type="submit" class="admin-nav-link w-100 border-0 text-danger"
                    style="background:none;text-align:left">
                    <i class="bi bi-power"></i> Logout
                </button>
            </form>
        </div>

        <!-- Main -->
        <div class="admin-main">

            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-800 mb-0">
                        <?= htmlspecialchars($sectionTitle) ?>
                    </h4>
                    <p class="text-muted small mb-0">
                        <?= date('l, F j, Y') ?>
                    </p>
                </div>
            </div>

            <?php if ($isDashboard): ?>
                <!-- Stat Cards -->
                <div class="row g-3 mb-4">
                    <?php
                    $statCards = [
                        ['bi-people-fill', 'text-primary', 'Total Users', number_format($stats['total_users'])],
                        ['bi-patch-check-fill', 'text-success', 'Paid Users', number_format($stats['paid_users'])],
                        ['bi-person-plus-fill', 'text-purple', 'New Today', number_format($stats['new_today'])],
                        ['bi-slash-circle-fill', 'text-danger', 'Suspended', number_format($stats['suspended'])],
                        ['bi-file-earmark-pdf-fill', 'text-danger', 'PDFs This Month', number_format($monthlyUsage['pdfs'])],
                        ['bi-chat-dots-fill', 'text-primary', 'Chats This Month', number_format($monthlyUsage['chats'])],
                        ['bi-file-text-fill', 'text-blue', 'Summaries This Month', number_format($monthlyUsage['summaries'])],
                        ['bi-ui-checks-grid', 'text-warning', 'Quizzes This Month', number_format($monthlyUsage['quizzes'])],
                    ];
                    foreach ($statCards as [$icon, $color, $label, $value]): ?>
                        <div class="col-6 col-xl-3">
                            <div class="admin-card">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi <?= $icon ?> <?= $color ?>"></i>
                                    <span class="text-muted small">
                                        <?= $label ?>
                                    </span>
                                </div>
                                <div class="admin-stat-value">
                                    <?= $value ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Charts Row -->
                <div class="row g-4 mb-4">

                    <!-- Daily Signups Chart -->
                    <div class="col-lg-8">
                        <div class="admin-card h-100">
                            <h6 class="fw-700 mb-3">Daily Signups — Last 14 Days</h6>
                            <canvas id="signupChart" height="100"></canvas>
                        </div>
                    </div>

                    <!-- Plan Breakdown -->
                    <div class="col-lg-4">
                        <div class="admin-card h-100">
                            <h6 class="fw-700 mb-3">Plan Breakdown</h6>
                            <canvas id="planChart" height="200"></canvas>
                            <div class="mt-3">
                                <?php foreach (PLANS as $key => $plan): ?>
                                    <div class="d-flex justify-content-between align-items-center py-1"
                                        style="font-size:.82rem;border-bottom:1px solid #f1f4f9">
                                        <span>
                                            <?= $plan['name'] ?>
                                        </span>
                                        <strong>
                                            <?= number_format($planBreakdown[$key] ?? 0) ?>
                                        </strong>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- User Table -->
                <div class="admin-card">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
                        <div>
                            <h6 class="fw-700 mb-1">
                                <?= htmlspecialchars($sectionTitle) ?>
                            </h6>
                            <span class="text-muted small">
                                20 records per page
                            </span>
                        </div>
                        <form class="d-flex align-items-center gap-2 flex-wrap" method="GET" action="<?= $listingPath ?>">
                            <input type="text" name="q" value="<?= $search ?>" class="admin-search"
                                placeholder="Search by email..." style="width:260px" />
                            <button type="submit" class="btn btn-sm"
                                style="background:var(--qcp-gradient);color:#fff;border-radius:8px;font-weight:600">
                                <i class="bi bi-search me-1"></i>Search
                            </button>
                            <?php if ($search !== ''): ?>
                                <a href="<?= $listingPath ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px">
                                    Clear
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table admin-table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Email</th>
                                    <th>Plan</th>
                                    <th>Credits</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No users found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $u): ?>
                                        <tr onclick="window.location='<?= $appUrl ?>/admin/user?id=<?= $u['id'] ?>'">
                                            <td class="text-muted">#
                                                <?= str_pad($u['id'], 4, '0', STR_PAD_LEFT) ?>
                                            </td>
                                            <td class="fw-500">
                                                <?= htmlspecialchars($u['email']) ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $planColors[$u['plan']] ?? 'secondary' ?> px-2 py-1">
                                                    <?= ucfirst($u['plan']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
                                                <?= number_format($u['credits']) ?>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge <?= $u['status'] === 'active' ? 'bg-success' : 'bg-danger' ?> px-2">
                                                    <?= ucfirst($u['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-muted">
                                                <?= date('M d, Y', strtotime($u['created_at'])) ?>
                                            </td>
                                            <td>
                                                <a href="<?= $appUrl ?>/admin/user?id=<?= $u['id'] ?>"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    style="border-radius:8px;font-size:.78rem" onclick="event.stopPropagation()">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3"
                            style="border-top:1px solid var(--qcp-border)">
                            <span class="text-muted small">
                                Showing
                                <?= number_format(($page - 1) * $perPage + 1) ?>–
                                <?= number_format(min($page * $perPage, $totalUsers)) ?>
                                of
                                <?= number_format($totalUsers) ?> users
                            </span>
                            <div class="d-flex gap-1">
                                <?php
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);
                                $queryBase = ['q' => $search];
                                if (!$isFilteredRoute && $filter !== '') {
                                    $queryBase['plan'] = $filter;
                                }
                                ?>
                                <?php if ($page > 1): ?>
                                    <?php $prevQuery = http_build_query($queryBase + ['page' => $page - 1]); ?>
                                    <a href="<?= $listingPath ?>?<?= $prevQuery ?>" class="btn btn-sm btn-outline-secondary"
                                        style="border-radius:8px;font-size:.8rem">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <?php $pageQuery = http_build_query($queryBase + ['page' => $i]); ?>
                                    <a href="<?= $listingPath ?>?<?= $pageQuery ?>"
                                        class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-outline-secondary' ?>"
                                        style="border-radius:8px;min-width:32px;font-size:.8rem">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                                <?php if ($page < $totalPages): ?>
                                    <?php $nextQuery = http_build_query($queryBase + ['page' => $page + 1]); ?>
                                    <a href="<?= $listingPath ?>?<?= $nextQuery ?>" class="btn btn-sm btn-outline-secondary"
                                        style="border-radius:8px;font-size:.8rem">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
// ── Signup Chart ──────────────────────────────────────────
<?php
// Build 14-day labels + data
$signupMap = [];
foreach ($dailySignups as $row)
    $signupMap[$row['day']] = (int) $row['cnt'];
$labels = [];
$data = [];
for ($i = 13; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('M d', strtotime($day));
    $data[] = $signupMap[$day] ?? 0;
}
?>
            <?php if ($isDashboard): ?>
            new Chart(document.getElementById('signupChart'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode($labels) ?>,
                    datasets: [{
                        label: 'Signups',
                        data: <?= json_encode($data) ?>,
                        backgroundColor: 'rgba(108,71,255,.15)',
                        borderColor: '#6c47ff',
                        borderWidth: 2,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f1f4f9' } },
                        x: { grid: { display: false } }
                    }
                }
            });

        // ── Plan Doughnut Chart ───────────────────────────────────
        new Chart(document.getElementById('planChart'), {
            type: 'doughnut',
            data: {
                labels: ['Free', 'Basic', 'Pro', 'Professional'],
                datasets: [{
                    data: [
                <?= (int) ($planBreakdown['free'] ?? 0) ?>,
                <?= (int) ($planBreakdown['basic'] ?? 0) ?>,
                <?= (int) ($planBreakdown['pro'] ?? 0) ?>,
                <?= (int) ($planBreakdown['professional'] ?? 0) ?>,
                    ],
                    backgroundColor: ['#e2e8f0', '#3b82f6', '#6c47ff', '#22c55e'],
                    borderWidth: 0,
                }]
            },
            options: {
                cutout: '70%',
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } } }
            }
        });
        <?php endif; ?>
    </script>
</body>

</html>
