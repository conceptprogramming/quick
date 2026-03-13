<?php
$seo = ['title' => '404 — Page Not Found · QuickChatPDF'];
ob_start();
?>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:40px;">
    <div>
        <div
            style="font-size:5rem;font-weight:800;background:linear-gradient(135deg,#7c5cfc,#c471ed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
            404</div>
        <h2 style="color:#f1f5f9;margin-bottom:10px;">Page Not Found</h2>
        <p style="color:#64748b;margin-bottom:24px;">The page you're looking for doesn't exist.</p>
        <a href="<?= APP_URL ?>/" class="btn btn-primary px-4">Go Home</a>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
