<?php
$seo = ['title' => 'Dashboard — QuickChatPDF', 'canonical' => '/dashboard'];
ob_start();
$appUrl = APP_URL;
?>

<nav class="navbar navbar-light qcp-navbar sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= APP_URL ?>/">
            <div class="qcp-logo-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
            <span class="text-dark">Quick<strong>ChatPDF</strong></span>
        </a>
        <div class="d-flex align-items-center gap-3">
            <span class="qcp-plan-badge">
                <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
                <?= number_format($user['credits']) ?> credits
            </span>

            <!-- Profile Dropdown -->
            <div class="dropdown">
                <button class="qcp-profile-nav-btn dropdown-toggle" data-bs-toggle="dropdown">
                    <div class="qcp-nav-avatar">
                        <?= strtoupper(substr($user['email'], 0, 1)) ?>
                    </div>
                    <span class="d-none d-md-inline small fw-600 text-dark">
                        <?= htmlspecialchars(explode('@', $user['email'])[0]) ?>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end qcp-profile-dropdown shadow-sm">
                    <!-- User Info -->
                    <li>
                        <div class="px-3 py-2 border-bottom">
                            <div class="fw-700 small text-dark"><?= htmlspecialchars($user['email']) ?></div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="qcp-plan-badge" style="font-size:.7rem;padding:2px 8px">
                                    <?= ucfirst($user['plan']) ?> Plan
                                </span>
                            </div>
                        </div>
                    </li>
                    <li>
                        <a class="dropdown-item qcp-dropdown-item" href="<?= APP_URL ?>/profile">
                            <i class="bi bi-person-fill me-2"></i>My Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item qcp-dropdown-item" href="<?= APP_URL ?>/plans">
                            <i class="bi bi-rocket-takeoff-fill me-2"></i>Upgrade Plan
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider my-1">
                    </li>
                    <li>
                        <a class="dropdown-item qcp-dropdown-item text-danger" href="<?= APP_URL ?>/logout">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</nav>


<div class="container py-5">

    <!-- Welcome -->
    <div class="mb-5">
        <h1 class="qcp-section-title mb-1">Welcome back 👋</h1>
        <p class="text-muted"><?= htmlspecialchars($user['email']) ?> · <a href="<?= APP_URL ?>/plans"
                class="text-primary fw-500">Upgrade Plan</a></p>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-5">
        <?php
        
$stats = [
    ['bi-lightning-charge-fill', 'text-warning', 'Credits Left',     number_format($user['credits'])],
    ['bi-file-earmark-pdf-fill', 'text-danger',  'PDFs This Month',  ($usage['pdfs_uploaded'] ?? 0) . ' / ' . $effectiveLimits['pdfs_per_month']],
    ['bi-chat-dots-fill',        'text-primary',  'Chats Used',       ($usage['chat_messages'] ?? 0) . ' / ' . $effectiveLimits['chat_messages']],
    ['bi-layers-fill',           'text-purple',   'Summaries Made',   ($usage['summaries']     ?? 0) . ' / ' . $effectiveLimits['summaries']],
    ['bi-ui-checks-grid',        'text-success',  'Quizzes Made',     ($usage['quizzes']       ?? 0) . ' / ' . $effectiveLimits['quizzes']],
];


        foreach ($stats as [$icon, $color, $label, $value]): ?>
         <div class="col-6 col-lg">
                <div class="qcp-stat-card">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi <?= $icon ?> <?= $color ?>"></i>
                        <span class="small text-muted"><?= $label ?></span>
                    </div>
                    <div class="qcp-stat-value"><?= $value ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Upload Card -->
    <div class="qcp-upload-card mb-5">
        <div class="text-center py-5" id="uploadArea">
            <div class="qcp-upload-icon mx-auto mb-4">
                <i class="bi bi-cloud-upload-fill"></i>
            </div>
            <h4 class="fw-800 mb-2">Upload a PDF to Get Started</h4>
            <p class="text-muted mb-4">Max <?= $limits['pages'] ?> pages · <?= $limits['size_mb'] ?>MB on your
                <?= ucfirst($user['plan']) ?> plan
            </p>
            <label for="pdfInput" class="btn btn-primary btn-lg px-5">
                <i class="bi bi-file-earmark-arrow-up me-2"></i>Choose PDF
            </label>
            <input type="file" id="pdfInput" accept="application/pdf" class="d-none" />
            <p class="text-muted small mt-4 mb-0">
                <i class="bi bi-shield-fill-check text-success me-1"></i>
                Never stored — processed instantly and deleted
            </p>
        </div>
    </div>

    <!-- Tool Cards (shown after PDF ready) -->
    <div id="toolCards" style="display:none">
        <h5 class="fw-700 mb-4 text-dark">Choose a Tool</h5>
        <div class="row g-4">
            <?php
            $tools = [
                ['bi-chat-dots-fill', 'purple', 'Chat with PDF', 'Ask questions and get instant AI answers from your document.', '/chat'],
                ['bi-file-text-fill', 'blue', 'Summarize', 'Get a clean structured summary of your entire PDF.', '/summary'],
                ['bi-ui-checks-grid', 'orange', 'Take Quiz', 'Play an interactive quiz with timer and live scoring.', '/quiz'],
            ];

            foreach ($tools as [$icon, $color, $title, $desc, $url]): ?>
                <div class="col-md-4">

                    <a href="<?= APP_URL . $url ?>" class="text-decoration-none">
                        <div class="qcp-tool-card h-100">
                            <div class="qcp-feature-icon qcp-icon-<?= $color ?>">
                                <i class="bi <?= $icon ?>"></i>
                            </div>
                            <h6 class="mt-3 mb-1 fw-700 text-dark"><?= $title ?></h6>
                            <p class="text-muted small mb-0"><?= $desc ?></p>
                            <div class="mt-3">
                                <span class="qcp-tool-link">Open <i class="bi bi-arrow-right ms-1"></i></span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
$uploadUrl = $appUrl . '/pdf/upload';
$processUrl = $appUrl . '/pdf/process';
ob_start();
?>
<style>
    .qcp-upload-icon {
        width: 72px;
        height: 72px;
        background: var(--qcp-gradient);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: #fff;
    }

    .qcp-tool-card {
        background: #fff;
        border: 1px solid var(--qcp-border);
        border-radius: var(--qcp-radius);
        padding: 28px 24px;
        cursor: pointer;
        transition: all .25s;
        display: flex;
        flex-direction: column;
    }

    .qcp-tool-card:hover {
        border-color: var(--qcp-primary);
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(108, 71, 255, .1);
    }

    .qcp-tool-link {
        font-size: .82rem;
        font-weight: 600;
        color: var(--qcp-primary);
    }

    .qcp-profile-nav-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        border: 1px solid var(--qcp-border);
        border-radius: 10px;
        padding: 5px 12px 5px 6px;
        cursor: pointer;
        transition: all .2s;
    }

    .qcp-profile-nav-btn:hover {
        border-color: var(--qcp-primary);
        background: rgba(108, 71, 255, .03);
    }

    .qcp-profile-nav-btn::after {
        margin-left: 4px;
        color: #94a3b8;
    }

    .qcp-nav-avatar {
        width: 30px;
        height: 30px;
        background: var(--qcp-gradient);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-heading);
        font-size: .85rem;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
    }

    .qcp-profile-dropdown {
        border: 1px solid var(--qcp-border) !important;
        border-radius: 14px !important;
        padding: 6px !important;
        min-width: 220px;
    }

    .qcp-dropdown-item {
        border-radius: 8px;
        font-size: .88rem;
        font-weight: 500;
        padding: 9px 12px;
        color: #334155;
        transition: background .15s;
    }

    .qcp-dropdown-item:hover {
        background: rgba(108, 71, 255, .06);
        color: var(--qcp-primary);
    }

    .qcp-dropdown-item.text-danger:hover {
        background: rgba(239, 68, 68, .06);
        color: #dc2626 !important;
    }
    .text-purple { color: var(--qcp-primary) !important; }

</style>
<script>
    const UPLOAD_URL = '<?= $uploadUrl ?>';
    const PROCESS_URL = '<?= $processUrl ?>';

    document.getElementById('pdfInput').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        const csrf = document.querySelector('meta[name="csrf"]')?.content || '';
        const formData = new FormData();
        formData.append('pdf', file);
        formData.append('_csrf', csrf);

        setUploadArea(
            '<div class="spinner-border text-primary mb-3" role="status"></div>' +
            '<p class="text-muted mb-0">Uploading <strong>' + file.name + '</strong>...</p>'
        );

        fetch(UPLOAD_URL, { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) { showError(data.message); return; }
                showProcessing(data.data.pages);
            })
            .catch(function (err) { showError('Upload error: ' + err.message); });
    });

    function showProcessing(pages) {
        setUploadArea(
            '<div class="spinner-border text-primary mb-3" role="status"></div>' +
            '<p class="fw-600 mb-1 text-dark">Processing ' + pages + ' page(s)...</p>' +
            '<p class="text-muted small mb-0">Extracting text with OCR — this may take a moment</p>'
        );
        const csrf = document.querySelector('meta[name="csrf"]')?.content || '';
        fetch(PROCESS_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: '_csrf=' + encodeURIComponent(csrf),
        })
            .then(function (r) {
                if (!r.ok) return r.text().then(function (t) { throw new Error(t); });
                return r.json();
            })
            .then(function (data) {
                if (!data.success) { showError(data.message); return; }
                showReady(pages);
            })
            .catch(function (err) { showError('Process error: ' + err.message); });
    }

    function showReady(pages) {
        setUploadArea(
            '<div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">' +
            '<div class="qcp-feature-icon qcp-icon-teal"><i class="bi bi-check-circle-fill"></i></div>' +
            '<div class="text-start">' +
            '<p class="fw-700 mb-0 text-success">PDF Ready — ' + pages + ' page(s) processed</p>' +
            '<p class="text-muted small mb-0">Select a tool below to get started</p>' +
            '</div>' +
            '<button onclick="resetUpload()" class="btn btn-outline-secondary btn-sm">' +
            '<i class="bi bi-arrow-counterclockwise me-1"></i>New PDF</button>' +
            '</div>'
        );
        document.getElementById('toolCards').style.display = 'block';
    }

    function showError(msg) {
        setUploadArea(
            '<div class="qcp-feature-icon qcp-icon-red mx-auto mb-3"><i class="bi bi-exclamation-circle-fill"></i></div>' +
            '<p class="text-danger fw-600 mb-3">' + msg + '</p>' +
            '<button onclick="resetUpload()" class="btn btn-outline-primary">Try Again</button>'
        );
    }

    function setUploadArea(html) {
        document.getElementById('uploadArea').innerHTML = '<div class="py-4 text-center">' + html + '</div>';
    }

    function resetUpload() { location.reload(); }
</script>
<?php
$extraScripts = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
