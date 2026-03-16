<?php
$seo = ['title' => '404 - Page Not Found | QuickChatPDF'];
ob_start();
?>
<section class="qcp-404-shell">
    <div class="qcp-404-card">
        <div class="qcp-404-copy">
            <span class="qcp-404-kicker">404 Error</span>
            <h1>That page is not here.</h1>
            <p>The link may be outdated, the route may require a different HTTP method, or the page may have moved. If you were checking a webhook URL in the browser, remember that many webhook routes only respond to <code>POST</code>.</p>
            <div class="qcp-404-actions">
                <a href="<?= APP_URL ?>/dashboard" class="btn btn-primary btn-lg px-4">
                    <i class="bi bi-grid me-2"></i>Go to Dashboard
                </a>
                <a href="<?= APP_URL ?>/" class="btn btn-outline-secondary btn-lg px-4">
                    <i class="bi bi-house-door me-2"></i>Go Home
                </a>
            </div>
        </div>
        <div class="qcp-404-art">
            <div class="qcp-404-code">404</div>
            <div class="qcp-404-orb orb-a"></div>
            <div class="qcp-404-orb orb-b"></div>
            <div class="qcp-404-panel">
                <div class="qcp-404-row">
                    <span>Status</span>
                    <strong>Page Not Found</strong>
                </div>
                <div class="qcp-404-row">
                    <span>Hint</span>
                    <strong>Check the route and request method</strong>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
ob_start();
?>
<style>
    .qcp-404-shell {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px;
        background:
            radial-gradient(circle at top left, rgba(59, 130, 246, .12), transparent 30%),
            radial-gradient(circle at bottom right, rgba(124, 58, 237, .12), transparent 34%),
            linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
    }

    .qcp-404-card {
        width: min(1080px, 100%);
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(320px, .9fr);
        gap: 24px;
        padding: 28px;
        border-radius: 30px;
        border: 1px solid rgba(148, 163, 184, .18);
        background: rgba(255, 255, 255, .88);
        box-shadow: 0 30px 80px rgba(15, 23, 42, .08);
        backdrop-filter: blur(8px);
    }

    .qcp-404-copy,
    .qcp-404-art {
        border-radius: 24px;
        background: #fff;
        border: 1px solid rgba(226, 232, 240, .9);
    }

    .qcp-404-copy {
        padding: 34px;
    }

    .qcp-404-kicker {
        display: inline-flex;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(79, 70, 229, .08);
        color: #4f46e5;
        font-size: .8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 18px;
    }

    .qcp-404-copy h1 {
        margin: 0 0 14px;
        font-family: var(--font-heading);
        font-size: clamp(2.2rem, 4vw, 4rem);
        font-weight: 800;
        line-height: .98;
        color: #0f172a;
    }

    .qcp-404-copy p {
        margin: 0;
        color: #475569;
        font-size: 1rem;
        line-height: 1.7;
        max-width: 640px;
    }

    .qcp-404-copy code {
        background: #f1f5f9;
        border-radius: 8px;
        padding: 2px 8px;
        color: #334155;
    }

    .qcp-404-actions {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        margin-top: 28px;
    }

    .qcp-404-art {
        position: relative;
        overflow: hidden;
        padding: 28px;
        min-height: 360px;
        display: flex;
        align-items: flex-end;
        justify-content: flex-start;
        background:
            radial-gradient(circle at 20% 20%, rgba(99, 102, 241, .18), transparent 26%),
            radial-gradient(circle at 80% 30%, rgba(6, 182, 212, .14), transparent 24%),
            linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .qcp-404-code {
        position: absolute;
        top: 24px;
        right: 26px;
        font-family: var(--font-heading);
        font-size: clamp(5rem, 12vw, 8rem);
        font-weight: 800;
        line-height: .9;
        color: rgba(99, 102, 241, .18);
        user-select: none;
    }

    .qcp-404-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(10px);
    }

    .qcp-404-orb.orb-a {
        width: 160px;
        height: 160px;
        top: -32px;
        left: -20px;
        background: rgba(59, 130, 246, .14);
    }

    .qcp-404-orb.orb-b {
        width: 180px;
        height: 180px;
        right: -40px;
        bottom: -40px;
        background: rgba(124, 58, 237, .14);
    }

    .qcp-404-panel {
        position: relative;
        z-index: 1;
        width: 100%;
        padding: 20px;
        border-radius: 22px;
        background: rgba(255, 255, 255, .92);
        border: 1px solid rgba(226, 232, 240, .95);
        box-shadow: 0 16px 36px rgba(15, 23, 42, .06);
    }

    .qcp-404-row {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: center;
        color: #475569;
        font-size: .92rem;
    }

    .qcp-404-row + .qcp-404-row {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #e2e8f0;
    }

    .qcp-404-row strong {
        color: #0f172a;
        font-weight: 700;
        text-align: right;
    }

    @media (max-width: 900px) {
        .qcp-404-card {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .qcp-404-shell {
            padding: 18px;
        }

        .qcp-404-copy,
        .qcp-404-art {
            padding: 22px;
        }

        .qcp-404-actions {
            flex-direction: column;
        }
    }
</style>
<?php
$extraHead = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
