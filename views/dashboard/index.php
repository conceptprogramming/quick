<?php
$seo = ['title' => 'Dashboard — QuickChatPDF', 'canonical' => '/dashboard'];
ob_start();
$appUrl = APP_URL;
$creditBalance = (int) ($user['credits'] ?? 0);
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
                <span class="js-credit-balance"><?= number_format($user['credits']) ?></span> credits
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
        <div class="qcp-upload-shell" id="uploadArea">
            <div class="qcp-upload-orb"></div>
            <div class="qcp-upload-panel" id="dropZone">
                <div class="qcp-upload-icon mx-auto mb-4">
                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                </div>
                <span class="qcp-upload-kicker">Zero-retention document processing</span>
                <h4 class="fw-800 mb-2">Drop your PDF here</h4>
                <p class="text-muted mb-4">Drag and drop a PDF or browse from your device. Max <?= $limits['pages'] ?> pages and <?= $limits['size_mb'] ?>MB on your <?= ucfirst($user['plan']) ?> plan.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <label for="pdfInput" class="btn btn-primary btn-lg px-5">
                        <i class="bi bi-folder2-open me-2"></i>Select PDF
                    </label>
                    <span class="qcp-upload-chip"><i class="bi bi-stars me-2"></i>OCR + AI extraction</span>
                </div>
                <input type="file" id="pdfInput" accept="application/pdf" class="d-none" />
                <div class="qcp-upload-footnote">
                    <span><i class="bi bi-shield-fill-check text-success me-1"></i>Processed instantly</span>
                    <span><i class="bi bi-trash3-fill text-danger me-1"></i>Deleted after use</span>
                </div>
            </div>
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
        width: 84px;
        height: 84px;
        background: var(--qcp-gradient);
        border-radius: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: #fff;
        box-shadow: 0 20px 40px rgba(108, 71, 255, .18);
    }

    .qcp-upload-shell {
        position: relative;
        overflow: hidden;
        border-radius: 28px;
        background:
            radial-gradient(circle at top left, rgba(108, 71, 255, .14), transparent 38%),
            radial-gradient(circle at top right, rgba(20, 184, 166, .12), transparent 32%),
            linear-gradient(180deg, #ffffff, #f8f9fc);
        padding: 18px;
    }

    .qcp-upload-orb {
        position: absolute;
        width: 240px;
        height: 240px;
        right: -70px;
        top: -80px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(108, 71, 255, .16), rgba(108, 71, 255, 0));
        filter: blur(8px);
        pointer-events: none;
    }

    .qcp-upload-panel {
        position: relative;
        z-index: 1;
        border: 2px dashed rgba(108, 71, 255, .18);
        border-radius: 22px;
        padding: 56px 28px;
        text-align: center;
        background: rgba(255,255,255,.72);
        backdrop-filter: blur(8px);
        transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease, background .22s ease;
    }

    .qcp-upload-panel.drag-over {
        transform: translateY(-2px) scale(1.01);
        border-color: var(--qcp-primary);
        box-shadow: 0 18px 40px rgba(108, 71, 255, .16);
        background: rgba(255,255,255,.94);
    }

    .qcp-upload-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(108, 71, 255, .08);
        color: var(--qcp-primary);
        font-size: .78rem;
        font-weight: 700;
        margin-bottom: 16px;
    }

    .qcp-upload-chip {
        display: inline-flex;
        align-items: center;
        padding: 0 16px;
        min-height: 48px;
        border-radius: 14px;
        border: 1px solid var(--qcp-border);
        background: #fff;
        color: #334155;
        font-size: .92rem;
        font-weight: 600;
    }

    .qcp-upload-footnote {
        display: flex;
        justify-content: center;
        gap: 18px;
        flex-wrap: wrap;
        margin-top: 22px;
        font-size: .8rem;
        color: #64748b;
        font-weight: 500;
    }

    .qcp-processing-stage {
        max-width: 540px;
        margin: 0 auto;
    }

    .qcp-processing-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(108, 71, 255, .08);
        color: var(--qcp-primary);
        font-size: .8rem;
        font-weight: 700;
        margin-bottom: 18px;
    }

    .qcp-processing-line {
        width: 100%;
        height: 10px;
        border-radius: 999px;
        background: #e9edf5;
        overflow: hidden;
        margin: 18px 0 14px;
    }

    .qcp-processing-line span {
        display: block;
        width: 42%;
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #6c47ff, #14b8a6);
        animation: qcpLoad 1.3s infinite ease-in-out;
    }

    .qcp-upload-file-meta {
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 14px;
    }

    .qcp-upload-file-meta span {
        padding: 6px 12px;
        border-radius: 999px;
        background: #fff;
        border: 1px solid var(--qcp-border);
        color: #475569;
        font-size: .8rem;
        font-weight: 600;
    }

    @keyframes qcpLoad {
        0% { transform: translateX(-120%); }
        100% { transform: translateX(260%); }
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

    @media (max-width: 640px) {
        .qcp-upload-panel {
            padding: 40px 20px;
        }

        .qcp-upload-footnote {
            gap: 10px;
        }
    }

</style>
<script>
    const UPLOAD_URL = '<?= $uploadUrl ?>';
    const PROCESS_URL = '<?= $processUrl ?>';
    const pdfInput = document.getElementById('pdfInput');
    const dropZone = document.getElementById('dropZone');

    pdfInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        startUpload(file);
    });

    ['dragenter', 'dragover'].forEach(function (eventName) {
        dropZone.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('drag-over');
        });
    });

    ['dragleave', 'dragend', 'drop'].forEach(function (eventName) {
        dropZone.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('drag-over');
        });
    });

    dropZone.addEventListener('drop', function (e) {
        const file = e.dataTransfer?.files?.[0];
        if (!file) return;
        pdfInput.files = e.dataTransfer.files;
        startUpload(file);
    });

    function startUpload(file) {
        const csrf = document.querySelector('meta[name="csrf"]')?.content || '';
        const formData = new FormData();
        formData.append('pdf', file);
        formData.append('_csrf', csrf);

        setUploadArea(
            '<div class="qcp-processing-stage">' +
                '<div class="qcp-processing-pill"><i class="bi bi-cloud-arrow-up-fill"></i> Uploading PDF</div>' +
                '<h4 class="fw-800 text-dark mb-2">Sending your document to the runtime pipeline</h4>' +
                '<p class="text-muted mb-0">We are validating the file and preparing secure temporary storage.</p>' +
                '<div class="qcp-upload-file-meta">' +
                    '<span><i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i>' + escapeHtml(file.name) + '</span>' +
                    '<span><i class="bi bi-hdd-stack-fill text-primary me-1"></i>' + formatBytes(file.size) + '</span>' +
                '</div>' +
                '<div class="qcp-processing-line"><span></span></div>' +
                '<p class="text-muted small mb-0">No permanent storage. File is removed after processing.</p>' +
            '</div>'
        );

        fetch(UPLOAD_URL, { method: 'POST', body: formData })
            .then(function (r) {
                return r.text().then(function (text) {
                    try {
                        return JSON.parse(text);
                    } catch (err) {
                        throw new Error(text.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim() || 'Upload endpoint returned invalid JSON.');
                    }
                });
            })
            .then(function (data) {
                if (!data.success) { showError(data.message); return; }
                showProcessing(data.data.pages);
            })
            .catch(function (err) { showError('Upload error: ' + err.message); });
    }

    function showProcessing(pages) {
        setUploadArea(
            '<div class="qcp-processing-stage">' +
                '<div class="qcp-processing-pill"><i class="bi bi-magic"></i> Processing ' + pages + ' page(s)</div>' +
                '<h4 class="fw-800 text-dark mb-2">Transforming your PDF into AI-ready content</h4>' +
                '<p class="text-muted mb-0">Running OCR, extracting structure, and preparing your workspace.</p>' +
                '<div class="qcp-processing-line"><span></span></div>' +
                '<div class="qcp-upload-file-meta">' +
                    '<span><i class="bi bi-images text-primary me-1"></i>Page rendering</span>' +
                    '<span><i class="bi bi-file-text-fill text-success me-1"></i>Text extraction</span>' +
                    '<span><i class="bi bi-robot text-purple me-1"></i>AI context prep</span>' +
                '</div>' +
            '</div>'
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
            '<div class="qcp-processing-stage">' +
            '<div class="qcp-processing-pill"><i class="bi bi-check-circle-fill text-success"></i> PDF Ready</div>' +
            '<h4 class="fw-800 text-dark mb-2">Your document is ready for chat, summaries, and quizzes</h4>' +
            '<p class="text-muted mb-3">Processed ' + pages + ' page(s) successfully. Pick a tool below to continue.</p>' +
            '<div class="qcp-upload-file-meta">' +
                '<span><i class="bi bi-check2-circle text-success me-1"></i>OCR complete</span>' +
                '<span><i class="bi bi-lightning-charge-fill text-warning me-1"></i>AI-ready</span>' +
            '</div>' +
            '<div class="mt-4">' +
            '<button onclick="resetUpload()" class="btn btn-outline-secondary btn-sm">' +
            '<i class="bi bi-arrow-counterclockwise me-1"></i>Upload another PDF</button>' +
            '</div>' +
            '</div>'
        );
        document.getElementById('toolCards').style.display = 'block';
    }

    function showError(msg) {
        setUploadArea(
            '<div class="qcp-processing-stage">' +
            '<div class="qcp-processing-pill" style="background:rgba(239,68,68,.08);color:#dc2626"><i class="bi bi-exclamation-triangle-fill"></i> Upload failed</div>' +
            '<h4 class="fw-800 text-dark mb-2">We could not process that PDF</h4>' +
            '<p class="text-danger fw-600 mb-3">' + escapeHtml(msg) + '</p>' +
            '<button onclick="resetUpload()" class="btn btn-outline-primary">Try Again</button>' +
            '</div>'
        );
    }

    function setUploadArea(html) {
        document.getElementById('uploadArea').innerHTML = '<div class="py-3 text-center">' + html + '</div>';
    }

    function resetUpload() { location.reload(); }

    function formatBytes(bytes) {
        if (!bytes) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB'];
        const exp = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
        const value = bytes / Math.pow(1024, exp);
        return value.toFixed(value >= 10 || exp === 0 ? 0 : 1) + ' ' + units[exp];
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
<?php
$extraScripts = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
