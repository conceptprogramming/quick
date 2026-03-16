<?php
$seo = [
    'title' => 'Terms of Service | QuickChatPDF',
    'description' => 'Read the QuickChatPDF terms covering subscriptions, credits, top-ups, cancellations, payments, and acceptable use.',
    'canonical' => '/terms',
];
ob_start();
?>
<section class="qcp-policy-shell">
    <div class="qcp-policy-card">
        <div class="qcp-policy-head">
            <span class="qcp-policy-kicker">Terms</span>
            <h1>Terms of Service</h1>
            <p>These terms describe how QuickChatPDF subscriptions, credits, top-ups, and document tools are provided. If you need help with billing or account issues, contact <a href="mailto:support@quickchatpdf.com">support@quickchatpdf.com</a>.</p>
        </div>

        <div class="qcp-policy-block">
            <h2>Service overview</h2>
            <p>QuickChatPDF provides AI-powered PDF chat, summaries, quizzes, and related document tools. Features and limits depend on the active plan and available credits.</p>
        </div>

        <div class="qcp-policy-block">
            <h2>Subscriptions</h2>
            <p>Paid plans renew automatically through PayPal until cancelled. Cancelling a subscription stops the next renewal, but the current paid period remains active until its end date.</p>
        </div>

        <div class="qcp-policy-block">
            <h2>Credits and add-ons</h2>
            <p>Subscription credits are added when a paid subscription starts and on successful renewals. Chat credit top-ups are one-time wallet credits. Monthly PDF, summary, and quiz add-ons apply only to the current month usage limits.</p>
        </div>

        <div class="qcp-policy-block">
            <h2>Downgrade behavior</h2>
            <p>When a paid subscription fully ends, the account reverts to the free plan. Monthly paid-plan bonuses are cleared and wallet usage may be restricted until a paid subscription is active again.</p>
        </div>

        <div class="qcp-policy-block">
            <h2>Payments and refunds</h2>
            <p>Payments are processed by PayPal. Unless otherwise required by law or specifically agreed in writing, subscription charges and top-up purchases are treated as non-refundable once processed.</p>
        </div>

        <div class="qcp-policy-block">
            <h2>Acceptable use</h2>
            <p>You must not use QuickChatPDF for unlawful activity, abuse platform limits, interfere with system operation, or upload content you do not have the right to process.</p>
        </div>

        <div class="qcp-policy-block">
            <h2>Contact</h2>
            <p>Questions about these terms can be sent to <a href="mailto:support@quickchatpdf.com">support@quickchatpdf.com</a>. Privacy-specific questions should go to <a href="mailto:privacy@quickchatpdf.com">privacy@quickchatpdf.com</a>.</p>
        </div>

        <div class="qcp-policy-actions">
            <a href="<?= APP_URL ?>/" class="btn btn-primary">Back to Home</a>
            <a href="<?= APP_URL ?>/faq" class="btn btn-outline-secondary">FAQ</a>
            <a href="<?= APP_URL ?>/privacy" class="btn btn-outline-secondary">Privacy</a>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
ob_start();
?>
<style>
    .qcp-policy-shell{min-height:100vh;padding:48px 16px;background:linear-gradient(180deg,#f8fafc 0%,#eef2ff 100%)}
    .qcp-policy-card{max-width:860px;margin:0 auto;background:#fff;border:1px solid var(--qcp-border);border-radius:28px;padding:36px;box-shadow:0 24px 60px rgba(15,23,42,.08)}
    .qcp-policy-kicker{display:inline-flex;padding:7px 12px;border-radius:999px;background:rgba(79,70,229,.08);color:#4f46e5;font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px}
    .qcp-policy-head h1{font-family:var(--font-heading);font-size:clamp(2.2rem,5vw,3.5rem);font-weight:800;margin:0 0 10px;color:#0f172a}
    .qcp-policy-head p,.qcp-policy-block p{color:#475569;line-height:1.75}
    .qcp-policy-block + .qcp-policy-block{margin-top:24px;padding-top:24px;border-top:1px solid #e2e8f0}
    .qcp-policy-block h2{font-size:1.1rem;font-weight:800;color:#0f172a;margin-bottom:8px}
    .qcp-policy-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:28px}
</style>
<?php
$extraHead = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
