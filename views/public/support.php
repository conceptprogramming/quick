<?php
$seo = [
    'title' => 'Support | QuickChatPDF',
    'description' => 'Get help with account access, subscriptions, payments, and PDF processing for QuickChatPDF.',
    'canonical' => '/support',
];
ob_start();
?>
<section class="qcp-policy-shell">
    <div class="qcp-policy-card">
        <div class="qcp-policy-head">
            <span class="qcp-policy-kicker">Support</span>
            <h1>Support</h1>
            <p>If you need help with billing, subscriptions, credits, PDF processing, or account access, contact <a href="mailto:support@quickchatpdf.com">support@quickchatpdf.com</a>.</p>
        </div>

        <div class="qcp-policy-grid">
            <div class="qcp-support-panel">
                <h2>General Support</h2>
                <p>For login issues, plan questions, top-ups, or PDF tool problems.</p>
                <a href="mailto:support@quickchatpdf.com" class="qcp-support-link">support@quickchatpdf.com</a>
            </div>
            <div class="qcp-support-panel">
                <h2>Privacy Requests</h2>
                <p>For privacy concerns, data handling questions, and account/privacy requests.</p>
                <a href="mailto:privacy@quickchatpdf.com" class="qcp-support-link">privacy@quickchatpdf.com</a>
            </div>
        </div>

        <div class="qcp-policy-block">
            <h2>What to include in your email</h2>
            <p>Share your account email, the page or feature affected, what happened, and any screenshot or exact error message. That helps us resolve issues much faster.</p>
        </div>

        <div class="qcp-policy-actions">
            <a href="<?= APP_URL ?>/" class="btn btn-primary">Back to Home</a>
            <a href="<?= APP_URL ?>/privacy" class="btn btn-outline-secondary">Privacy Policy</a>
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
    .qcp-policy-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin-top:26px}
    .qcp-support-panel{padding:22px;border:1px solid #e2e8f0;border-radius:22px;background:linear-gradient(180deg,#fff 0%,#f8fafc 100%)}
    .qcp-support-panel h2{font-size:1.05rem;font-weight:800;color:#0f172a;margin-bottom:8px}
    .qcp-support-panel p{margin-bottom:12px;color:#64748b}
    .qcp-support-link{font-weight:700;color:var(--qcp-primary);text-decoration:none}
    .qcp-policy-block + .qcp-policy-block{margin-top:24px;padding-top:24px;border-top:1px solid #e2e8f0}
    .qcp-policy-block h2{font-size:1.1rem;font-weight:800;color:#0f172a;margin-bottom:8px}
    .qcp-policy-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:28px}
    @media (max-width: 700px){.qcp-policy-grid{grid-template-columns:1fr}.qcp-policy-card{padding:24px}}
</style>
<?php
$extraHead = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
