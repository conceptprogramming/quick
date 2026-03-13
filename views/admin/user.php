<?php
$appUrl = APP_URL;
$planColors = ['free' => 'secondary', 'basic' => 'primary', 'pro' => 'purple', 'professional' => 'success'];
$featureIcons = [
    'chat' => ['bi-chat-dots-fill', 'text-primary'],
    'summary' => ['bi-file-text-fill', 'text-info'],
    'quiz' => ['bi-ui-checks-grid', 'text-warning'],
    'qa' => ['bi-patch-question-fill', 'text-teal'],
    'topup' => ['bi-plus-circle-fill', 'text-success'],
    'subscription' => ['bi-patch-check-fill', 'text-success'],
    'admin' => ['bi-shield-fill', 'text-purple'],
    'refund' => ['bi-arrow-counterclockwise', 'text-success'],
    'export' => ['bi-download', 'text-muted'],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>User #
        <?= $user['id'] ?> — Admin
    </title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --qcp-primary: #6c47ff;
            --qcp-gradient: linear-gradient(135deg, #6c47ff 0%, #a855f7 100%);
            --qcp-border: #e2e8f0;
        }

        body {
            background: #f8f9fc;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: #0f172a;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .admin-card {
            background: #fff;
            border: 1px solid var(--qcp-border);
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .admin-input {
            background: #f8f9fc !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 10px !important;
            padding: 10px 14px !important;
            font-size: .88rem;
            transition: all .2s;
        }

        .admin-input:focus {
            border-color: var(--qcp-primary) !important;
            box-shadow: 0 0 0 3px rgba(108, 71, 255, .1) !important;
            background: #fff !important;
            outline: none !important;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f1f4f9;
            font-size: .86rem;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .ledger-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f8f9fc;
            font-size: .84rem;
        }

        .ledger-row:last-child {
            border-bottom: none;
        }

        .badge-purple {
            background: rgba(108, 71, 255, .1);
            color: #6c47ff;
        }

        .text-purple {
            color: #6c47ff !important;
        }

        .text-teal {
            color: #14b8a6 !important;
        }
    </style>
</head>

<body>
    <div class="container-lg py-4">

        <!-- Back -->
        <div class="mb-4">
            <a href="<?= $appUrl ?>/admin" class="btn btn-sm btn-outline-secondary" style="border-radius:8px">
                <i class="bi bi-arrow-left me-1"></i>Back to Users
            </a>
        </div>

        <!-- User Header -->
        <div class="admin-card mb-4">
            <div class="d-flex align-items-center gap-4 flex-wrap">
                <div style="width:60px;height:60px;background:var(--qcp-gradient);border-radius:16px;
                        display:flex;align-items:center;justify-content:center;
                        font-size:1.6rem;font-weight:800;color:#fff;font-family:'Plus Jakarta Sans',sans-serif">
                    <?= strtoupper(substr($user['email'], 0, 1)) ?>
                </div>
                <div>
                    <h5 class="fw-800 mb-1">
                        <?= htmlspecialchars($user['email']) ?>
                    </h5>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge badge-<?= $planColors[$user['plan']] ?? 'secondary' ?> px-2 py-1">
                            <?= ucfirst($user['plan']) ?> Plan
                        </span>
                        <span class="badge <?= $user['status'] === 'active' ? 'bg-success' : 'bg-danger' ?> px-2">
                            <?= ucfirst($user['status']) ?>
                        </span>
                        <span class="text-muted small">
                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
                            <?= number_format($user['credits']) ?> credits
                        </span>
                        <span class="text-muted small">
                            Joined
                            <?= date('M d, Y', strtotime($user['created_at'])) ?>
                        </span>
                    </div>
                </div>

                <!-- Toggle Status -->
                <div class="ms-auto">
                    <form action="<?= $appUrl ?>/admin/status" method="POST" onsubmit="return confirm('Are you sure?')">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <button type="submit"
                            class="btn btn-sm <?= $user['status'] === 'active' ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                            style="border-radius:8px">
                            <i
                                class="bi <?= $user['status'] === 'active' ? 'bi-slash-circle' : 'bi-check-circle' ?> me-1"></i>
                            <?= $user['status'] === 'active' ? 'Suspend User' : 'Activate User' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="row g-4">

            <!-- Left -->
            <div class="col-lg-5">

                <!-- Account Info -->
                <div class="admin-card mb-4">
                    <h6 class="fw-700 mb-3">Account Details</h6>
                    <div class="info-row"><span class="text-muted">User ID</span><strong>#
                            <?= str_pad($user['id'], 6, '0', STR_PAD_LEFT) ?>
                        </strong></div>
                    <div class="info-row"><span class="text-muted">Email</span><span>
                            <?= htmlspecialchars($user['email']) ?>
                        </span></div>
                    <div class="info-row"><span class="text-muted">Plan</span><span>
                            <?= ucfirst($user['plan']) ?>
                        </span></div>
                    <div class="info-row"><span class="text-muted">Credits</span><strong>
                            <?= number_format($user['credits']) ?>
                        </strong></div>
                    <div class="info-row"><span class="text-muted">Status</span><span>
                            <?= ucfirst($user['status']) ?>
                        </span></div>
                    <div class="info-row"><span class="text-muted">Joined</span><span>
                            <?= date('M d, Y · g:ia', strtotime($user['created_at'])) ?>
                        </span></div>
                    <?php if ($user['paypal_subscription_id']): ?>
                        <div class="info-row">
                            <span class="text-muted">PayPal Sub</span>
                            <span class="small" style="word-break:break-all;max-width:60%;text-align:right">
                                <?= htmlspecialchars($user['paypal_subscription_id']) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Adjust Credits -->
                <div class="admin-card mb-4">
                    <h6 class="fw-700 mb-3">Adjust Credits</h6>
                    <form action="<?= $appUrl ?>/admin/credits" method="POST">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-500 text-muted">Amount (use negative to deduct)</label>
                            <input type="number" name="amount" class="form-control admin-input"
                                placeholder="e.g. 100 or -50" required />
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-500 text-muted">Note (optional)</label>
                            <input type="text" name="note" class="form-control admin-input"
                                placeholder="Reason for adjustment" />
                        </div>
                        <button type="submit" class="btn w-100 fw-600"
                            style="background:var(--qcp-gradient);color:#fff;border-radius:10px;height:42px">
                            <i class="bi bi-lightning-charge-fill me-2"></i>Apply Credit Adjustment
                        </button>
                    </form>
                </div>

                <!-- Plan Limits -->
                <div class="admin-card">
                    <h6 class="fw-700 mb-3">Plan Limits</h6>
                    <div class="info-row"><span class="text-muted">Credits/month</span><strong>
                            <?= number_format($plan['monthly_credits']) ?>
                        </strong></div>
                    <div class="info-row"><span class="text-muted">PDFs/month</span><strong>
                            <?= $plan['benefits']['pdfs_per_month'] ?>
                        </strong></div>
                    <div class="info-row"><span class="text-muted">Chat messages</span><strong>
                            <?= $plan['benefits']['chat_messages'] ?>
                        </strong></div>
                    <div class="info-row"><span class="text-muted">Summaries</span><strong>
                            <?= $plan['benefits']['summaries'] ?>
                        </strong></div>
                    <div class="info-row"><span class="text-muted">Quizzes</span><strong>
                            <?= $plan['benefits']['quizzes'] ?>
                        </strong></div>
                    <div class="info-row"><span class="text-muted">Max pages</span><strong>
                            <?= $limits['pages'] ?>
                        </strong></div>
                    <div class="info-row"><span class="text-muted">Max file size</span><strong>
                            <?= $limits['size_mb'] ?>MB
                        </strong></div>
                </div>

            </div>

            <!-- Right -->
            <div class="col-lg-7">

                <!-- Usage History -->
                <div class="admin-card mb-4">
                    <h6 class="fw-700 mb-3">Usage History (Last 6 Months)</h6>
                    <?php if (empty($usageHistory)): ?>
                        <p class="text-muted small text-center py-3 mb-0">No usage data yet</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table mb-0" style="font-size:.83rem">
                                <thead>
                                    <tr style="background:#f8f9fc">
                                        <th style="padding:8px 12px;color:#64748b;font-weight:600">Month</th>
                                        <th style="padding:8px 12px;color:#64748b;font-weight:600">PDFs</th>
                                        <th style="padding:8px 12px;color:#64748b;font-weight:600">Chats</th>
                                        <th style="padding:8px 12px;color:#64748b;font-weight:600">Summaries</th>
                                        <th style="padding:8px 12px;color:#64748b;font-weight:600">Quizzes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usageHistory as $row): ?>
                                        <tr style="border-bottom:1px solid #f1f4f9">
                                            <td style="padding:10px 12px;font-weight:600">
                                                <?= date('M Y', strtotime($row['month'] . '-01')) ?>
                                            </td>
                                            <td style="padding:10px 12px">
                                                <?= number_format($row['pdfs_uploaded']) ?>
                                            </td>
                                            <td style="padding:10px 12px">
                                                <?= number_format($row['chat_messages']) ?>
                                            </td>
                                            <td style="padding:10px 12px">
                                                <?= number_format($row['summaries']) ?>
                                            </td>
                                            <td style="padding:10px 12px">
                                                <?= number_format($row['quizzes']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Credit Ledger -->
                <div class="admin-card">
                    <h6 class="fw-700 mb-3">Credit Ledger (Last 30)</h6>
                    <?php if (empty($ledger)): ?>
                        <p class="text-muted small text-center py-3 mb-0">No transactions yet</p>
                    <?php else: ?>
                        <div style="max-height:420px;overflow-y:auto">
                            <?php foreach ($ledger as $entry):
                                $featureKey = strtolower(explode('_', $entry['feature'])[0]);
                                [$icon, $color] = $featureIcons[$featureKey] ?? ['bi-circle', 'text-muted'];
                                $isCredit = $entry['credit_change'] > 0;
                                ?>
                                <div class="ledger-row">
                                    <div class="d-flex align-items-center gap-3">
                                        <div style="width:34px;height:34px;background:#f1f4f9;border-radius:9px;
                                        display:flex;align-items:center;justify-content:center">
                                            <i class="bi <?= $icon ?> <?= $color ?>"></i>
                                        </div>
                                        <div>
                                            <div class="fw-600" style="font-size:.83rem">
                                                <?= ucfirst(str_replace('_', ' ', $entry['feature'])) ?>
                                            </div>
                                            <?php if ($entry['note']): ?>
                                                <div class="text-muted" style="font-size:.75rem">
                                                    <?= htmlspecialchars($entry['note']) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="text-muted" style="font-size:.74rem">
                                                <?= date('M d, Y · g:ia', strtotime($entry['created_at'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-700 <?= $isCredit ? 'text-success' : 'text-danger' ?>">
                                            <?= ($isCredit ? '+' : '') . number_format($entry['credit_change']) ?>
                                        </div>
                                        <div class="text-muted" style="font-size:.74rem">
                                            Bal:
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>