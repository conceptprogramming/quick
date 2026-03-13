<?php
$seo = [
    'title' => 'QuickChatPDF — AI PDF Chat, Summary & Quiz Generator',
    'description' => 'Upload any PDF and instantly chat with it, generate summaries, and take AI-powered quizzes. Zero document retention — your files are never stored.',
    'keywords' => 'chat with pdf, pdf ai, pdf summary, pdf quiz generator, ai pdf tool',
    'canonical' => '/',
];
ob_start();
$appUrl = APP_URL;
?>

<!-- ── Navbar ── -->
<nav class="navbar navbar-expand-lg qcp-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $appUrl ?>/">
            <div class="qcp-logo-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
            <span>Quick<strong>ChatPDF</strong></span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto gap-1">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#how-it-works">How it Works</a></li>
                <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
            </ul>
            <div class="d-flex gap-2 mt-3 mt-lg-0">
                <a href="<?= $appUrl ?>/login" class="btn btn-outline-primary btn-sm px-4">Log In</a>
                <a href="<?= $appUrl ?>/login" class="btn btn-primary btn-sm px-4">Get Started Free</a>
            </div>
        </div>
    </div>
</nav>

<!-- ── Hero ── -->
<section class="qcp-hero">
    <div class="container">
        <div class="row align-items-center g-5">

            <!-- Left -->
            <div class="col-lg-6">
                <div class="qcp-badge mb-4">
                    <i class="bi bi-stars me-2"></i> Powered by GPT-4o
                </div>
                <h1 class="qcp-hero-title mb-4">
                    Chat with any PDF<br>
                    <span class="qcp-gradient-text">in seconds</span>
                </h1>
                <p class="qcp-hero-sub mb-5">
                    Upload your PDF and instantly chat with it, generate summaries, and take AI-powered quizzes.
                    Zero data retention — your files are processed and deleted immediately.
                </p>
                <div class="d-flex flex-wrap gap-3 mb-5">
                    <a href="<?= $appUrl ?>/login" class="btn btn-primary btn-lg px-5">
                        <i class="bi bi-rocket-takeoff me-2"></i>Start for Free
                    </a>
                    <a href="#how-it-works" class="btn btn-outline-primary btn-lg px-4">
                        See How it Works
                    </a>
                </div>
                <div class="qcp-trust-badges d-flex flex-wrap gap-4">
                    <span><i class="bi bi-shield-fill-check text-success me-2"></i>No signup credit card</span>
                    <span><i class="bi bi-lightning-charge-fill text-warning me-2"></i>20 free credits</span>
                    <span><i class="bi bi-trash3-fill text-primary me-2"></i>Files never stored</span>
                </div>
            </div>

            <!-- Right — Live Chat Demo Card -->
            <div class="col-lg-6">
                <div class="qcp-hero-card">
                    <div class="qcp-hero-card-header">
                        <span class="dot red"></span>
                        <span class="dot yellow"></span>
                        <span class="dot green"></span>
                        <span class="ms-2 small text-muted fw-500" style="font-size:.78rem">
                            <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i>
                            research-paper.pdf — 8 pages
                        </span>
                    </div>
                    <div class="qcp-hero-card-body">
                        <div class="qcp-chat-msg qcp-chat-user">
                            <i class="bi bi-person-fill me-2 mt-1 flex-shrink-0" style="color:#94a3b8"></i>
                            What are the key findings of this research paper?
                        </div>
                        <div class="qcp-chat-msg qcp-chat-ai">
                            <i class="bi bi-robot me-2 mt-1 flex-shrink-0" style="color:var(--qcp-primary)"></i>
                            <span>The paper identifies <strong>3 key findings</strong>: (1) AI-driven models outperform
                                traditional methods by 34%, (2) data preprocessing reduces error rates significantly,
                                and (3) the proposed architecture scales linearly with dataset size...</span>
                        </div>
                        <div class="qcp-chat-msg qcp-chat-user">
                            <i class="bi bi-person-fill me-2 mt-1 flex-shrink-0" style="color:#94a3b8"></i>
                            Summarize the methodology section
                        </div>
                        <div class="qcp-chat-msg qcp-chat-ai d-flex align-items-center">
                            <i class="bi bi-robot me-2 flex-shrink-0" style="color:var(--qcp-primary)"></i>
                            <div class="qcp-typing"><span></span><span></span><span></span></div>
                        </div>

                        <!-- Input bar -->
                        <div class="d-flex gap-2 mt-3">
                            <input type="text" class="form-control form-control-sm"
                                style="background:#f8f9fc;border:1.5px solid #e2e8f0;border-radius:10px;font-size:.85rem"
                                placeholder="Ask anything about your PDF..." disabled />
                            <button class="btn btn-primary btn-sm px-3" disabled>
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Floating stats -->
                <div class="d-flex gap-3 mt-3 justify-content-center">
                    <div class="qcp-hero-stat">
                        <span class="qcp-hero-stat-num">50K+</span>
                        <span class="qcp-hero-stat-label">PDFs Processed</span>
                    </div>
                    <div class="qcp-hero-stat">
                        <span class="qcp-hero-stat-num">4.9★</span>
                        <span class="qcp-hero-stat-label">User Rating</span>
                    </div>
                    <div class="qcp-hero-stat">
                        <span class="qcp-hero-stat-num">&lt;3s</span>
                        <span class="qcp-hero-stat-label">Avg Response</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ── Privacy Banner ── -->
<section class="qcp-privacy-banner">
    <div class="container">
        <div class="text-center mb-4">
            <span class="qcp-badge"><i class="bi bi-shield-lock-fill me-2"></i>Privacy First — Always</span>
        </div>
        <div class="row g-3 justify-content-center">
            <?php
            $privacy = [
                ['bi-cloud-slash-fill', 'Never Stored', 'Your PDF is processed in memory and deleted immediately after use.'],
                ['bi-incognito', 'No Training Data', 'We never use your documents to train AI models.'],
                ['bi-lock-fill', 'Encrypted Transit', 'All uploads use TLS encryption end-to-end.'],
                ['bi-eye-slash-fill', 'Private by Design', 'Nobody at QuickChatPDF can read your documents.'],
            ];
            foreach ($privacy as [$icon, $title, $desc]): ?>
                <div class="col-6 col-md-3">
                    <div class="qcp-privacy-item text-center h-100">
                        <i class="bi <?= $icon ?> fs-4 mb-2" style="color:var(--qcp-primary)"></i>
                        <div class="fw-700 small mb-1"><?= $title ?></div>
                        <div class="text-muted" style="font-size:.8rem"><?= $desc ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Features ── -->
<section class="qcp-section" id="features">
    <div class="container">
        <div class="text-center mb-5">
            <span class="qcp-badge mb-3"><i class="bi bi-grid-fill me-2"></i>Features</span>
            <h2 class="qcp-section-title">Everything you need<br>to understand any PDF</h2>
            <p class="qcp-section-sub">Three powerful AI tools in one place</p>
        </div>

        <div class="row g-4">
            <?php
            $features = [
                [
                    'bi-chat-dots-fill',
                    'purple',
                    'Chat with PDF',
                    'Ask any question and get instant, accurate answers pulled directly from your document. Like having an expert read it for you.',
                    ['Natural conversation', 'Source-grounded answers', 'Multi-turn memory'],
                ],
                [
                    'bi-file-text-fill',
                    'blue',
                    'Smart Summaries',
                    'Choose from 8 summary styles — Brief, Detailed, Technical, Chapter-wise and more. Get exactly the depth you need.',
                    ['8 summary types', 'Structured output', 'Copy in one click'],
                ],
                [
                    'bi-ui-checks-grid',
                    'orange',
                    'Interactive Quiz',
                    'Auto-generate MCQ or True/False quizzes with a live countdown timer, instant scoring and a full review at the end.',
                    ['Timer per question', 'Live score tracking', 'Full answer review'],
                ],
            ];
            foreach ($features as [$icon, $color, $title, $desc, $bullets]): ?>
                <div class="col-md-4">
                    <div class="qcp-feature-card h-100">
                        <div class="qcp-feature-icon qcp-icon-<?= $color ?> mb-3">
                            <i class="bi <?= $icon ?>"></i>
                        </div>
                        <h5 class="fw-700 mb-2"><?= $title ?></h5>
                        <p class="text-muted small mb-3"><?= $desc ?></p>
                        <ul class="list-unstyled mt-auto mb-0">
                            <?php foreach ($bullets as $b): ?>
                                <li class="small mb-1">
                                    <i class="bi bi-check-circle-fill me-2" style="color:var(--qcp-primary)"></i><?= $b ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── How it Works ── -->
<section class="qcp-section-dark" id="how-it-works">
    <div class="container">
        <div class="text-center mb-5">
            <span class="qcp-badge mb-3"><i class="bi bi-play-circle-fill me-2"></i>How it Works</span>
            <h2 class="qcp-section-title">From PDF to insights<br>in 3 simple steps</h2>
        </div>

        <div class="row g-4 align-items-stretch">
            <?php
            $steps = [
                ['01', 'bi-cloud-upload-fill', 'purple', 'Upload your PDF', 'Drag & drop or browse to upload. Supports research papers, contracts, textbooks, reports — any PDF up to your plan limit.'],
                ['02', 'bi-cpu-fill', 'blue', 'AI Processes it', 'Our AI instantly extracts, indexes, and understands your document. No waiting — it\'s ready in seconds.'],
                ['03', 'bi-stars', 'orange', 'Get Instant Value', 'Chat, summarize, or quiz yourself. Every answer is grounded in your document — no hallucinations.'],
            ];
            foreach ($steps as [$num, $icon, $color, $title, $desc]): ?>
                <div class="col-md-4">
                    <div class="qcp-step-card h-100 text-center px-4 py-5">
                        <div class="qcp-step-num mb-3"><?= $num ?></div>
                        <div class="qcp-feature-icon qcp-icon-<?= $color ?> mx-auto mb-3">
                            <i class="bi <?= $icon ?>"></i>
                        </div>
                        <h5 class="fw-700 mb-2"><?= $title ?></h5>
                        <p class="text-muted small mb-0"><?= $desc ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Use Cases ── -->
<section class="qcp-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="qcp-badge mb-3"><i class="bi bi-people-fill me-2"></i>Who uses it</span>
            <h2 class="qcp-section-title">Built for everyone<br>who reads documents</h2>
        </div>
        <div class="row g-3">
            <?php
            $usecases = [
                ['bi-mortarboard-fill', 'blue', 'Students', 'Summarize textbooks, generate quizzes for exam prep, and chat with research papers.'],
                ['bi-briefcase-fill', 'purple', 'Professionals', 'Extract key clauses from contracts, summarize reports, and get quick answers.'],
                ['bi-search', 'teal', 'Researchers', 'Chat with papers, extract methodology, and compare findings across documents.'],
                ['bi-building-fill', 'orange', 'Business Teams', 'Onboard faster by chatting with policy docs, SOPs, and product manuals.'],
            ];
            foreach ($usecases as [$icon, $color, $title, $desc]): ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="qcp-feature-card h-100 text-center py-4">
                        <div class="qcp-feature-icon qcp-icon-<?= $color ?> mx-auto mb-3">
                            <i class="bi <?= $icon ?>"></i>
                        </div>
                        <h6 class="fw-700 mb-2"><?= $title ?></h6>
                        <p class="text-muted small mb-0"><?= $desc ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Pricing ── -->
<section class="qcp-section-dark" id="pricing">
    <div class="container">
        <div class="text-center mb-5">
            <span class="qcp-badge mb-3"><i class="bi bi-tag-fill me-2"></i>Pricing</span>
            <h2 class="qcp-section-title">Simple, transparent pricing</h2>
            <p class="qcp-section-sub">Start free. Upgrade when you need more.</p>
        </div>

        <div class="row g-4 justify-content-center align-items-stretch">
            <?php
            $plans = [
                [
                    'Free',
                    '$0',
                    '/mo',
                    false,
                    ['20 credits/month', '3 PDFs per month', '10 chats', '2 summaries', '1 quiz', '5-page PDFs'],
                    'Get Started Free',
                    false,
                ],
                [
                    'Basic',
                    '$4.99',
                    '/mo',
                    false,
                    ['100 credits/month', '20 PDFs per month', '100 chats', '10 summaries', '10 quizzes', '10-page PDFs'],
                    'Get Basic',
                    false,
                ],
                [
                    'Pro',
                    '$14.99',
                    '/mo',
                    true,
                    ['300 credits/month', '100 PDFs per month', '500 chats', '50 summaries', '50 quizzes', '15-page PDFs'],
                    'Get Pro',
                    true,
                ],
                [
                    'Professional',
                    '$24.99',
                    '/mo',
                    false,
                    ['1000 credits/month', '500 PDFs per month', '2000 chats', '200 summaries', '200 quizzes', '25-page PDFs'],
                    'Get Professional',
                    false,
                ],
            ];
            foreach ($plans as [$name, $price, $period, $popular, $features, $cta, $highlight]): ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="qcp-pricing-card h-100 <?= $popular ? 'qcp-pricing-popular' : '' ?>">
                        <?php if ($popular): ?>
                            <div class="qcp-popular-badge">⭐ Most Popular</div>
                        <?php endif; ?>
                        <div class="qcp-pricing-header">
                            <h5><?= $name ?></h5>
                            <div class="d-flex align-items-end gap-1 mb-1">
                                <span class="qcp-price-currency">$</span>
                                <span class="qcp-price-amount"><?= ltrim($price, '$') ?></span>
                                <span class="qcp-price-period ms-1"><?= $period ?></span>
                            </div>
                        </div>
                        <ul class="qcp-pricing-features list-unstyled flex-grow-1 mb-4">
                            <?php foreach ($features as $f): ?>
                                <li>
                                    <i class="bi bi-check2 me-2" style="color:var(--qcp-primary)"></i><?= $f ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="<?= $appUrl ?>/login"
                            class="btn <?= $highlight ? 'btn-primary' : 'btn-outline-primary' ?> w-100">
                            <?= $cta ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="text-center text-muted small mt-4">
            <i class="bi bi-shield-check me-1 text-success"></i>
            No credit card required to start · Cancel anytime · Secure payments via PayPal
        </p>
    </div>
</section>

<!-- ── FAQ ── -->
<section class="qcp-section" id="faq">
    <div class="container" style="max-width:760px">
        <div class="text-center mb-5">
            <span class="qcp-badge mb-3"><i class="bi bi-question-circle-fill me-2"></i>FAQ</span>
            <h2 class="qcp-section-title">Frequently asked questions</h2>
        </div>

        <div class="accordion" id="faqAccordion">
            <?php
            $faqs = [
                [
                    'Are my PDFs stored on your servers?',
                    'No. Your PDFs are processed entirely in memory and permanently deleted immediately after your session ends. We never write your document to disk or store it in any database.'
                ],
                [
                    'What types of PDFs are supported?',
                    'Any standard PDF file — research papers, contracts, textbooks, reports, manuals, invoices, and more. Scanned PDFs with text layers are also supported.'
                ],
                [
                    'What is a credit?',
                    'Credits are consumed per action: 1 credit per chat message, 3 credits per summary, 2 credits per quiz. Free accounts start with 20 credits per month. You can upgrade or buy top-up packs.'
                ],
                [
                    'How accurate are the AI answers?',
                    'All answers are grounded exclusively in your document text — we instruct the AI to never answer from general knowledge. This means answers are highly accurate to your specific document.'
                ],
                [
                    'Can I use this on mobile?',
                    'Yes — QuickChatPDF is fully responsive and works on all modern smartphones and tablets.'
                ],
                [
                    'What happens when I run out of credits?',
                    'You\'ll see a clear message when credits run low. You can upgrade your plan or purchase a top-up pack instantly to continue without interruption.'
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
                        <div class="accordion-body"><?= $a ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── CTA ── -->
<section class="qcp-cta-section">
    <div class="container text-center">
        <div class="qcp-badge mx-auto mb-4">
            <i class="bi bi-rocket-takeoff-fill me-2"></i> Free to start — no card needed
        </div>
        <h2 class="qcp-section-title mb-3">
            Start understanding your PDFs<br>
            <span class="qcp-gradient-text">in under 30 seconds</span>
        </h2>
        <p class="qcp-section-sub mx-auto mb-5">
            Join thousands of students, researchers and professionals who use QuickChatPDF every day.
        </p>
        <a href="<?= $appUrl ?>/login" class="btn btn-primary btn-lg px-5 me-3">
            <i class="bi bi-rocket-takeoff me-2"></i>Get Started Free
        </a>
        <a href="#features" class="btn btn-outline-primary btn-lg px-4">
            Learn More
        </a>
    </div>
</section>

<!-- ── Footer ── -->
<footer class="qcp-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 mb-3">
                <a class="d-flex align-items-center gap-2 mb-3 text-decoration-none" href="<?= $appUrl ?>/">
                    <div class="qcp-logo-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
                    <span class="fw-800" style="font-family:var(--font-heading);color:#0f172a">
                        Quick<strong>ChatPDF</strong>
                    </span>
                </a>
                <p class="text-muted small mb-3">
                    AI-powered PDF chat, summaries and quizzes. Zero data retention. Built for people who need fast
                    answers from documents.
                </p>
                <div class="d-flex gap-2">
                    <a href="#" class="qcp-social-btn"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="qcp-social-btn"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>

            <div class="col-6 col-lg-2 offset-lg-2">
                <h6 class="fw-700 mb-3 small text-uppercase" style="letter-spacing:.06em;color:#94a3b8">Product</h6>
                <ul class="qcp-footer-links list-unstyled">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <li><a href="#how-it-works">How it Works</a></li>
                    <li><a href="<?= $appUrl ?>/login">Get Started</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h6 class="fw-700 mb-3 small text-uppercase" style="letter-spacing:.06em;color:#94a3b8">Legal</h6>
                <ul class="qcp-footer-links list-unstyled">
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Cookie Policy</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h6 class="fw-700 mb-3 small text-uppercase" style="letter-spacing:.06em;color:#94a3b8">Support</h6>
                <ul class="qcp-footer-links list-unstyled">
                    <li><a href="#faq">FAQ</a></li>
                    <li><a href="mailto:hello@quickchatpdf.com">Contact</a></li>
                </ul>
            </div>
        </div>

        <hr class="qcp-footer-divider" />

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <p class="text-muted small mb-0">
                © <?= date('Y') ?> QuickChatPDF. All rights reserved.
            </p>
            <div class="d-flex align-items-center gap-3">
                <span class="d-flex align-items-center gap-1 text-muted" style="font-size:.8rem">
                    <i class="bi bi-shield-fill-check text-success"></i> Privacy First
                </span>
                <span class="d-flex align-items-center gap-1 text-muted" style="font-size:.8rem">
                    <i class="bi bi-lock-fill text-primary"></i> TLS Encrypted
                </span>
                <span class="d-flex align-items-center gap-1 text-muted" style="font-size:.8rem">
                    <i class="bi bi-stars text-warning"></i> GPT-4o Powered
                </span>
            </div>
        </div>
    </div>
</footer>

<?php
$content = ob_get_clean();
ob_start();
?>
<style>
    /* ── Hero Stats ── */
    .qcp-hero-stat {
        background: #fff;
        border: 1px solid var(--qcp-border);
        border-radius: 12px;
        padding: 10px 20px;
        text-align: center;
        flex: 1;
    }

    .qcp-hero-stat-num {
        display: block;
        font-family: var(--font-heading);
        font-size: 1.1rem;
        font-weight: 800;
        background: var(--qcp-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .qcp-hero-stat-label {
        display: block;
        font-size: .72rem;
        color: var(--qcp-muted);
        font-weight: 500;
    }

    /* ── Social Buttons ── */
    .qcp-social-btn {
        width: 34px;
        height: 34px;
        background: #f1f4f9;
        border: 1px solid var(--qcp-border);
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: .9rem;
        transition: all .2s;
        text-decoration: none;
    }

    .qcp-social-btn:hover {
        background: var(--qcp-primary);
        border-color: var(--qcp-primary);
        color: #fff;
    }

    /* ── Smooth scroll ── */
    html {
        scroll-behavior: smooth;
    }
</style>
<?php
$extraScripts = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
