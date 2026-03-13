<?php
$seo = [
    'title' => 'QuickChatPDF — Chat, Summarize & Quiz Any PDF with AI',
    'description' => 'Upload a PDF and instantly chat with it, generate summaries, Q&A sets, and MCQ or True/False quizzes. Zero retention — your documents are never stored.',
    'keywords' => 'chat with pdf, pdf summary ai, pdf quiz generator, ai pdf tool, pdf qa generator',
    'canonical' => '/',
];

ob_start();
?>

<!-- ── NAVBAR ─────────────────────────────────────────────── -->
<nav class="navbar navbar-expand-lg navbar-light qcp-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= APP_URL ?>/">
            <div class="qcp-logo-icon">
                <i class="bi bi-file-earmark-text-fill"></i>
            </div>
            <span>Quick<strong>ChatPDF</strong></span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto gap-1">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#how-it-works">How It Works</a></li>
                <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
            </ul>
            <div class="d-flex gap-2 align-items-center">
                <a href="<?= APP_URL ?>/login" class="btn btn-outline-light btn-sm px-3">Login</a>
                <a href="<?= APP_URL ?>/login" class="btn btn-primary btn-sm px-3">Get Started Free</a>
            </div>
        </div>
    </div>
</nav>

<!-- ── HERO ──────────────────────────────────────────────── -->
<section class="qcp-hero d-flex align-items-center">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="qcp-badge mb-3">
                    <i class="bi bi-shield-check-fill me-1"></i> Zero Retention · Privacy First
                </div>
                <h1 class="qcp-hero-title">
                    Chat with Any PDF<br />
                    <span class="qcp-gradient-text">Powered by AI</span>
                </h1>
                <p class="qcp-hero-sub mt-3">
                    Upload a PDF and instantly get AI-powered chat, summaries, Q&amp;A sets, and quizzes.
                    Your documents are <strong>never stored</strong> — processed and deleted in seconds.
                </p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="<?= APP_URL ?>/login" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-rocket-takeoff me-2"></i>Start for Free
                    </a>
                    <a href="#how-it-works" class="btn btn-outline-light btn-lg px-4">
                        See How It Works
                    </a>
                </div>
                <div class="qcp-trust-badges mt-4 d-flex flex-wrap gap-3">
                    <span><i class="bi bi-lock-fill text-success me-1"></i>No signup hassle</span>
                    <span><i class="bi bi-trash-fill text-danger me-1"></i>Files auto-deleted</span>
                    <span><i class="bi bi-cpu-fill text-info me-1"></i>GPT-4 powered</span>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="qcp-hero-card">
                    <div class="qcp-hero-card-header">
                        <span class="dot red"></span>
                        <span class="dot yellow"></span>
                        <span class="dot green"></span>
                        <span class="ms-2 small text-muted">QuickChatPDF · AI Processing</span>
                    </div>
                    <div class="qcp-hero-card-body">
                        <div class="qcp-chat-msg qcp-chat-user">
                            <i class="bi bi-person-circle me-2"></i>
                            What are the key findings in this research paper?
                        </div>
                        <div class="qcp-chat-msg qcp-chat-ai">
                            <i class="bi bi-stars me-2 text-warning"></i>
                            The paper identifies 3 key findings: (1) a 42% improvement in processing speed, (2) reduced
                            error rates by 18%, and (3) significant cost savings in deployment...
                        </div>
                        <div class="qcp-chat-msg qcp-chat-user">
                            <i class="bi bi-person-circle me-2"></i>
                            Generate a 5-question quiz from this
                        </div>
                        <div class="qcp-chat-msg qcp-chat-ai">
                            <i class="bi bi-stars me-2 text-warning"></i>
                            <span class="text-success">✓ Quiz generated!</span> 5 MCQ questions ready to export as
                            PDF...
                        </div>
                        <div class="qcp-typing mt-2">
                            <span></span><span></span><span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── FEATURES ───────────────────────────────────────────── -->
<section class="qcp-section" id="features">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="qcp-section-title">Everything You Need from a PDF</h2>
            <p class="qcp-section-sub">Four powerful AI tools, one simple upload</p>
        </div>
        <div class="row g-4">
            <?php
            $features = [
                ['bi-chat-dots-fill', 'purple', 'Chat with PDF', 'Ask any question and get instant, accurate answers sourced directly from your document.'],
                ['bi-file-text-fill', 'blue', 'Smart Summary', 'Get a concise, structured summary of any PDF in seconds. Perfect for research and reports.'],
                ['bi-patch-question-fill', 'teal', 'Q&A Generation', 'Auto-generate question and answer pairs from your PDF. Export with or without answers.'],
                ['bi-ui-checks-grid', 'orange', 'Quiz Generator', 'Create MCQ or True/False quizzes from any PDF. Export with or without options and answers.'],
            ];
            foreach ($features as [$icon, $color, $title, $desc]): ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="qcp-feature-card h-100">
                        <div class="qcp-feature-icon qcp-icon-<?= $color ?>">
                            <i class="bi <?= $icon ?>"></i>
                        </div>
                        <h5 class="mt-3 mb-2">
                            <?= $title ?>
                        </h5>
                        <p class="text-muted small mb-0">
                            <?= $desc ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── PRIVACY BANNER ────────────────────────────────────── -->
<section class="qcp-privacy-banner">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h3 class="fw-bold mb-2">
                    <i class="bi bi-shield-fill-check text-success me-2"></i>
                    We Never Store Your Documents
                </h3>
                <p class="mb-0 text-muted">
                    Your PDF is processed entirely in runtime memory — extracted, analysed by AI, results returned to
                    you, then <strong>permanently deleted</strong>. No document, OCR text, or AI output is ever written
                    to a database. Zero retention by design.
                </p>
            </div>
            <div class="col-lg-5">
                <div class="row g-3 text-center">
                    <?php
                    $privacy = [
                        ['bi-cloud-slash-fill', 'text-danger', 'No Cloud Storage'],
                        ['bi-database-slash', 'text-warning', 'No DB Documents'],
                        ['bi-eye-slash-fill', 'text-info', 'No AI History'],
                        ['bi-trash3-fill', 'text-success', 'Auto Deleted'],
                    ];
                    foreach ($privacy as [$icon, $color, $label]): ?>
                        <div class="col-6">
                            <div class="qcp-privacy-item">
                                <i class="bi <?= $icon ?> <?= $color ?> fs-4"></i>
                                <div class="small mt-1 fw-500">
                                    <?= $label ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── HOW IT WORKS ───────────────────────────────────────── -->
<section class="qcp-section qcp-section-dark" id="how-it-works">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="qcp-section-title">How It Works</h2>
            <p class="qcp-section-sub">From upload to results in seconds</p>
        </div>
        <div class="row g-4 justify-content-center">
            <?php
            $steps = [
                ['01', 'bi-cloud-upload-fill', 'purple', 'Upload Your PDF', 'Select and upload any PDF. We validate size and page count based on your plan instantly.'],
                ['02', 'bi-cpu-fill', 'blue', 'AI Processes It', 'Our system converts pages, runs OCR, and sends the full text to GPT-4 for analysis.'],
                ['03', 'bi-stars', 'teal', 'Get Your Results', 'Chat, summaries, Q&A, or quizzes are returned to you instantly. Export as PDF anytime.'],
                ['04', 'bi-trash-fill', 'red', 'Data Auto-Deleted', 'All temporary files, OCR text, and AI prompts are wiped immediately after processing.'],
            ];
            foreach ($steps as $i => [$num, $icon, $color, $title, $desc]): ?>
                <div class="col-sm-6 col-lg-3 text-center">
                    <div class="qcp-step-card">
                        <div class="qcp-step-num">
                            <?= $num ?>
                        </div>
                        <div class="qcp-feature-icon qcp-icon-<?= $color ?> mx-auto mt-3">
                            <i class="bi <?= $icon ?>"></i>
                        </div>
                        <h6 class="mt-3 fw-600">
                            <?= $title ?>
                        </h6>
                        <p class="small text-muted mb-0">
                            <?= $desc ?>
                        </p>
                    </div>
                    <?php if ($i < count($steps) - 1): ?>
                        <div class="qcp-step-arrow d-none d-lg-block">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── PRICING ────────────────────────────────────────────── -->
<section class="qcp-section" id="pricing">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="qcp-section-title">Simple, Transparent Pricing</h2>
            <p class="qcp-section-sub">Start free. Upgrade when you need more.</p>
        </div>
        <div class="row g-4 justify-content-center">
            <?php foreach (PLANS as $key => $plan):
                $limits = PDF_LIMITS[$key];
                $popular = $key === 'pro';
                ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="qcp-pricing-card h-100 <?= $popular ? 'qcp-pricing-popular' : '' ?>">
                        <?php if ($popular): ?>
                            <div class="qcp-popular-badge">Most Popular</div>
                        <?php endif; ?>
                        <div class="qcp-pricing-header">
                            <h5>
                                <?= $plan['name'] ?>
                            </h5>
                            <div class="qcp-pricing-price">
                                <?php if ($plan['price'] == 0): ?>
                                    <span class="qcp-price-amount">Free</span>
                                <?php else: ?>
                                    <span class="qcp-price-currency">$</span>
                                    <span class="qcp-price-amount">
                                        <?= number_format($plan['price'], 2) ?>
                                    </span>
                                    <span class="qcp-price-period">/mo</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <ul class="qcp-pricing-features list-unstyled">
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>
                                <?= $limits['pages'] ?> pages per PDF
                            </li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>
                                <?= $limits['size_mb'] ?>MB max PDF size
                            </li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>
                                <?= $plan['benefits']['pdfs_per_month'] ?> PDFs/month
                            </li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>
                                <?= $plan['benefits']['chat_messages'] ?> chat messages
                            </li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>
                                <?= $plan['benefits']['summaries'] ?> summaries
                            </li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>
                                <?= $plan['benefits']['quizzes'] ?> quizzes
                            </li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>
                                <?= $plan['monthly_credits'] ?> credits/month
                            </li>
                        </ul>
                        <a href="<?= APP_URL ?>/login"
                            class="btn <?= $popular ? 'btn-primary' : 'btn-outline-primary' ?> w-100 mt-auto">
                            <?= $plan['price'] == 0 ? 'Get Started Free' : 'Choose ' . $plan['name'] ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Top-up note -->
        <div class="text-center mt-4">
            <p class="text-muted small">
                <i class="bi bi-plus-circle-fill text-primary me-1"></i>
                Need more? Top-up credits anytime —
                50 credits for $1.99 · 150 for $4.99 · 400 for $9.99
            </p>
        </div>
    </div>
</section>

<!-- ── FAQ ────────────────────────────────────────────────── -->
<section class="qcp-section qcp-section-dark" id="faq">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="qcp-section-title">Frequently Asked Questions</h2>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion qcp-accordion" id="faqAccordion">
                    <?php
                    $faqs = [
                        [
                            'Are my PDFs stored anywhere?',
                            'No. Your PDF is processed entirely in runtime memory. Once AI returns your results, all files, OCR text, and prompts are permanently deleted. We store only your email, subscription, and usage counters.'
                        ],
                        [
                            'What AI model is used?',
                            'We use OpenAI GPT-4 for all AI features — chat, summaries, Q&A, and quiz generation — ensuring high-quality, accurate responses.'
                        ],
                        [
                            'Can I export my quiz or summary?',
                            'Yes! All results can be exported as a PDF. For quizzes you can choose to export with or without answer options, and with or without the answer key.'
                        ],
                        [
                            'How does OTP login work?',
                            'Enter your email address, we send a 6-digit OTP, you verify it — that\'s it. No password needed. Your session stays active for 30 days so you won\'t need to log in repeatedly.'
                        ],
                        [
                            'What payment methods are accepted?',
                            'We use PayPal for all subscriptions and top-up purchases, supporting credit cards, debit cards, and PayPal balance.'
                        ],
                        [
                            'Can I cancel my subscription anytime?',
                            'Yes. You can cancel your subscription at any time from your dashboard. Your plan remains active until the end of the billing period.'
                        ],
                    ];
                    foreach ($faqs as $i => [$q, $a]): ?>
                        <div class="accordion-item qcp-faq-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>">
                                    <?= $q ?>
                                </button>
                            </h2>
                            <div id="faq<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    <?= $a ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── CTA BANNER ─────────────────────────────────────────── -->
<section class="qcp-cta-section">
    <div class="container text-center">
        <h2 class="fw-800 mb-3">Ready to Chat with Your PDFs?</h2>
        <p class="text-muted mb-4">Start free. No credit card required. Your documents stay private.</p>
        <a href="<?= APP_URL ?>/login" class="btn btn-primary btn-lg px-5">
            <i class="bi bi-rocket-takeoff me-2"></i>Get Started Free
        </a>
    </div>
</section>

<!-- ── FOOTER ─────────────────────────────────────────────── -->
<footer class="qcp-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="qcp-logo-icon small">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>
                    <span class="fw-700 fs-5">Quick<strong>ChatPDF</strong></span>
                </div>
                <p class="text-muted small">AI-powered PDF chat, summary, Q&amp;A and quiz generator. Zero document
                    retention — private by design.</p>
            </div>
            <div class="col-6 col-lg-2 offset-lg-2">
                <h6 class="fw-600 mb-3">Product</h6>
                <ul class="list-unstyled qcp-footer-links">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="fw-600 mb-3">Account</h6>
                <ul class="list-unstyled qcp-footer-links">
                    <li><a href="<?= APP_URL ?>/login">Login</a></li>
                    <li><a href="<?= APP_URL ?>/login">Sign Up</a></li>
                    <li><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="fw-600 mb-3">Legal</h6>
                <ul class="list-unstyled qcp-footer-links">
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Refund Policy</a></li>
                </ul>
            </div>
        </div>
        <hr class="qcp-footer-divider" />
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <p class="text-muted small mb-0">&copy;
                <?= date('Y') ?> QuickChatPDF. All rights reserved.
            </p>
            <p class="text-muted small mb-0">
                <i class="bi bi-shield-fill-check text-success me-1"></i>Zero document retention · Private by design
            </p>
        </div>
    </div>
</footer>

<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/base.php';
