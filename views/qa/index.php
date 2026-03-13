<?php
$seo = ['title' => 'Generate Q&A — QuickChatPDF', 'canonical' => '/qa'];
ob_start();
$appUrl = APP_URL;
?>

<nav class="navbar navbar-light qcp-navbar sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= APP_URL ?>/dashboard">
            <div class="qcp-logo-icon small"><i class="bi bi-arrow-left"></i></div>
            <span class="text-dark fw-600">Generate Q&amp;A</span>
        </a>
        <div class="d-flex align-items-center gap-2">
            <span class="qcp-plan-badge">
                <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
                <?= number_format($user['credits'] ?? 0) ?> credits
            </span>
            <a href="<?= APP_URL ?>/dashboard" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-grid me-1"></i>Dashboard
            </a>
        </div>
    </div>
</nav>

<div class="container py-5" style="max-width:860px">

    <div class="text-center mb-5">
        <div class="qcp-feature-icon qcp-icon-teal mx-auto mb-3" style="width:64px;height:64px;font-size:1.6rem">
            <i class="bi bi-patch-question-fill"></i>
        </div>
        <h2 class="fw-800 mb-2">Q&amp;A Generator</h2>
        <p class="text-muted">Auto-generate question and answer pairs from your document</p>
    </div>

    <!-- Controls -->
    <div class="qcp-panel-card mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <label class="qcp-label">Number of Q&amp;A pairs</label>
                <select id="qaCount" class="form-select">
                    <option value="5">5 pairs</option>
                    <option value="10">10 pairs</option>
                    <option value="15">15 pairs</option>
                    <option value="20">20 pairs</option>
                </select>
            </div>
            <div class="col-md-6 text-md-end">
                <button id="generateBtn" class="btn btn-primary btn-lg px-5 mt-3 mt-md-0">
                    <i class="bi bi-patch-question-fill me-2"></i>Generate
                </button>
            </div>
        </div>
    </div>

    <!-- Output -->
    <div id="qaOutput"></div>
    <div id="qaError" class="alert qcp-alert-danger d-none"></div>

</div>

<?php
$content = ob_get_clean();
$qaUrl = $appUrl . '/pdf/qa';
ob_start();
?>
<style>
    .qcp-qa-card {
        background: #fff;
        border: 1px solid var(--qcp-border);
        border-radius: 12px;
        padding: 20px 24px;
        margin-bottom: 14px;
        transition: box-shadow .2s;
    }

    .qcp-qa-card:hover {
        box-shadow: 0 4px 16px rgba(108, 71, 255, .08);
    }

    .qcp-qa-q {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 10px;
        font-size: .95rem;
    }

    .qcp-qa-q .q-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        background: var(--qcp-gradient);
        color: #fff;
        border-radius: 8px;
        font-size: .75rem;
        font-weight: 700;
        margin-right: 10px;
        flex-shrink: 0;
    }

    .qcp-qa-a {
        background: #f8f9fc;
        border-left: 3px solid var(--qcp-primary);
        padding: 10px 14px;
        border-radius: 0 8px 8px 0;
        font-size: .9rem;
        color: #475569;
    }

    .qcp-panel-card {
        background: #fff;
        border: 1px solid var(--qcp-border);
        border-radius: var(--qcp-radius);
        padding: 24px;
    }
</style>
<script>
    const QA_URL = '<?= $qaUrl ?>';

    document.getElementById('generateBtn').addEventListener('click', function () {
        const btn = this;
        const count = document.getElementById('qaCount').value;
        const error = document.getElementById('qaError');
        const out = document.getElementById('qaOutput');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generating...';
        error.classList.add('d-none');
        out.innerHTML = '';

        const body = 'count=' + count + '&_csrf=' + encodeURIComponent(document.querySelector('meta[name="csrf"]')?.content || '');

        fetch(QA_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body,
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-patch-question-fill me-2"></i>Regenerate';
                if (data.success) {
                    out.innerHTML = renderQA(data.data.qa);
                } else {
                    error.textContent = data.message;
                    error.classList.remove('d-none');
                }
            })
            .catch(function (err) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-patch-question-fill me-2"></i>Generate';
                error.textContent = 'Error: ' + err.message;
                error.classList.remove('d-none');
            });
    });

    function renderQA(qa) {
        return qa.map(function (item, i) {
            return '<div class="qcp-qa-card">' +
                '<div class="qcp-qa-q d-flex align-items-start">' +
                '<span class="q-num">' + (i + 1) + '</span>' + item.question +
                '</div>' +
                '<div class="qcp-qa-a">' + item.answer + '</div>' +
                '</div>';
        }).join('');
    }
</script>
<?php
$extraScripts = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
