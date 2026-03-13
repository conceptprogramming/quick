<?php
$seo = ['title' => 'Plans & Pricing — QuickChatPDF', 'canonical' => '/plans'];
ob_start();
$appUrl = APP_URL;
$userPlanKey = $user['plan'] ?? 'free';
$userCredits = (int) ($user['credits'] ?? 0);
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
                <?= number_format($userCredits) ?> credits
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

<div class="container py-5">

    <!-- Header -->
    <div class="text-center mb-5">
        <span class="qcp-badge mb-3"><i class="bi bi-tag-fill me-2"></i>Plans & Pricing</span>
        <h1 class="qcp-section-title mb-2">Choose the right plan</h1>
        <p class="qcp-section-sub">Upgrade anytime · Cancel anytime · Secure payments via PayPal</p>
    </div>

    <!-- Current Plan Banner -->
    <div class="qcp-current-plan-bar mb-5">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="qcp-feature-icon qcp-icon-purple flex-shrink-0">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <div>
                <div class="fw-700 text-dark">
                    You're on the <span class="qcp-gradient-text">
                        <?= ucfirst($userPlanKey) ?>
                    </span> plan
                </div>
                <div class="text-muted small">
                    <?= number_format($userCredits) ?> credits remaining ·
                    <?= $usage['pdfs_uploaded'] ?? 0 ?>/
                    <?= $currentPlan['benefits']['pdfs_per_month'] ?> PDFs used this month ·
                    <?= $usage['summaries'] ?? 0 ?>/
                    <?= $currentPlan['benefits']['summaries'] ?> summaries ·
                    <?= $usage['quizzes'] ?? 0 ?>/
                    <?= $currentPlan['benefits']['quizzes'] ?> quizzes
                </div>
            </div>
            <?php if ($userPlanKey !== 'free'): ?>
                <div class="ms-auto">
                    <span class="badge bg-success px-3 py-2">
                        <i class="bi bi-check-circle-fill me-1"></i>Active Subscription
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Plan Cards -->
    <div class="row g-4 justify-content-center align-items-stretch mb-5">
        <?php
        $planMeta = [
            'free' => ['icon' => 'bi-stars', 'color' => 'purple', 'desc' => 'Perfect for trying out'],
            'basic' => ['icon' => 'bi-lightning-fill', 'color' => 'blue', 'desc' => 'For regular users'],
            'pro' => ['icon' => 'bi-rocket-takeoff-fill', 'color' => 'orange', 'desc' => 'Most popular choice'],
            'professional' => ['icon' => 'bi-building-fill', 'color' => 'teal', 'desc' => 'For power users & teams'],
        ];
        foreach (PLANS as $key => $plan):
            $meta = $planMeta[$key];
            $isCurrent = ($key === $userPlanKey);
            $isPopular = ($key === 'pro');
            $limits = PDF_LIMITS[$key] ?? PDF_LIMITS['free'];
            ?>
            <div class="col-sm-6 col-lg-3">
                <div
                    class="qcp-pricing-card h-100 <?= $isPopular ? 'qcp-pricing-popular' : '' ?> <?= $isCurrent ? 'qcp-pricing-current' : '' ?>">

                    <?php if ($isPopular && !$isCurrent): ?>
                        <div class="qcp-popular-badge">⭐ Most Popular</div>
                    <?php endif; ?>

                    <?php if ($isCurrent): ?>
                        <div class="qcp-popular-badge" style="background:linear-gradient(135deg,#22c55e,#16a34a)">
                            <i class="bi bi-check-circle-fill me-1"></i> Current Plan
                        </div>
                    <?php endif; ?>

                    <!-- Header -->
                    <div class="qcp-pricing-header">
                        <div class="qcp-feature-icon qcp-icon-<?= $meta['color'] ?> mb-3">
                            <i class="bi <?= $meta['icon'] ?>"></i>
                        </div>
                        <h5>
                            <?= $plan['name'] ?>
                        </h5>
                        <p class="text-muted small mb-3">
                            <?= $meta['desc'] ?>
                        </p>
                        <div class="d-flex align-items-end gap-1 mb-1">
                            <span class="qcp-price-currency">$</span>
                            <span class="qcp-price-amount">
                                <?= number_format($plan['price'], 2) ?>
                            </span>
                            <span class="qcp-price-period ms-1">/mo</span>
                        </div>
                        <div class="small text-muted">
                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
                            <?= number_format($plan['monthly_credits']) ?> credits/month
                        </div>
                    </div>

                    <!-- Features -->
                    <ul class="qcp-pricing-features list-unstyled flex-grow-1 mb-4">
                        <li><i class="bi bi-check2 me-2" style="color:var(--qcp-primary)"></i>
                            <?= $plan['benefits']['pdfs_per_month'] ?> PDFs per month
                        </li>
                        <li><i class="bi bi-check2 me-2" style="color:var(--qcp-primary)"></i>
                            <?= $plan['benefits']['chat_messages'] ?> chat messages
                        </li>
                        <li><i class="bi bi-check2 me-2" style="color:var(--qcp-primary)"></i>
                            <?= $plan['benefits']['summaries'] ?> summaries
                        </li>
                        <li><i class="bi bi-check2 me-2" style="color:var(--qcp-primary)"></i>
                            <?= $plan['benefits']['quizzes'] ?> quizzes
                        </li>
                        <li><i class="bi bi-check2 me-2" style="color:var(--qcp-primary)"></i>Up to
                            <?= $limits['pages'] ?> pages per PDF
                        </li>
                        <li><i class="bi bi-check2 me-2" style="color:var(--qcp-primary)"></i>Up to
                            <?= $limits['size_mb'] ?>MB per PDF
                        </li>
                        <li><i class="bi bi-shield-fill-check me-2 text-success"></i>Zero data retention</li>
                    </ul>

                    <!-- CTA Button -->
                    <?php if ($isCurrent): ?>
                        <button class="btn btn-outline-secondary w-100" disabled>
                            <i class="bi bi-check-circle-fill me-2 text-success"></i>Current Plan
                        </button>
                    <?php elseif ($plan['price'] == 0): ?>
                        <button class="btn btn-outline-primary w-100" disabled>Free Plan</button>
                    <?php else: ?>
                        <button class="btn <?= $isPopular ? 'btn-primary' : 'btn-outline-primary' ?> w-100 qcp-paypal-btn"
                            data-plan="<?= $key ?>" data-plan-id="<?= $plan['paypal_plan_id'] ?>"
                            data-name="<?= $plan['name'] ?>" data-price="<?= $plan['price'] ?>">
                            <i class="bi bi-paypal me-2"></i>Subscribe — $
                            <?= number_format($plan['price'], 2) ?>/mo
                        </button>
                    <?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Credit Top-Up Packs -->
    <div class="qcp-topup-section">
        <div class="text-center mb-4">
            <h4 class="fw-800 mb-1">Need more credits?</h4>
            <p class="text-muted small">One-time top-up packs for active paid subscribers</p>
            <?php if ((($user['plan'] ?? 'free') === 'free') || empty($user['paypal_subscription_id'])): ?>
                <p class="text-muted small mb-0">Upgrade to a paid plan to unlock top-up purchases.</p>
            <?php endif; ?>
        </div>

        <div class="row g-4 justify-content-center">
            <?php
            $packMeta = [
                'small' => ['icon' => 'bi-lightning-charge', 'color' => 'blue', 'label' => 'Starter'],
                'medium' => ['icon' => 'bi-lightning-fill', 'color' => 'purple', 'label' => 'Popular'],
                'large' => ['icon' => 'bi-lightning-charge-fill', 'color' => 'orange', 'label' => 'Best Value'],
            ];
            $canTopup = (($user['plan'] ?? 'free') !== 'free') && !empty($user['paypal_subscription_id']);
            foreach (TOPUP_PACKS as $packKey => $pack):
                $meta = $packMeta[$packKey];
                ?>
                <div class="col-sm-4 col-lg-3">
                    <div class="qcp-topup-card <?= $packKey === 'medium' ? 'qcp-topup-featured' : '' ?>">
                        <?php if ($packKey === 'medium'): ?>
                            <div class="qcp-topup-ribbon">Popular</div>
                        <?php endif; ?>
                        <div class="qcp-feature-icon qcp-icon-<?= $meta['color'] ?> mx-auto mb-3">
                            <i class="bi <?= $meta['icon'] ?>"></i>
                        </div>
                        <div class="fw-700 mb-1">
                            <?= $meta['label'] ?> Pack
                        </div>
                        <div class="qcp-topup-credits">
                            <?= number_format($pack['credits']) ?>
                            <span class="qcp-topup-unit">credits</span>
                        </div>
                        <div class="qcp-topup-price mb-3">$
                            <?= number_format($pack['price'], 2) ?> one-time
                        </div>
                        <div class="text-muted small mb-4">
                            $
                            <?= number_format($pack['price'] / $pack['credits'], 4) ?> per credit
                        </div>
                        <button
                            class="btn <?= $packKey === 'medium' ? 'btn-primary' : 'btn-outline-primary' ?> w-100 qcp-topup-btn"
                            data-pack="<?= $packKey ?>" data-credits="<?= $pack['credits'] ?>"
                            data-price="<?= $pack['price'] ?>" <?= $canTopup ? '' : 'disabled' ?>>
                            <i class="bi bi-paypal me-2"></i>Buy for $
                            <?= number_format($pack['price'], 2) ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Trust Bar -->
    <div class="d-flex flex-wrap justify-content-center gap-4 mt-5 pt-3">
        <span class="qcp-trust-item"><i class="bi bi-shield-fill-check text-success me-2"></i>Secure PayPal
            Checkout</span>
        <span class="qcp-trust-item"><i class="bi bi-arrow-counterclockwise text-primary me-2"></i>Cancel Anytime</span>
        <span class="qcp-trust-item"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Credits Added
            Instantly</span>
        <span class="qcp-trust-item"><i class="bi bi-headset text-purple me-2"></i>24/7 Support</span>
    </div>

</div>

<!-- PayPal Modal -->
<div class="modal fade" id="paypalModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:20px;border:1px solid var(--qcp-border)">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-700" id="modalTitle">Complete Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3 pb-4 px-4">

                <!-- Order Summary -->
                <div class="qcp-order-summary mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Plan / Pack</span>
                        <span class="fw-600 small" id="modalPlanName">—</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Amount</span>
                        <span class="fw-700" id="modalAmount">—</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Credits</span>
                        <span class="fw-600 small text-success" id="modalCredits">—</span>
                    </div>
                </div>

                <!-- PayPal Button Container -->
                <div id="paypal-button-container"></div>

                <p class="text-center text-muted mt-3 mb-0" style="font-size:.78rem">
                    <i class="bi bi-shield-fill-check text-success me-1"></i>
                    Secured by PayPal · Your financial info is never shared with us
                </p>
            </div>
        </div>
    </div>
</div>

<?php
$content        = ob_get_clean();
$paypalClientId = PAYPAL_CLIENT_ID;
$csrfToken      = \Middleware\CSRFMiddleware::generate();
$paypalSDKUrl   = PAYPAL_MODE === 'live'          
    ? 'https://www.paypal.com/sdk/js'
    : 'https://www.sandbox.paypal.com/sdk/js';
ob_start();
?>


<style>
    /* ── Current Plan Bar ── */
    .qcp-current-plan-bar {
        background: #fff;
        border: 1px solid var(--qcp-border);
        border-radius: var(--qcp-radius);
        padding: 20px 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
    }

    /* ── Pricing Current ── */
    .qcp-pricing-current {
        border-color: #22c55e !important;
        box-shadow: 0 8px 30px rgba(34, 197, 94, .12) !important;
    }

    /* ── Top-Up Section ── */
    .qcp-topup-section {
        background: #fff;
        border: 1px solid var(--qcp-border);
        border-radius: 20px;
        padding: 40px 32px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, .04);
    }

    .qcp-topup-card {
        background: var(--qcp-bg);
        border: 2px solid var(--qcp-border);
        border-radius: 16px;
        padding: 28px 20px;
        text-align: center;
        position: relative;
        transition: all .25s;
    }

    .qcp-topup-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(108, 71, 255, .1);
        border-color: var(--qcp-primary);
    }

    .qcp-topup-featured {
        background: #fff;
        border-color: var(--qcp-primary);
        box-shadow: 0 8px 30px rgba(108, 71, 255, .12);
    }

    .qcp-topup-ribbon {
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--qcp-gradient);
        color: #fff;
        font-size: .72rem;
        font-weight: 700;
        padding: 3px 14px;
        border-radius: 100px;
    }

    .qcp-topup-credits {
        font-family: var(--font-heading);
        font-size: 2rem;
        font-weight: 800;
        background: var(--qcp-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1.2;
    }

    .qcp-topup-unit {
        font-size: .85rem;
        font-weight: 600;
        -webkit-text-fill-color: var(--qcp-muted);
        color: var(--qcp-muted);
    }

    .qcp-topup-price {
        font-size: .9rem;
        font-weight: 600;
        color: #0f172a;
    }

    /* ── Order Summary ── */
    .qcp-order-summary {
        background: var(--qcp-bg);
        border: 1px solid var(--qcp-border);
        border-radius: 12px;
        padding: 16px 18px;
    }

    /* ── Trust Bar ── */
    .qcp-trust-item {
        font-size: .82rem;
        color: #64748b;
        font-weight: 500;
        display: flex;
        align-items: center;
    }
</style>

<!-- PayPal SDK -->
<!-- ✅ CORRECT — switches based on PAYPAL_MODE -->
<script src="<?= $paypalSDKUrl ?>?client-id=<?= htmlspecialchars($paypalClientId) ?>&vault=true&intent=subscription"
        data-sdk-integration-source="button-factory"></script>


<script>
    const CSRF = '<?= $csrfToken ?>';
    let paypalButtonInstance = null;

    // ── Plan subscribe ────────────────────────────────────────
    document.querySelectorAll('.qcp-paypal-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const plan = this.dataset.plan;
            const planId = this.dataset.planId;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);
            const credits = <?= json_encode(array_map(fn($p) => $p['monthly_credits'], PLANS)) ?> [plan] || 0;

    document.getElementById('modalTitle').textContent = 'Subscribe to ' + name;
    document.getElementById('modalPlanName').textContent = name + ' Plan';
    document.getElementById('modalAmount').textContent = '$' + price.toFixed(2) + '/month';
    document.getElementById('modalCredits').textContent = credits + ' credits/month';

    renderPayPalSubscription(planId, plan, 'subscription');
    new bootstrap.Modal(document.getElementById('paypalModal')).show();
    });
});

    // ── Top-up buy ────────────────────────────────────────────
    document.querySelectorAll('.qcp-topup-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const pack = this.dataset.pack;
            const credits = parseInt(this.dataset.credits);
            const price = parseFloat(this.dataset.price);

            document.getElementById('modalTitle').textContent = 'Buy Credit Pack';
            document.getElementById('modalPlanName').textContent = credits + ' Credit Top-Up';
            document.getElementById('modalAmount').textContent = '$' + price.toFixed(2) + ' one-time';
            document.getElementById('modalCredits').textContent = '+' + credits + ' credits added instantly';

            renderPayPalTopup(pack, credits, price);
            new bootstrap.Modal(document.getElementById('paypalModal')).show();
        });
    });

    // ── Render PayPal Subscription Button ─────────────────────
    function renderPayPalSubscription(planId, planKey, type) {
        clearPayPalContainer();
        paypalButtonInstance = paypal.Buttons({
            style: {
                shape: 'rect', color: 'gold', layout: 'vertical', label: 'subscribe',
            },
            createSubscription: function (data, actions) {
                return actions.subscription.create({ plan_id: planId });
            },
            onApprove: function (data) {
                handleSuccess({
                    type: 'subscription',
                    plan: planKey,
                    subscription_id: data.subscriptionID,
                });
            },
            onError: function (err) {
                showPayPalError('Payment failed. Please try again.');
            },
            onCancel: function () {
                bootstrap.Modal.getInstance(document.getElementById('paypalModal')).hide();
            },
        });
        paypalButtonInstance.render('#paypal-button-container');
    }

    // ── Render PayPal One-Time Top-Up Button ──────────────────
    function renderPayPalTopup(packKey, credits, price) {
        clearPayPalContainer();
        paypalButtonInstance = paypal.Buttons({
            style: {
                shape: 'rect', color: 'gold', layout: 'vertical', label: 'pay',
            },
            createOrder: function (data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: { value: price.toFixed(2) },
                        description: credits + ' QuickChatPDF Credits',
                    }],
                });
            },
            onApprove: function (data, actions) {
                return actions.order.capture().then(function (details) {
                    handleSuccess({
                        type: 'topup',
                        pack: packKey,
                        order_id: details.id,
                    });
                });
            },
            onError: function (err) {
                showPayPalError('Payment failed. Please try again.');
            },
            onCancel: function () {
                bootstrap.Modal.getInstance(document.getElementById('paypalModal')).hide();
            },
        });
        paypalButtonInstance.render('#paypal-button-container');
    }

    // ── Handle Success ─────────────────────────────────────────
    function handleSuccess(payload) {
        payload._csrf = CSRF;
        document.getElementById('paypal-button-container').innerHTML =
            '<div class="text-center py-4">' +
            '<div class="spinner-border text-primary mb-3" role="status"></div>' +
            '<p class="text-muted mb-0">Processing your payment...</p>' +
            '</div>';

        fetch('<?= $appUrl ?>/payment/confirm', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                bootstrap.Modal.getInstance(document.getElementById('paypalModal')).hide();
                if (data.success) {
                    showToast(data.message || 'Payment successful! Credits added.', 'success');
                    setTimeout(function () { location.reload(); }, 1800);
                } else {
                    showToast(data.message || 'Something went wrong.', 'danger');
                }
            })
            .catch(function () {
                showToast('Server error. Contact support.', 'danger');
            });
    }

    function clearPayPalContainer() {
        if (paypalButtonInstance) {
            try { paypalButtonInstance.close(); } catch (e) { }
            paypalButtonInstance = null;
        }
        document.getElementById('paypal-button-container').innerHTML = '';
    }

    function showPayPalError(msg) {
        document.getElementById('paypal-button-container').innerHTML =
            '<div class="alert qcp-alert-danger text-center">' + msg + '</div>';
    }

    // ── Dismiss modal clears PayPal ───────────────────────────
    document.getElementById('paypalModal').addEventListener('hidden.bs.modal', function () {
        clearPayPalContainer();
    });

    // ── Toast helper ──────────────────────────────────────────
    function showToast(msg, type) {
        type = type || 'primary';
        const colors = { success: '#22c55e', danger: '#ef4444', primary: 'var(--qcp-primary)' };
        const t = document.createElement('div');
        t.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;' +
            'background:#fff;border:1px solid var(--qcp-border);' +
            'border-left:4px solid ' + (colors[type] || colors.primary) + ';' +
            'border-radius:12px;padding:14px 18px;box-shadow:0 8px 24px rgba(0,0,0,.1);' +
            'font-size:.9rem;font-weight:500;color:#0f172a;max-width:320px;' +
            'animation:slideUp .3s ease';
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(function () { t.remove(); }, 4000);
    }

    // Inject keyframe once
    if (!document.getElementById('toastStyle')) {
        const s = document.createElement('style');
        s.id = 'toastStyle';
        s.textContent = '@keyframes slideUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}';
        document.head.appendChild(s);
    }
</script>
<?php
$extraScripts = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
