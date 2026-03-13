<?php
$seo = ['title' => 'Summarize PDF — QuickChatPDF', 'canonical' => '/summary'];
ob_start();
$appUrl = APP_URL;
$cost   = CREDIT_COSTS['summary'] ?? 2;
?>

<nav class="navbar navbar-light qcp-navbar sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= APP_URL ?>/dashboard">
            <div class="qcp-logo-icon small"><i class="bi bi-arrow-left"></i></div>
            <span class="text-dark fw-600">Summarize PDF</span>
        </a>
        <div class="d-flex align-items-center gap-2">
            <span class="qcp-plan-badge">
                <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
                <span id="navCredits"><?= number_format($user['credits'] ?? 0) ?></span> credits
            </span>
            <a href="<?= APP_URL ?>/dashboard" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-grid me-1"></i>Dashboard
            </a>
        </div>
    </div>
</nav>

<div class="container py-5" style="max-width:900px">

    <!-- Type Selection Screen -->
    <div id="typeScreen">
        <div class="qcp-type-header d-flex align-items-center gap-3 mb-5">
            <div class="qcp-feature-icon qcp-icon-purple" style="width:60px;height:60px;font-size:1.5rem;flex-shrink:0">
                <i class="bi bi-layers-fill"></i>
            </div>
            <div>
                <h2 class="fw-800 mb-1">Choose Summary Type</h2>
                <p class="text-muted mb-0">Select how you want your PDF to be summarized</p>
            </div>
        </div>

        <div class="row g-3">
            <?php
            $types = [
                ['brief',         'bi-lightning-charge-fill',      'Brief',         'Short 3–5 sentence overview'],
                ['detailed',      'bi-justify',                    'Detailed',      'In-depth section-by-section breakdown'],
                ['comprehensive', 'bi-file-earmark-text-fill',     'Comprehensive', 'Complete analysis with all key info'],
                ['keypoints',     'bi-list-ul',                    'Key Points',    'Bulleted list of main takeaways'],
                ['technical',     'bi-cpu-fill',                   'Technical',     'Focus on technical details and data'],
                ['simple',        'bi-person-fill',                'Simple',        'Plain language, easy to understand'],
                ['chapterwise',   'bi-book-fill',                  'Chapter-wise',  'Section by section breakdown'],
                ['abstract',      'bi-file-earmark-medical-fill',  'Abstract',      'Academic-style abstract format'],
            ];
            foreach ($types as [$val, $icon, $label, $desc]): ?>
                <div class="col-6 col-md-3">
                    <div class="qcp-type-card" data-type="<?= $val ?>"
                         onclick="selectType('<?= $val ?>', '<?= $label ?>')">
                        <div class="qcp-type-icon">
                            <i class="bi <?= $icon ?>"></i>
                        </div>
                        <div class="qcp-type-label"><?= $label ?></div>
                        <div class="qcp-type-desc"><?= $desc ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="text-center text-muted small mt-4">
            <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
            Each summary costs <strong><?= $cost ?> credits</strong>
        </p>
    </div>

    <!-- Output Screen -->
    <div id="outputScreen" style="display:none">

        <!-- Back + Header -->
        <div class="d-flex align-items-center gap-3 mb-4">
            <button onclick="goBack()" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Change Type
            </button>
            <div class="d-flex align-items-center gap-2">
                <span id="outputBadge" class="qcp-type-badge"></span>
                <span class="text-muted small">Summary</span>
            </div>
        </div>

        <!-- Loading -->
        <div id="loadingSection" class="text-center py-5">
            <div class="qcp-loading-dots mx-auto mb-3">
                <span></span><span></span><span></span>
            </div>
            <p class="fw-600 text-dark mb-1">
                Generating <span id="loadingType"></span> summary...
            </p>
            <p class="text-muted small">This may take a few seconds</p>
        </div>

        <!-- Result -->
        <div id="resultSection" style="display:none">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-700 mb-0 text-dark" id="resultTitle"></h6>
                <div class="d-flex gap-2">
                    <button id="copyBtn" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-clipboard me-1"></i>Copy
                    </button>
                    <button onclick="goBack()" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-arrow-clockwise me-1"></i>New Summary
                    </button>
                </div>
            </div>
            <div id="summaryText" class="qcp-summary-box"></div>
        </div>

        <!-- Error -->
        <div id="errorSection" class="alert qcp-alert-danger d-none"></div>

    </div>

</div>

<?php
$content    = ob_get_clean();
$summaryUrl = $appUrl . '/pdf/summary';
ob_start();
?>

<style>
    .qcp-type-card {
        background: #fff;
        border: 2px solid var(--qcp-border);
        border-radius: 16px;
        padding: 28px 16px 20px;
        text-align: center;
        cursor: pointer;
        transition: all .22s;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .qcp-type-card:hover {
        border-color: var(--qcp-primary);
        background: rgba(108, 71, 255, .04);
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(108, 71, 255, .1);
    }

    .qcp-type-card.selected {
        border-color: var(--qcp-primary);
        background: rgba(108, 71, 255, .06);
        box-shadow: 0 0 0 4px rgba(108, 71, 255, .12);
    }

    .qcp-type-icon {
        font-size: 2rem;
        color: #334155;
        margin-bottom: 12px;
        line-height: 1;
    }

    .qcp-type-card:hover .qcp-type-icon,
    .qcp-type-card.selected .qcp-type-icon {
        color: var(--qcp-primary);
    }

    .qcp-type-label {
        font-weight: 700;
        font-size: .95rem;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .qcp-type-desc {
        font-size: .75rem;
        color: #94a3b8;
        line-height: 1.4;
    }

    .qcp-type-badge {
        background: var(--qcp-gradient);
        color: #fff;
        border-radius: 8px;
        padding: 3px 12px;
        font-size: .78rem;
        font-weight: 600;
    }

    .qcp-summary-box {
        background: #fff;
        border: 1px solid var(--qcp-border);
        border-radius: 16px;
        padding: 32px;
        font-size: .95rem;
        color: #334155;
        line-height: 1.9;
        box-shadow: 0 4px 20px rgba(0, 0, 0, .04);
        min-height: 200px;
    }

    .qcp-summary-box ol,
    .qcp-summary-box ul {
        padding-left: 1.5rem;
        margin-bottom: 1rem;
    }

    .qcp-summary-box li {
        margin-bottom: 6px;
    }

    .qcp-summary-box strong {
        color: #0f172a;
    }

    .qcp-summary-box code {
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: .88rem;
        color: #7c3aed;
    }

    .qcp-loading-dots {
        display: flex;
        gap: 8px;
        justify-content: center;
        align-items: center;
        height: 40px;
    }

    .qcp-loading-dots span {
        width: 10px;
        height: 10px;
        background: var(--qcp-primary);
        border-radius: 50%;
        animation: loadBounce 1.2s infinite;
    }

    .qcp-loading-dots span:nth-child(2) { animation-delay: .2s; }
    .qcp-loading-dots span:nth-child(3) { animation-delay: .4s; }

    @keyframes loadBounce {
        0%, 60%, 100% { transform: translateY(0);    opacity: .4; }
        30%            { transform: translateY(-10px); opacity: 1;  }
    }
</style>

<script>
    const SUMMARY_URL = '<?= $summaryUrl ?>';
    let selectedType  = '';

    // ── Select summary type & trigger generation ──────────────
    function selectType(type, label) {
        selectedType = type;

        document.querySelectorAll('.qcp-type-card').forEach(function (c) {
            c.classList.remove('selected');
        });
        document.querySelector('[data-type="' + type + '"]').classList.add('selected');

        setTimeout(function () {
            document.getElementById('typeScreen').style.display    = 'none';
            document.getElementById('outputScreen').style.display  = 'block';
            document.getElementById('loadingSection').style.display = 'block';
            document.getElementById('resultSection').style.display = 'none';
            document.getElementById('errorSection').classList.add('d-none');
            document.getElementById('loadingType').textContent     = label;
            document.getElementById('outputBadge').textContent     = label;
            document.getElementById('resultTitle').textContent     = label + ' Summary';

            generateSummary(type);
        }, 180);
    }

    // ── Fetch summary from backend ────────────────────────────
    function generateSummary(type) {
        const csrf = document.querySelector('meta[name="csrf"]')?.content || '';
        const body = 'type=' + encodeURIComponent(type) + '&_csrf=' + encodeURIComponent(csrf);

        fetch(SUMMARY_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body,
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            document.getElementById('loadingSection').style.display = 'none';

            if (data.success) {
                // ✅ innerHTML renders the HTML from markdownToHtml()
                document.getElementById('summaryText').innerHTML = data.data.summary;
                document.getElementById('resultSection').style.display = 'block';

                if (data.data.credits_remaining !== undefined) {
                    document.getElementById('navCredits').textContent =
                        parseInt(data.data.credits_remaining).toLocaleString();
                }
            } else {
                const err = document.getElementById('errorSection');
                err.textContent = data.message;
                err.classList.remove('d-none');
            }
        })
        .catch(function (err) {
            document.getElementById('loadingSection').style.display = 'none';
            const e = document.getElementById('errorSection');
            e.textContent = 'Error: ' + err.message;
            e.classList.remove('d-none');
        });
    }

    // ── Go back to type selection ─────────────────────────────
    function goBack() {
        document.getElementById('outputScreen').style.display = 'none';
        document.getElementById('typeScreen').style.display   = 'block';
        document.querySelectorAll('.qcp-type-card').forEach(function (c) {
            c.classList.remove('selected');
        });
    }

    // ── Copy plain text to clipboard ──────────────────────────
    document.getElementById('copyBtn').addEventListener('click', function () {
        const plainText = document.getElementById('summaryText').innerText;
        navigator.clipboard.writeText(plainText).then(function () {
            document.getElementById('copyBtn').innerHTML = '<i class="bi bi-check me-1"></i>Copied!';
            setTimeout(function () {
                document.getElementById('copyBtn').innerHTML = '<i class="bi bi-clipboard me-1"></i>Copy';
            }, 2000);
        });
    });
</script>

<?php
$extraScripts = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
