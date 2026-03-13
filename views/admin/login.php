<?php
$error = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_error']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Login — QuickChatPDF</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --qcp-primary: #6c47ff;
            --qcp-gradient: linear-gradient(135deg, #6c47ff 0%, #a855f7 100%);
        }

        body {
            background: #f8f9fc;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-login-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 44px 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .08);
        }

        .admin-icon {
            width: 64px;
            height: 64px;
            background: var(--qcp-gradient);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: #fff;
            margin: 0 auto 20px;
        }

        .admin-input {
            background: #f8f9fc !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 10px !important;
            padding: 12px 16px !important;
            font-size: .95rem;
            transition: all .2s;
        }

        .admin-input:focus {
            border-color: var(--qcp-primary) !important;
            box-shadow: 0 0 0 3px rgba(108, 71, 255, .1) !important;
            background: #fff !important;
            outline: none !important;
        }

        .btn-admin {
            background: var(--qcp-gradient);
            border: none;
            color: #fff;
            border-radius: 10px;
            height: 48px;
            font-weight: 600;
            font-size: .95rem;
            transition: opacity .2s;
        }

        .btn-admin:hover {
            opacity: .9;
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="admin-login-card">
        <div class="admin-icon"><i class="bi bi-shield-lock-fill"></i></div>
        <h4 class="text-center fw-800 mb-1" style="font-family:'Plus Jakarta Sans',sans-serif">Admin Access</h4>
        <p class="text-center text-muted small mb-4">QuickChatPDF Control Panel</p>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:.85rem;border-radius:10px">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= APP_URL ?>/admin/login" method="POST">
            <div class="mb-3">
                <label class="form-label small fw-500 text-secondary">Admin Password</label>
                <input type="password" name="password" class="form-control admin-input"
                    placeholder="Enter admin password" required autofocus />
            </div>
            <button type="submit" class="btn btn-admin w-100">
                <i class="bi bi-shield-fill-check me-2"></i>Access Admin Panel
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="<?= APP_URL ?>/" class="text-muted small">
                <i class="bi bi-arrow-left me-1"></i>Back to site
            </a>
        </div>
    </div>
</body>

</html>