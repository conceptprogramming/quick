<?php
use Middleware\CSRFMiddleware;

$seo = [
    'title' => 'Login — QuickChatPDF',
    'description' => 'Log in to QuickChatPDF using your email. No password needed.',
    'canonical' => '/login',
];

$error = $_SESSION['auth_error'] ?? null;
unset($_SESSION['auth_error']);

ob_start();
?>

<div class="qcp-auth-page">

    <!-- ── Left Panel (desktop only) ── -->
    <div class="qcp-auth-left">
        <div class="qcp-auth-left-inner">

            <a href="<?= APP_URL ?>/" class="qcp-auth-logo">
                <div class="qcp-logo-icon" style="width:40px;height:40px;font-size:1.1rem">
                    <i class="bi bi-file-earmark-text-fill"></i>
                </div>
                <span>Quick<span style="opacity:.75">ChatPDF</span></span>
            </a>

            <div class="qcp-auth-left-body">
                <h2 class="qcp-auth-left-title">
                    Turn any PDF into<br>a conversation
                </h2>
                <p class="qcp-auth-left-sub">
                    Chat, summarize and quiz yourself from any document — powered by GPT-4o.
                </p>

                <div class="qcp-auth-features">
                    <?php
                    $features = [
                        ['bi-chat-dots-fill', 'Chat with your PDF', 'Ask anything, get instant answers'],
                        ['bi-file-text-fill', 'Smart Summaries', '8 summary styles to choose from'],
                        ['bi-ui-checks-grid', 'Interactive Quizzes', 'Timer, scoring & full review'],
                    ];
                    foreach ($features as [$icon, $title, $sub]): ?>
                        <div class="qcp-auth-feature">
                            <div class="qcp-auth-feature-icon">
                                <i class="bi <?= $icon ?>"></i>
                            </div>
                            <div>
                                <div class="qcp-auth-feature-title"><?= $title ?></div>
                                <div class="qcp-auth-feature-sub"><?= $sub ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="qcp-auth-left-footer">
                <div class="qcp-auth-stars">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <i class="bi bi-star-fill"></i>
                    <?php endfor; ?>
                    <span>4.9 · 2,000+ users</span>
                </div>
                <p class="qcp-auth-left-trust">
                    <i class="bi bi-shield-fill-check"></i>
                    Zero data retention · Your files are never stored
                </p>
            </div>

        </div>
    </div>

    <!-- ── Right Panel ── -->
    <div class="qcp-auth-right">
        <div class="qcp-auth-form-wrap">

            <!-- Logo (mobile only) -->
            <div class="qcp-auth-mobile-logo">
                <a href="<?= APP_URL ?>/" class="qcp-auth-logo-dark">
                    <div class="qcp-logo-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
                    <span>Quick<strong>ChatPDF</strong></span>
                </a>
            </div>

            <h1 class="qcp-auth-heading">Welcome back 👋</h1>
            <p class="qcp-auth-subheading">Enter your email — we'll send you a login code</p>

            <!-- Error -->
            <?php if ($error): ?>
                <div class="qcp-alert-danger d-flex align-items-center gap-2 p-3 mb-4" style="border-radius:10px">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="<?= APP_URL ?>/login" method="POST" id="loginForm">
                <?= CSRFMiddleware::field() ?>
                <div class="mb-4">
                    <label for="email" class="qcp-label">Email Address</label>
                    <div class="qcp-input-wrap">
                        <i class="bi bi-envelope-fill qcp-input-icon"></i>
                        <input type="email" id="email" name="email" class="form-control qcp-input"
                            placeholder="you@example.com" required autofocus autocomplete="email" />
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 qcp-submit-btn" id="submitBtn">
                    <span class="btn-text"><i class="bi bi-send-fill me-2"></i>Send Login Code</span>
                    <span class="btn-loading d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span>Sending...
                    </span>
                </button>
            </form>

            <div class="qcp-auth-divider">
                <span>Secure · Passwordless · Instant</span>
            </div>

            <div class="qcp-trust-row">
                <span><i class="bi bi-shield-fill-check text-success me-1"></i>No password</span>
                <span><i class="bi bi-lightning-charge-fill text-warning me-1"></i>20 free credits</span>
                <span><i class="bi bi-lock-fill text-primary me-1"></i>TLS encrypted</span>
            </div>

        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
ob_start();
?>
<style>
    /* ── Page Layout ── */
    .qcp-auth-page {
        display: flex;
        min-height: 100vh;
    }

    /* ── Left Panel ── */
    .qcp-auth-left {
        width: 400px;
        flex-shrink: 0;
        background: var(--qcp-gradient);
        padding: 0;
        position: relative;
        overflow: hidden;
    }

    .qcp-auth-left::before {
        content: '';
        position: absolute;
        top: -100px;
        right: -100px;
        width: 320px;
        height: 320px;
        background: rgba(255, 255, 255, .06);
        border-radius: 50%;
        pointer-events: none;
    }

    .qcp-auth-left::after {
        content: '';
        position: absolute;
        bottom: -80px;
        left: -80px;
        width: 260px;
        height: 260px;
        background: rgba(255, 255, 255, .04);
        border-radius: 50%;
        pointer-events: none;
    }

    .qcp-auth-left-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 44px 36px;
    }

    .qcp-auth-logo {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        font-family: var(--font-heading);
        font-size: 1.15rem;
        font-weight: 800;
        color: #fff;
        margin-bottom: 0;
    }

    .qcp-auth-left-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 40px 0;
    }

    .qcp-auth-left-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: #fff;
        line-height: 1.25;
        font-family: var(--font-heading);
        margin-bottom: 12px;
    }

    .qcp-auth-left-sub {
        color: rgba(255, 255, 255, .7);
        font-size: .9rem;
        line-height: 1.6;
        margin-bottom: 32px;
    }

    .qcp-auth-features {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .qcp-auth-feature {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .qcp-auth-feature-icon {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, .15);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        color: #fff;
        flex-shrink: 0;
    }

    .qcp-auth-feature-title {
        font-size: .88rem;
        font-weight: 600;
        color: #fff;
        margin-bottom: 2px;
    }

    .qcp-auth-feature-sub {
        font-size: .76rem;
        color: rgba(255, 255, 255, .6);
    }

    .qcp-auth-left-footer {
        padding-top: 24px;
        border-top: 1px solid rgba(255, 255, 255, .12);
    }

    .qcp-auth-stars {
        display: flex;
        align-items: center;
        gap: 3px;
        margin-bottom: 8px;
        font-size: .78rem;
        color: #fbbf24;
    }

    .qcp-auth-stars span {
        color: rgba(255, 255, 255, .6);
        margin-left: 6px;
    }

    .qcp-auth-left-trust {
        font-size: .78rem;
        color: rgba(255, 255, 255, .5);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .qcp-auth-left-trust i {
        color: #4ade80;
    }

    /* ── Right Panel ── */
    .qcp-auth-right {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        padding: 40px 24px;
        min-height: 100vh;
    }

    .qcp-auth-form-wrap {
        width: 100%;
        max-width: 400px;
    }

    /* ── Mobile Logo ── */
    .qcp-auth-mobile-logo {
        display: none;
        text-align: center;
        margin-bottom: 32px;
    }

    .qcp-auth-logo-dark {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        font-family: var(--font-heading);
        font-size: 1.2rem;
        font-weight: 800;
        color: #0f172a;
    }

    /* ── Form Text ── */
    .qcp-auth-heading {
        font-size: 1.6rem;
        font-weight: 800;
        color: #0f172a;
        font-family: var(--font-heading);
        margin-bottom: 6px;
    }

    .qcp-auth-subheading {
        color: #64748b;
        font-size: .9rem;
        margin-bottom: 28px;
    }

    /* ── Submit ── */
    .qcp-submit-btn {
        height: 50px;
        font-size: .95rem;
        font-weight: 600;
    }

    /* ── Divider ── */
    .qcp-auth-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #94a3b8;
        font-size: .76rem;
        margin: 24px 0 16px;
    }

    .qcp-auth-divider::before,
    .qcp-auth-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }

    /* ── Trust Row ── */
    .qcp-trust-row {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
        font-size: .78rem;
        color: #64748b;
        font-weight: 500;
    }

    /* ── Mobile ── */
    @media (max-width: 991px) {
        .qcp-auth-page {
            flex-direction: column;
        }

        .qcp-auth-left {
            display: none !important;
        }

        .qcp-auth-right {
            min-height: 100vh;
            padding: 48px 24px;
            align-items: center;
        }

        .qcp-auth-mobile-logo {
            display: block;
        }

        .qcp-trust-row {
            gap: 12px;
        }
    }
</style>
<script>
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.querySelector('.btn-text').classList.add('d-none');
        btn.querySelector('.btn-loading').classList.remove('d-none');
        btn.disabled = true;
    });
</script>
<?php
$extraScripts = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
