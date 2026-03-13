<?php
use Middleware\CSRFMiddleware;

$seo = [
    'title' => 'Verify OTP — QuickChatPDF',
    'canonical' => '/verify',
];

$email = $_SESSION['otp_email'] ?? '';
$error = $_SESSION['auth_error'] ?? null;
unset($_SESSION['auth_error']);

ob_start();
?>

<div class="qcp-auth-wrap">

    <!-- Left Panel — same branding -->
    <div class="qcp-auth-left d-none d-lg-flex">
        <div class="qcp-auth-left-inner">

            <a href="<?= APP_URL ?>/" class="d-inline-flex align-items-center gap-2 text-decoration-none mb-5">
                <div class="qcp-logo-icon" style="width:40px;height:40px;font-size:1.1rem">
                    <i class="bi bi-file-earmark-text-fill"></i>
                </div>
                <span class="fw-800 fs-5" style="color:#fff;font-family:var(--font-heading)">
                    Quick<span style="opacity:.8">ChatPDF</span>
                </span>
            </a>

            <!-- Email Sent Illustration -->
            <div class="qcp-otp-illustration">
                <div class="qcp-otp-envelope">
                    <i class="bi bi-envelope-open-fill"></i>
                </div>
                <div class="qcp-otp-dots">
                    <span></span><span></span><span></span>
                </div>
                <div class="qcp-otp-phone">
                    <i class="bi bi-phone-fill"></i>
                </div>
            </div>

            <h2 class="qcp-auth-left-title mt-4">
                Check your<br>inbox
            </h2>
            <p class="qcp-auth-left-sub">
                We sent a secure 6-digit code to your email. It expires in 10 minutes.
            </p>

            <!-- Steps -->
            <div class="d-flex flex-column gap-3 mt-4">
                <?php
                $steps = [
                    ['1', 'Open your email app'],
                    ['2', 'Find the email from QuickChatPDF'],
                    ['3', 'Enter the 6-digit code'],
                ];
                foreach ($steps as [$num, $text]): ?>
                    <div class="d-flex align-items-center gap-3">
                        <div class="qcp-otp-step-num"><?= $num ?></div>
                        <span style="font-size:.88rem;color:rgba(255,255,255,.8)"><?= $text ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-auto pt-5">
                <p style="font-size:.8rem;color:rgba(255,255,255,.5);margin:0">
                    <i class="bi bi-shield-fill-check me-1" style="color:#4ade80"></i>
                    OTP login — no password ever stored
                </p>
            </div>

        </div>
    </div>

    <!-- Right Panel -->
    <div class="qcp-auth-right">

        <!-- Mobile Logo -->
        <div class="text-center mb-5 d-lg-none">
            <a href="<?= APP_URL ?>/" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                <div class="qcp-logo-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
                <span class="fw-800 fs-5 text-dark" style="font-family:var(--font-heading)">
                    Quick<strong>ChatPDF</strong>
                </span>
            </a>
        </div>

        <div class="qcp-auth-form-wrap">

            <!-- Header -->
            <div class="text-center mb-4">
                <div class="qcp-otp-icon-ring mx-auto mb-3">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h1 class="qcp-auth-title">Enter your code</h1>
                <p class="qcp-auth-sub">
                    We sent a 6-digit code to<br>
                    <strong class="text-dark"><?= htmlspecialchars($email) ?></strong>
                </p>
            </div>

            <!-- Error -->
            <?php if ($error): ?>
                <div class="qcp-alert-danger d-flex align-items-center gap-2 p-3 mb-4">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- OTP Form -->
            <form action="<?= APP_URL ?>/verify" method="POST" id="otpForm">
                <?= CSRFMiddleware::field() ?>
                <input type="hidden" name="otp" id="otpHidden" />

                <!-- OTP Boxes -->
                <div class="qcp-otp-inputs mb-2">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <input type="text" class="qcp-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric"
                            autocomplete="one-time-code" data-index="<?= $i ?>" />
                    <?php endfor; ?>
                </div>

                <!-- Timer -->
                <div class="text-center mb-4">
                    <span class="small text-muted">
                        <i class="bi bi-clock me-1"></i>
                        Code expires in <span id="countdown" class="fw-600 text-dark">10:00</span>
                    </span>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg qcp-submit-btn" id="verifyBtn">
                    <span class="btn-text">
                        <i class="bi bi-check-circle-fill me-2"></i>Verify & Login
                    </span>
                    <span class="btn-loading d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span>Verifying...
                    </span>
                </button>
            </form>

            <!-- Resend -->
            <div class="text-center mt-4">
                <p class="text-muted small mb-2">Didn't receive the code?</p>
                <a href="<?= APP_URL ?>/login" class="qcp-resend-link">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Send a new code
                </a>
            </div>

            <!-- Back -->
            <div class="text-center mt-3">
                <a href="<?= APP_URL ?>/login" class="text-muted small">
                    <i class="bi bi-arrow-left me-1"></i>Wrong email? Go back
                </a>
            </div>

        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
ob_start();
?>
<style>
    body {
        overflow: hidden;
    }

    .qcp-auth-wrap {
        display: flex;
        min-height: 100vh;
        background: #fff;
    }

    /* ── Left Panel ── */
    .qcp-auth-left {
        width: 420px;
        flex-shrink: 0;
        background: var(--qcp-gradient);
        padding: 48px 40px;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    .qcp-auth-left::before {
        content: '';
        position: absolute;
        top: -80px;
        right: -80px;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, .06);
        border-radius: 50%;
    }

    .qcp-auth-left::after {
        content: '';
        position: absolute;
        bottom: -60px;
        left: -60px;
        width: 240px;
        height: 240px;
        background: rgba(255, 255, 255, .04);
        border-radius: 50%;
    }

    .qcp-auth-left-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .qcp-auth-left-title {
        font-size: 1.8rem;
        font-weight: 800;
        color: #fff;
        line-height: 1.25;
        font-family: var(--font-heading);
        margin: 0;
    }

    .qcp-auth-left-sub {
        color: rgba(255, 255, 255, .7);
        font-size: .9rem;
        margin-top: 12px;
        line-height: 1.6;
    }

    /* ── OTP Illustration ── */
    .qcp-otp-illustration {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-top: 40px;
    }

    .qcp-otp-envelope,
    .qcp-otp-phone {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, .15);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        color: #fff;
    }

    .qcp-otp-dots {
        display: flex;
        gap: 5px;
        flex: 1;
        justify-content: center;
    }

    .qcp-otp-dots span {
        width: 8px;
        height: 8px;
        background: rgba(255, 255, 255, .4);
        border-radius: 50%;
        animation: dotPulse 1.5s infinite;
    }

    .qcp-otp-dots span:nth-child(2) {
        animation-delay: .3s;
    }

    .qcp-otp-dots span:nth-child(3) {
        animation-delay: .6s;
    }

    @keyframes dotPulse {

        0%,
        100% {
            opacity: .3;
            transform: scale(.8);
        }

        50% {
            opacity: 1;
            transform: scale(1.2);
        }
    }

    .qcp-otp-step-num {
        width: 28px;
        height: 28px;
        background: rgba(255, 255, 255, .2);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
    }

    /* ── Right Panel ── */
    .qcp-auth-right {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 48px 32px;
        background: #fff;
        overflow-y: auto;
    }

    .qcp-auth-form-wrap {
        width: 100%;
        max-width: 400px;
    }

    /* ── OTP Icon Ring ── */
    .qcp-otp-icon-ring {
        width: 68px;
        height: 68px;
        background: rgba(108, 71, 255, .08);
        border: 2px solid rgba(108, 71, 255, .15);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: var(--qcp-primary);
    }

    /* ── OTP Boxes ── */
    .qcp-otp-inputs {
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .qcp-otp-box {
        width: 52px;
        height: 60px;
        background: #f8f9fc;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        text-align: center;
        font-size: 1.5rem;
        font-weight: 700;
        color: #0f172a;
        font-family: var(--font-heading);
        transition: all .2s;
        outline: none;
    }

    .qcp-otp-box:focus {
        border-color: var(--qcp-primary);
        box-shadow: 0 0 0 4px rgba(108, 71, 255, .1);
        background: #fff;
        transform: translateY(-2px);
    }

    .qcp-otp-box.filled {
        border-color: var(--qcp-primary);
        background: rgba(108, 71, 255, .04);
    }

    /* ── Submit ── */
    .qcp-submit-btn {
        height: 50px;
        font-size: .95rem;
    }

    /* ── Mobile ── */
    @media (max-width: 991px) {
        body {
            overflow: auto;
        }

        .qcp-auth-wrap {
            flex-direction: column;
        }

        .qcp-auth-right {
            padding: 40px 20px;
        }

        .qcp-otp-box {
            width: 44px;
            height: 54px;
            font-size: 1.3rem;
        }
    }
</style>
<script>
    const boxes      = document.querySelectorAll('.qcp-otp-box');
const otpHidden  = document.getElementById('otpHidden');
const otpForm    = document.getElementById('otpForm');
const verifyBtn  = document.getElementById('verifyBtn');
let   submitting = false;

function updateHidden() {
    otpHidden.value = Array.from(boxes).map(b => b.value).join('');
}

function tryAutoSubmit() {
    const val = Array.from(boxes).map(b => b.value).join('');
    console.log('[tryAutoSubmit] val=', val, 'submitting=', submitting);
    if (val.length === 6 && !submitting) {
        submitting = true;
        updateHidden();
        console.log('[tryAutoSubmit] FIRING SUBMIT');
        setTimeout(() => otpForm.requestSubmit(), 150);
    }
}

boxes.forEach((box, idx) => {
    box.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(-1);
        this.classList.toggle('filled', !!this.value);
        if (this.value && idx < boxes.length - 1) boxes[idx + 1].focus();
        updateHidden();
        console.log('[input] box', idx, 'value=', this.value, 'hidden=', otpHidden.value);
        tryAutoSubmit();
    });

    box.addEventListener('keydown', function (e) {
        if (e.key === 'Backspace' && !this.value && idx > 0) {
            boxes[idx - 1].value = '';
            boxes[idx - 1].classList.remove('filled');
            boxes[idx - 1].focus();
            updateHidden();
        }
    });

    box.addEventListener('paste', function (e) {
        e.preventDefault();
        const paste = (e.clipboardData || window.clipboardData)
            .getData('text').replace(/\D/g, '').slice(0, 6);
        console.log('[paste] pasted=', paste);
        paste.split('').forEach((char, i) => {
            if (boxes[i]) {
                boxes[i].value = char;
                boxes[i].classList.add('filled');
            }
        });
        updateHidden();
        const last = boxes[Math.min(paste.length, 5)];
        if (last) last.focus();
        tryAutoSubmit();
    });
});

otpForm.addEventListener('submit', function (e) {
    console.log('[submit] fired — submitting=', submitting, 'otp=', otpHidden.value, 'btn.disabled=', verifyBtn.disabled);
    if (submitting && verifyBtn.disabled) {
        console.log('[submit] BLOCKED duplicate');
        e.preventDefault();
        return;
    }
    const otp = otpHidden.value;
    if (otp.length !== 6) {
        e.preventDefault();
        submitting = false;
        boxes[0].focus();
        return;
    }
    submitting = true;
    verifyBtn.querySelector('.btn-text').classList.add('d-none');
    verifyBtn.querySelector('.btn-loading').classList.remove('d-none');
    verifyBtn.disabled = true;
    console.log('[submit] ALLOWED — sending OTP', otp);
});

// ── Countdown ─────────────────────────────────────────────────
let seconds = 600;
const cdEl  = document.getElementById('countdown');
const timer = setInterval(() => {
    seconds--;
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    cdEl.textContent = m + ':' + (s < 10 ? '0' : '') + s;
    if (seconds <= 60) cdEl.style.color = '#ef4444';
    if (seconds <= 0) {
        clearInterval(timer);
        cdEl.textContent = 'Expired';
        verifyBtn.disabled = true;
    }
}, 1000);

boxes[0].focus();

</script>
<?php
$extraScripts = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
