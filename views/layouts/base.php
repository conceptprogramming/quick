<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- SEO -->
    <title>
        <?= $seo['title'] ?? 'QuickChatPDF — AI PDF Chat, Summary & Quiz Generator' ?>
    </title>
    <meta name="description"
        content="<?= $seo['description'] ?? 'Upload any PDF and instantly chat with it, generate summaries, Q&A, and quizzes using AI. Zero document retention — your files are never stored.' ?>" />
    <meta name="keywords"
        content="<?= $seo['keywords'] ?? 'chat with pdf, pdf ai, pdf summary, pdf quiz generator, ai pdf tool' ?>" />
    <meta name="robots" content="index, follow" />
    <meta name="theme-color" content="#6c47ff" />
    <link rel="canonical" href="<?= APP_URL . ($seo['canonical'] ?? '') ?>" />
    <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/public/favicon.svg" />
    <link rel="shortcut icon" href="<?= APP_URL ?>/public/favicon.svg" />

    <!-- Open Graph -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= APP_URL ?>" />
    <meta property="og:site_name" content="QuickChatPDF" />
    <meta property="og:title" content="<?= $seo['title'] ?? 'QuickChatPDF' ?>" />
    <meta property="og:description"
        content="<?= $seo['description'] ?? 'AI-powered PDF chat, summaries, Q&A and quiz generator.' ?>" />
    <meta property="og:image" content="<?= APP_URL ?>/public/assets/og-image.png" />

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= $seo['title'] ?? 'QuickChatPDF' ?>" />
    <meta name="twitter:description"
        content="<?= $seo['description'] ?? 'AI-powered PDF processing. Zero retention.' ?>" />
    <meta name="csrf" content="<?= \Middleware\CSRFMiddleware::generate() ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap"
        rel="stylesheet" />

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />

    <!-- Custom CSS -->
    <link href="<?= APP_URL ?>/public/css/style.css" rel="stylesheet" />

    <?= $extraHead ?? '' ?>
</head>

<body>
    <?php if (isset($creditBalance)): ?>
        <div id="qcp-credit-data" data-credits="<?= (int) $creditBalance ?>" style="display:none"></div>
    <?php endif; ?>
    <!-- Flash Message Data (from PHP session) -->
    <?php
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    if ($flash): ?>
        <div id="qcp-flash-data" data-message="<?= htmlspecialchars($flash['message']) ?>"
            data-type="<?= htmlspecialchars($flash['type']) ?>" style="display:none">
        </div>
    <?php endif; ?>


    <?= $content ?? '' ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="<?= APP_URL ?>/public/js/app.js"></script>
    <script>
        (function () {
            const dataEl = document.getElementById('qcp-credit-data');
            const storageKey = 'qcp_credit_balance';

            function formatCredits(value) {
                return Math.max(0, Number(value || 0)).toLocaleString();
            }

            function renderCredits(value) {
                document.querySelectorAll('.js-credit-balance').forEach(function (el) {
                    el.textContent = formatCredits(value);
                });
            }

            function readStoredCredits() {
                const raw = window.localStorage.getItem(storageKey);
                return raw === null ? null : Number(raw);
            }

            function writeStoredCredits(value) {
                window.localStorage.setItem(storageKey, String(Math.max(0, Number(value || 0))));
                renderCredits(value);
            }

            const serverCredits = dataEl ? Number(dataEl.dataset.credits || 0) : null;
            const storedCredits = readStoredCredits();
            const initialCredits = serverCredits !== null ? serverCredits : (storedCredits ?? 0);

            if (serverCredits !== null) {
                writeStoredCredits(serverCredits);
            } else {
                renderCredits(initialCredits);
            }

            window.qcpCredits = {
                get: function () {
                    return readStoredCredits() ?? initialCredits;
                },
                set: function (value) {
                    writeStoredCredits(value);
                },
                consume: function (amount) {
                    writeStoredCredits((readStoredCredits() ?? initialCredits) - Number(amount || 0));
                },
                add: function (amount) {
                    writeStoredCredits((readStoredCredits() ?? initialCredits) + Number(amount || 0));
                },
            };
        })();
    </script>

    <?= $extraScripts ?? '' ?>
</body>

</html>
