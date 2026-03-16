<?php
$seo = [
    'title' => 'Privacy Policy | QuickChatPDF',
    'description' => 'Read how QuickChatPDF handles documents, email addresses, cookies, and zero-retention processing.',
    'canonical' => '/privacy',
];
ob_start();
?>
<section class="qcp-policy-shell">
    <div class="qcp-policy-card">
        <div class="qcp-policy-head">
            <span class="qcp-policy-kicker">Privacy</span>
            <h1>Privacy Policy</h1>
            <p>QuickChatPDF is built around minimal data collection and short-lived document processing. For privacy questions, contact <a href="mailto:privacy@quickchatpdf.com">privacy@quickchatpdf.com</a>.</p>
        </div>

        <div class="qcp-policy-block">
            <h2>What we collect</h2>
            <p>We collect your account email, plan/payment metadata, and the minimum usage records needed to operate credits, subscriptions, and account access.</p>
        </div>

        <div class="qcp-policy-block">
            <h2>How PDF processing works</h2>
            <p>Uploaded PDFs are processed temporarily for OCR and AI tasks. QuickChatPDF is designed for zero-retention document handling, which means files are processed and then removed after use.</p>
        </div>

        <div class="qcp-policy-block">
            <h2>Cookies</h2>
            <p>We use essential cookies for session management, security, and account login. Optional cookie consent preferences are stored locally in your browser.</p>
        </div>

        <div class="qcp-policy-block">
            <h2>Payments</h2>
            <p>Payments are processed through PayPal. We store payment references and subscription metadata required to manage credits, renewals, cancellations, and support requests.</p>
        </div>

        <div class="qcp-policy-block">
            <h2>Contact</h2>
            <p>If you need deletion, clarification, or privacy support, email <a href="mailto:privacy@quickchatpdf.com">privacy@quickchatpdf.com</a>.</p>
        </div>

        <div class="qcp-policy-actions">
            <a href="<?= APP_URL ?>/" class="btn btn-primary">Back to Home</a>
            <a href="<?= APP_URL ?>/support" class="btn btn-outline-secondary">Support</a>
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
