<?php
$seo = ['title' => 'My Profile — QuickChatPDF', 'canonical' => '/profile'];
ob_start();
$appUrl = APP_URL;
$userPlanKey = $user['plan'] ?? 'free';
$userCredits = (int) ($user['credits'] ?? 0);
$creditBalance = $userCredits;
$joinedDate = date('M d, Y', strtotime($user['created_at']));
$monthLabel = date('F Y');
$hasSubscriptionRecord = !empty($subscription);
$subscriptionStatus = $subscription['status'] ?? null;
$subscriptionCancelled = ($subscriptionStatus === 'cancelled') || !empty($subscription['cancelled_at']);
$subscriptionRenewsAt = $subscription['renews_at'] ?? null;
$canCancelSubscription = $hasSubscriptionRecord
    && $userPlanKey !== 'free'
    && !empty($user['paypal_subscription_id'])
    && !$subscriptionCancelled;
$subscriptionLabel = !$hasSubscriptionRecord
    ? 'Subscription record missing'
    : ($subscriptionCancelled
    ? 'Subscription cancelled'
    : ($userPlanKey !== 'free' ? 'Subscription active' : 'No paid subscription'));
?>

<!-- Navbar -->
<nav class="navbar navbar-light qcp-navbar sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $appUrl ?>/dashboard">
            <div class="qcp-logo-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
            <span class="text-dark">Quick<strong>ChatPDF</strong></span>
        </a>
        <div class="d-flex align-items-center gap-3">
            <span class="qcp-plan-badge">
                <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
                <span class="js-credit-balance"><?= number_format($userCredits) ?></span> credits
            </span>
            <a href="<?= $appUrl ?>/dashboard" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-grid me-1"></i>Dashboard
            </a>
            <a href="<?= $appUrl ?>/logout" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
        </div>
    </div>
</nav>

<div class="container py-5" style="max-width:960px">

    <!-- Profile Header -->
    <div class="qcp-profile-header mb-5">
        <div class="d-flex align-items-center gap-4 flex-wrap">
            <div class="qcp-avatar">
                <?= strtoupper(substr($user['email'], 0, 1)) ?>
            </div>
            <div>
                <h2 class="fw-800 mb-1">
                    <?= htmlspecialchars($user['email']) ?>
                </h2>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span class="qcp-plan-badge">
                        <i class="bi bi-patch-check-fill me-1"></i>
                        <?= ucfirst($userPlanKey) ?> Plan
                    </span>
                    <span class="text-muted small">
                        <i class="bi bi-calendar3 me-1"></i>
                        Member since
                        <?= $joinedDate ?>
                    </span>
                    <span class="text-muted small">
                        <i class="bi bi-circle-fill me-1 <?= $user['status'] === 'active' ? 'text-success' : 'text-danger' ?>"
                            style="font-size:.5rem"></i>
                        <?= ucfirst($user['status']) ?>
                    </span>
                </div>
            </div>
            <div class="ms-auto d-flex gap-2 flex-wrap">
                <a href="<?= $appUrl ?>/plans" class="btn btn-primary btn-sm px-4">
                    <i class="bi bi-rocket-takeoff me-1"></i>Upgrade Plan
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-5">
        <?php
        $allStats = [
            ['bi-lightning-charge-fill', 'text-warning', 'Credits Balance', number_format($userCredits), 'Available to use'],
            ['bi-bar-chart-fill', 'text-primary', 'Credits Spent', number_format($stats['total_spent'] ?? 0), 'All time'],
            ['bi-plus-circle-fill', 'text-success', 'Credits Earned', number_format($stats['total_earned'] ?? 0), 'All time'],
            ['bi-receipt', 'text-purple', 'Total Transactions', number_format($stats['total_transactions'] ?? 0), 'All time'],
        ];
        foreach ($allStats as [$icon, $color, $label, $value, $sub]): ?>
            <div class="col-6 col-lg-3">
                <div class="qcp-stat-card h-100">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi <?= $icon ?> <?= $color ?>"></i>
                        <span class="small text-muted">
                            <?= $label ?>
                        </span>
                    </div>
                    <div class="qcp-stat-value">
                        <?= $value ?>
                    </div>
                    <div class="text-muted" style="font-size:.75rem">
                        <?= $sub ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">

        <!-- Left Column -->
        <div class="col-lg-5">

            <!-- Plan Card -->
            <div class="qcp-profile-card mb-4">
                <div class="qcp-profile-card-header">
                    <i class="bi bi-patch-check-fill me-2" style="color:var(--qcp-primary)"></i>
                    Current Plan
                </div>
                <div class="p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <div class="fw-800 fs-5">
                                <?= $plan['name'] ?>
                            </div>
                            <div class="text-muted small">
                                <?= $plan['price'] > 0
                                    ? '$' . number_format($plan['price'], 2) . '/month'
                                    : 'Free forever' ?>
                            </div>
                            <?php if ($userPlanKey !== 'free' && $hasSubscriptionRecord): ?>
                                <div class="small mt-2 <?= $subscriptionCancelled ? 'text-warning' : 'text-success' ?>">
                                    <?= $subscriptionCancelled
                                        ? 'Subscription cancelled. Current plan stays active until ' . ($subscriptionRenewsAt ? date('M j, Y', strtotime($subscriptionRenewsAt)) : 'period end')
                                        : 'Subscription active' ?>
                                </div>
                            <?php elseif ($userPlanKey !== 'free'): ?>
                                <div class="small mt-2 text-warning">
                                    Subscription status could not be verified from the database.
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="qcp-feature-icon qcp-icon-purple">
                            <i class="bi bi-stars"></i>
                        </div>
                    </div>

                    <ul class="list-unstyled mb-4" style="font-size:.85rem">
                        <li class="mb-2">
                            <i class="bi bi-check2 me-2" style="color:var(--qcp-primary)"></i>
                            <?= number_format($plan['monthly_credits']) ?> credits/month
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check2 me-2" style="color:var(--qcp-primary)"></i>
                            <?= $plan['benefits']['pdfs_per_month'] ?> PDFs per month
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check2 me-2" style="color:var(--qcp-primary)"></i>
                            <?= $plan['benefits']['chat_messages'] ?> chat messages
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check2 me-2" style="color:var(--qcp-primary)"></i>
                            <?= $plan['benefits']['summaries'] ?> summaries
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check2 me-2" style="color:var(--qcp-primary)"></i>
                            <?= $plan['benefits']['quizzes'] ?> quizzes
                        </li>
                        <li>
                            <i class="bi bi-check2 me-2" style="color:var(--qcp-primary)"></i>
                            Up to
                            <?= $limits['pages'] ?> pages ·
                            <?= $limits['size_mb'] ?>MB per PDF
                        </li>
                    </ul>

                    <?php if ($canCancelSubscription): ?>
                        <button class="btn btn-outline-danger w-100 btn-sm mb-2" id="cancelSubscriptionBtn">
                            <i class="bi bi-x-circle me-2"></i>Cancel Subscription
                        </button>
                    <?php endif; ?>

                    <?php if ($userPlanKey !== 'professional'): ?>
                        <a href="<?= $appUrl ?>/plans" class="btn btn-primary w-100 btn-sm">
                            <i class="bi bi-rocket-takeoff me-2"></i>Upgrade for More
                        </a>
                    <?php else: ?>
                        <div class="text-center text-muted small">
                            <i class="bi bi-trophy-fill text-warning me-1"></i>
                            You're on the highest plan!
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Account Info -->
            <div class="qcp-profile-card">
                <div class="qcp-profile-card-header">
                    <i class="bi bi-person-fill me-2" style="color:var(--qcp-primary)"></i>
                    Account Info
                </div>
                <div class="p-4">
                    <div class="qcp-info-row">
                        <span class="qcp-info-label">Email</span>
                        <span class="qcp-info-value">
                            <?= htmlspecialchars($user['email']) ?>
                        </span>
                    </div>
                    <div class="qcp-info-row">
                        <span class="qcp-info-label">Account ID</span>
                        <span class="qcp-info-value text-muted">#
                            <?= str_pad($user['id'], 6, '0', STR_PAD_LEFT) ?>
                        </span>
                    </div>
                    <div class="qcp-info-row">
                        <span class="qcp-info-label">Member Since</span>
                        <span class="qcp-info-value">
                            <?= $joinedDate ?>
                        </span>
                    </div>
                    <div class="qcp-info-row">
                        <span class="qcp-info-label">Account Status</span>
                        <span class="qcp-info-value">
                            <span class="badge <?= $user['status'] === 'active' ? 'bg-success' : 'bg-danger' ?> px-2">
                                <?= ucfirst($user['status']) ?>
                            </span>
                        </span>
                    </div>
                    <?php if ($userPlanKey !== 'free'): ?>
                        <div class="qcp-info-row">
                            <span class="qcp-info-label">Subscription Status</span>
                            <span class="qcp-info-value">
                                <span class="badge <?= (!$hasSubscriptionRecord || $subscriptionCancelled) ? 'bg-warning text-dark' : 'bg-success' ?> px-2">
                                    <?= htmlspecialchars($subscriptionLabel) ?>
                                </span>
                            </span>
                        </div>
                    <?php endif; ?>
                    <?php if ($user['paypal_subscription_id']): ?>
                        <div class="qcp-info-row">
                            <span class="qcp-info-label">Subscription</span>
                            <span class="qcp-info-value text-muted small" style="word-break:break-all">
                                <?= htmlspecialchars($user['paypal_subscription_id']) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="qcp-profile-card mt-4">
                <div class="qcp-profile-card-header">
                    <i class="bi bi-headset me-2" style="color:var(--qcp-primary)"></i>
                    Support
                </div>
                <div class="p-4">
                    <div class="qcp-info-row">
                        <span class="qcp-info-label">General Support</span>
                        <span class="qcp-info-value">
                            <a href="mailto:support@quickchatpdf.com" class="text-decoration-none">support@quickchatpdf.com</a>
                        </span>
                    </div>
                    <div class="qcp-info-row">
                        <span class="qcp-info-label">Privacy Contact</span>
                        <span class="qcp-info-value">
                            <a href="mailto:privacy@quickchatpdf.com" class="text-decoration-none">privacy@quickchatpdf.com</a>
                        </span>
                    </div>
                    <div class="small text-muted mt-3">
                        Share your account email, the affected page, and any screenshot or exact error message so we can help faster.
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column -->
        <div class="col-lg-7">

            <!-- Monthly Usage -->
            <div class="qcp-profile-card mb-4">
                <div class="qcp-profile-card-header">
                    <i class="bi bi-bar-chart-fill me-2" style="color:var(--qcp-primary)"></i>
                    Usage This Month
                    <span class="ms-auto small text-muted fw-400">
                        <?= $monthLabel ?>
                    </span>
                </div>
                <div class="p-4">
                    <?php
                    $usageItems = [
                        ['PDFs Uploaded', $usage['pdfs_uploaded'] ?? 0, $effectiveLimits['pdfs_per_month'], 'bi-file-earmark-pdf-fill', 'text-danger'],
                        ['Chat Messages', $usage['chat_messages'] ?? 0, $effectiveLimits['chat_messages'], 'bi-chat-dots-fill', 'text-primary'],
                        ['Summaries', $usage['summaries'] ?? 0, $effectiveLimits['summaries'], 'bi-file-text-fill', 'text-blue'],
                        ['Quizzes', $usage['quizzes'] ?? 0, $effectiveLimits['quizzes'], 'bi-ui-checks-grid', 'text-warning'],
                    ];
                    foreach ($usageItems as [$label, $used, $limit, $icon, $color]):
                        $pct = $limit > 0 ? min(100, round(($used / $limit) * 100)) : 0;
                        $barColor = $pct >= 90 ? '#ef4444' : ($pct >= 70 ? '#f97316' : 'var(--qcp-primary)');
                        ?>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-600">
                                    <i class="bi <?= $icon ?> <?= $color ?> me-2"></i>
                                    <?= $label ?>
                                </span>
                                <span class="small text-muted">
                                    <strong class="text-dark">
                                        <?= number_format($used) ?>
                                    </strong>
                                    /
                                    <?= number_format($limit) ?>
                                </span>
                            </div>
                            <div class="progress" style="height:7px;border-radius:100px;background:#f1f4f9">
                                <div class="progress-bar"
                                    style="width:<?= $pct ?>%;background:<?= $barColor ?>;border-radius:100px;transition:width .6s ease">
                                </div>
                            </div>
                            <div class="text-muted mt-1" style="font-size:.72rem">
                                <?= $limit - $used > 0
                                    ? number_format($limit - $used) . ' remaining'
                                    : '<span class="text-danger fw-600">Limit reached</span>' ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Credit Ledger -->
            <div class="qcp-profile-card">
                <div class="qcp-profile-card-header">
                    <i class="bi bi-clock-history me-2" style="color:var(--qcp-primary)"></i>
                    Credit History
                    <span class="ms-auto small text-muted fw-400">Last 20 transactions</span>
                </div>
                <div class="p-0">
                    <?php if (empty($ledger)): ?>
                        <div class="text-center text-muted py-5 small">
                            <i class="bi bi-inbox fs-3 d-block mb-2 opacity-50"></i>
                            No transactions yet
                        </div>
                    <?php else: ?>
                        <div class="qcp-ledger-list">
                            <?php
                            $featureIcons = [
                                'chat' => ['bi-chat-dots-fill', 'text-primary'],
                                'summary' => ['bi-file-text-fill', 'text-blue'],
                                'quiz' => ['bi-ui-checks-grid', 'text-warning'],
                                'qa' => ['bi-patch-question-fill', 'text-teal'],
                                'topup' => ['bi-plus-circle-fill', 'text-success'],
                                'subscription' => ['bi-patch-check-fill', 'text-success'],
                                'admin' => ['bi-shield-fill', 'text-purple'],
                                'refund' => ['bi-arrow-counterclockwise', 'text-success'],
                                'export' => ['bi-download', 'text-muted'],
                            ];
                            foreach ($ledger as $entry):
                                $featureKey = strtolower(explode('_', $entry['feature'])[0]);
                                [$icon, $color] = $featureIcons[$featureKey] ?? ['bi-circle-fill', 'text-muted'];
                                $isCredit = $entry['credit_change'] > 0;
                                $changeStr = ($isCredit ? '+' : '') . number_format($entry['credit_change']);
                                $dateStr = date('M d, Y · g:ia', strtotime($entry['created_at']));
                                $label = ucfirst(str_replace(['_', 'plan upgrade '], [' ', ''], $entry['feature']));
                                ?>
                                <div class="qcp-ledger-row">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="qcp-ledger-icon">
                                            <i class="bi <?= $icon ?> <?= $color ?>"></i>
                                        </div>
                                        <div>
                                            <div class="fw-600 small text-dark">
                                                <?= htmlspecialchars($label) ?>
                                            </div>
                                            <div class="text-muted" style="font-size:.75rem">
                                                <?= $dateStr ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-700 small <?= $isCredit ? 'text-success' : 'text-danger' ?>">
                                            <?= $changeStr ?> credits
                                        </div>
                                        <div class="text-muted" style="font-size:.72rem">
                                            Balance:
                                            <?= number_format($entry['credit_balance']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
ob_start();
?>
<style>
    /* ── Profile Header ── */
    .qcp-avatar {
        width: 72px;
        height: 72px;
        background: var(--qcp-gradient);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-heading);
        font-size: 1.8rem;
        font-weight: 800;
        color: #fff;
        flex-shrink: 0;
    }

    /* ── Profile Card ── */
    .qcp-profile-card {
        background: #fff;
        border: 1px solid var(--qcp-border);
        border-radius: var(--qcp-radius);
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
    }

    .qcp-profile-card-header {
        padding: 14px 20px;
        border-bottom: 1px solid var(--qcp-border);
        background: #f8f9fc;
        font-weight: 700;
        font-size: .88rem;
        color: #0f172a;
        display: flex;
        align-items: center;
    }

    /* ── Info Rows ── */
    .qcp-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f1f4f9;
        font-size: .88rem;
    }

    .qcp-info-row:last-child {
        border-bottom: none;
    }

    .qcp-info-label {
        color: #64748b;
        font-weight: 500;
    }

    .qcp-info-value {
        font-weight: 600;
        color: #0f172a;
        text-align: right;
        max-width: 60%;
    }

    /* ── Ledger ── */
    .qcp-ledger-list {
        max-height: 420px;
        overflow-y: auto;
    }

    .qcp-ledger-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 20px;
        border-bottom: 1px solid #f8f9fc;
        transition: background .15s;
    }

    .qcp-ledger-row:last-child {
        border-bottom: none;
    }

    .qcp-ledger-row:hover {
        background: #f8f9fc;
    }

    .qcp-ledger-icon {
        width: 36px;
        height: 36px;
        background: #f1f4f9;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
        flex-shrink: 0;
    }

    /* ── Colors ── */
    .text-blue {
        color: #3b82f6 !important;
    }

    .text-teal {
        color: #14b8a6 !important;
    }

    .text-purple {
        color: #6c47ff !important;
    }

    /* ── Profile Header ── */
    .qcp-profile-header {
        background: #fff;
        border: 1px solid var(--qcp-border);
        border-radius: var(--qcp-radius);
        padding: 28px 32px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
    }
</style>
<script>
    const PROFILE_APP_URL = '<?= $appUrl ?>';
    const cancelSubscriptionBtn = document.getElementById('cancelSubscriptionBtn');

    if (cancelSubscriptionBtn) {
        cancelSubscriptionBtn.addEventListener('click', function () {
            if (!window.confirm('Cancel your PayPal subscription renewal? Your current plan will stay active until the end of the paid period.')) {
                return;
            }

            fetch(PROFILE_APP_URL + '/subscription/cancel', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({}),
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    window.alert(data.message || (data.success ? 'Subscription updated.' : 'Could not cancel subscription.'));
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(function () {
                    window.alert('Server error. Contact support.');
                });
        });
    }
</script>
<?php
$extraScripts = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
