<?php
$seo = [
    'title' => 'FAQ | QuickChatPDF',
    'description' => 'Frequently asked questions about QuickChatPDF, including credits, subscriptions, payments, renewals, cancellations, and PDF privacy.',
    'canonical' => '/faq',
];
ob_start();
$faqs = [
    [
        'Are my PDFs stored on your servers?',
        'No. QuickChatPDF is designed for zero-retention processing. Your PDF is processed temporarily and removed after use rather than being stored as a permanent document record.'
    ],
    [
        'What is a credit?',
        'Credits are used when you run AI actions. Chat uses 1 credit, summaries use 3 credits, Q&A uses 2 credits, and quizzes use 2 credits per quiz.'
    ],
    [
        'How do subscription credits work?',
        'Paid plans add monthly credits when the subscription starts and on each successful renewal. Those credits are added to your wallet balance.'
    ],
    [
        'How do top-up credits work?',
        'Top-up chat credits are one-time wallet credits. They do not expire, but in the current model they are only usable while the account is effectively in a paid subscription state.'
    ],
    [
        'What happens when I cancel a subscription?',
        'Cancellation stops renewal. Your current paid plan stays active until the paid period end date shown in your subscription record. During that time, plan features and wallet usage continue.'
    ],
    [
        'What happens after the paid period ends?',
        'When the subscription fully ends, the account is downgraded to the free plan. Monthly paid-plan bonuses are cleared, plan limits return to free limits, and wallet usage is locked until the user subscribes again.'
    ],
    [
        'Do PDF, summary, and quiz add-on packs expire?',
        'Yes. Monthly add-on packs increase the current month usage limits only. They are not permanent wallet credits.'
    ],
    [
        'How are payments handled?',
        'QuickChatPDF uses PayPal for subscriptions and one-time purchases. The app stores payment references and subscription metadata required for billing, renewals, cancellations, and support.'
    ],
    [
        'How are renewals and subscription status updates synced?',
        'The app uses PayPal webhooks as the primary sync path and a cron job as a fallback safety net for renewals and status reconciliation.'
    ],
    [
        'Who should I contact for billing or privacy questions?',
        'For support, email support@quickchatpdf.com. For privacy questions, email privacy@quickchatpdf.com.'
    ],
];
?>
<section class="qcp-faq-page-shell">
    <div class="qcp-faq-page-card">
        <div class="qcp-policy-head">
            <span class="qcp-policy-kicker">FAQ</span>
            <h1>Frequently Asked Questions</h1>
            <p>This page covers document privacy, credits, subscriptions, renewals, cancellations, and payment terms for QuickChatPDF.</p>
        </div>

        <div class="qcp-terms-grid">
            <div class="qcp-terms-panel">
                <h2>Subscription Terms</h2>
                <ul>
                    <li>Plans renew automatically through PayPal until cancelled.</li>
                    <li>Cancellation stops the next renewal, not the current paid period.</li>
                    <li>Paid plan access continues until the subscription period ends.</li>
                    <li>After period end, the account falls back to the free plan.</li>
                </ul>
            </div>
            <div class="qcp-terms-panel">
                <h2>Payment Terms</h2>
                <ul>
                    <li>Payments are processed through PayPal.</li>
                    <li>Top-up purchases are one-time charges.</li>
                    <li>Monthly add-on packs affect current-month usage limits only.</li>
                    <li>Chat credit top-ups are wallet credits and do not expire.</li>
                </ul>
            </div>
        </div>

        <div class="accordion qcp-faq-accordion" id="publicFaqAccordion">
            <?php foreach ($faqs as $i => [$q, $a]): ?>
                <div class="accordion-item qcp-faq-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#publicFaq<?= $i ?>">
                            <?= htmlspecialchars($q) ?>
                        </button>
                    </h2>
                    <div id="publicFaq<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#publicFaqAccordion">
                        <div class="accordion-body"><?= htmlspecialchars($a) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="qcp-policy-actions">
            <a href="<?= APP_URL ?>/" class="btn btn-primary">Back to Home</a>
            <a href="<?= APP_URL ?>/support" class="btn btn-outline-secondary">Support</a>
            <a href="<?= APP_URL ?>/privacy" class="btn btn-outline-secondary">Privacy</a>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
ob_start();
?>
<style>
    .qcp-faq-page-shell{min-height:100vh;padding:48px 16px;background:linear-gradient(180deg,#f8fafc 0%,#eef2ff 100%)}
    .qcp-faq-page-card{max-width:960px;margin:0 auto;background:#fff;border:1px solid var(--qcp-border);border-radius:28px;padding:36px;box-shadow:0 24px 60px rgba(15,23,42,.08)}
    .qcp-policy-kicker{display:inline-flex;padding:7px 12px;border-radius:999px;background:rgba(79,70,229,.08);color:#4f46e5;font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px}
    .qcp-policy-head h1{font-family:var(--font-heading);font-size:clamp(2.2rem,5vw,3.5rem);font-weight:800;margin:0 0 10px;color:#0f172a}
    .qcp-policy-head p{color:#475569;line-height:1.75;margin-bottom:24px}
    .qcp-terms-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin-bottom:28px}
    .qcp-terms-panel{padding:22px;border:1px solid #e2e8f0;border-radius:22px;background:linear-gradient(180deg,#fff 0%,#f8fafc 100%)}
    .qcp-terms-panel h2{font-size:1.05rem;font-weight:800;color:#0f172a;margin-bottom:10px}
    .qcp-terms-panel ul{margin:0;padding-left:18px;color:#475569;line-height:1.7}
    .qcp-terms-panel li+li{margin-top:6px}
    .qcp-faq-accordion .accordion-item{border:1px solid #e2e8f0;border-radius:18px;overflow:hidden}
    .qcp-faq-accordion .accordion-item + .accordion-item{margin-top:12px}
    .qcp-faq-accordion .accordion-button{font-weight:700;color:#0f172a;background:#fff;box-shadow:none}
    .qcp-faq-accordion .accordion-button:not(.collapsed){background:#f8fafc;color:#4f46e5}
    .qcp-faq-accordion .accordion-body{color:#475569;line-height:1.7}
    .qcp-policy-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:28px}
    @media (max-width: 700px){.qcp-faq-page-card{padding:24px}.qcp-terms-grid{grid-template-columns:1fr}}
</style>
<?php
$extraHead = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
