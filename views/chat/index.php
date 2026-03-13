<?php
$seo = ['title' => 'Chat with PDF — QuickChatPDF', 'canonical' => '/chat'];
ob_start();
$appUrl = APP_URL;
$creditBalance = (int) ($user['credits'] ?? 0);
?>

<nav class="navbar navbar-light qcp-navbar sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= APP_URL ?>/dashboard">
            <div class="qcp-logo-icon small"><i class="bi bi-arrow-left"></i></div>
            <span class="text-dark fw-600">Chat with PDF</span>
        </a>
        <div class="d-flex align-items-center gap-2">
            <span class="qcp-plan-badge">
                <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
                <span class="js-credit-balance"><?= number_format($user['credits'] ?? 0) ?></span> credits
            </span>
            <a href="<?= APP_URL ?>/dashboard" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-grid me-1"></i>Dashboard
            </a>
        </div>
    </div>
</nav>

<div class="qcp-chat-page">
    <div class="qcp-chat-container">

        <!-- Header -->
        <div class="qcp-chat-header">
            <div class="d-flex align-items-center gap-3">
                <div class="qcp-feature-icon qcp-icon-purple">
                    <i class="bi bi-chat-dots-fill"></i>
                </div>
                <div>
                    <h5 class="fw-700 mb-0">AI Chat</h5>
                    <p class="text-muted small mb-0">Ask anything about your document</p>
                </div>
            </div>
            <span class="qcp-status-dot">
                <span class="dot-green"></span> PDF Loaded
            </span>
        </div>

        <!-- Messages -->
        <div class="qcp-messages" id="chatMessages">
            <div class="qcp-msg qcp-msg-ai">
                <div class="qcp-msg-avatar"><i class="bi bi-robot"></i></div>
                <div class="qcp-msg-wrapper">
                    <div class="qcp-msg-bubble">
                        Hello! Your PDF has been processed. Ask me anything about it — I'll answer based on the document content.
                    </div>
                    <div class="qcp-msg-actions">
                        <button class="qcp-copy-btn" onclick="copyMsg(this)" title="Copy">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input -->
        <div class="qcp-chat-input-bar">
            <div class="qcp-chat-input-wrap">
                <input type="text" id="chatInput" class="qcp-chat-input"
                    placeholder="Ask a question about your PDF..."
                    maxlength="200" />
                <button id="chatSend" class="qcp-send-btn">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <p class="text-muted small mb-0">
                    Each message costs <?= CREDIT_COSTS['chat'] ?? 1 ?> credit
                </p>
                <span id="charCounter" class="small text-muted">0/200</span>
            </div>
        </div>

    </div>
</div>

<?php
$content = ob_get_clean();
$chatUrl = $appUrl . '/pdf/chat';
ob_start();
?>

<style>
    body { overflow: hidden; }

    .qcp-chat-page {
        height: calc(100vh - 65px);
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fc;
        padding: 20px;
    }

    .qcp-chat-container {
        width: 100%;
        max-width: 800px;
        height: 100%;
        max-height: 700px;
        background: #fff;
        border: 1px solid var(--qcp-border);
        border-radius: 20px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .08);
    }

    .qcp-chat-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--qcp-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
    }

    .qcp-status-dot {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: .8rem;
        color: #64748b;
        font-weight: 500;
    }

    .dot-green {
        width: 8px;
        height: 8px;
        background: #22c55e;
        border-radius: 50%;
        display: inline-block;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1;  }
        50%       { opacity: .4; }
    }

    .qcp-messages {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        background: #f8f9fc;
    }

    .qcp-msg {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .qcp-msg-ai   { flex-direction: row; }
    .qcp-msg-user { flex-direction: row-reverse; }

    .qcp-msg-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
        flex-shrink: 0;
    }

    .qcp-msg-ai .qcp-msg-avatar {
        background: rgba(108, 71, 255, .1);
        color: var(--qcp-primary);
    }

    .qcp-msg-user .qcp-msg-avatar {
        background: var(--qcp-gradient);
        color: #fff;
    }

    /* Wrapper holds bubble + copy button */
    .qcp-msg-wrapper {
        display: flex;
        flex-direction: column;
        gap: 4px;
        max-width: 70%;
    }

    .qcp-msg-bubble {
        padding: 12px 16px;
        border-radius: 16px;
        font-size: .9rem;
        line-height: 1.6;
    }

    .qcp-msg-ai .qcp-msg-bubble {
        background: #fff;
        border: 1px solid var(--qcp-border);
        color: #1e293b;
        border-top-left-radius: 4px;
    }

    .qcp-msg-user .qcp-msg-bubble {
        background: var(--qcp-gradient);
        color: #fff;
        border-top-right-radius: 4px;
    }

   

    .qcp-msg-actions {
    display: flex;
    align-items: center;
}

    .qcp-copy-btn {
        background: none;
        border: 1px solid var(--qcp-border);
        border-radius: 7px;
        padding: 3px 8px;
        font-size: .75rem;
        color: #94a3b8;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 4px;
        transition: color .15s, border-color .15s, background .15s;
    }

    .qcp-copy-btn:hover {
        color: var(--qcp-primary);
        border-color: var(--qcp-primary);
        background: rgba(108, 71, 255, .05);
    }

    .qcp-copy-btn.copied {
        color: #22c55e;
        border-color: #22c55e;
        background: rgba(34, 197, 94, .06);
    }

    /* User messages — no copy button needed */
    .qcp-msg-user .qcp-msg-actions { display: none; }

    .qcp-chat-input-bar {
        padding: 16px 24px;
        border-top: 1px solid var(--qcp-border);
        background: #fff;
    }

    .qcp-chat-input-wrap {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .qcp-chat-input {
        flex: 1;
        background: #f8f9fc;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: .95rem;
        outline: none;
        color: #0f172a;
        transition: border-color .2s;
    }

    .qcp-chat-input:focus {
        border-color: var(--qcp-primary);
        box-shadow: 0 0 0 3px rgba(108, 71, 255, .1);
        background: #fff;
    }

    .qcp-send-btn {
        width: 46px;
        height: 46px;
        background: var(--qcp-gradient);
        border: none;
        border-radius: 12px;
        color: #fff;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: opacity .2s, transform .2s;
        flex-shrink: 0;
    }

    .qcp-send-btn:hover    { opacity: .9; transform: translateY(-1px); }
    .qcp-send-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }

    #charCounter { font-size: .78rem; transition: color .2s; }
    #charCounter.near-limit { color: #f59e0b; font-weight: 600; }
    #charCounter.at-limit   { color: #ef4444; font-weight: 600; }

    .qcp-typing-dots span {
        width: 6px;
        height: 6px;
        background: #94a3b8;
        border-radius: 50%;
        display: inline-block;
        animation: typing 1.2s infinite;
        margin: 0 2px;
    }

    .qcp-typing-dots span:nth-child(2) { animation-delay: .2s; }
    .qcp-typing-dots span:nth-child(3) { animation-delay: .4s; }

    @keyframes typing {
        0%, 60%, 100% { opacity: .3; transform: translateY(0);    }
        30%            { opacity: 1;  transform: translateY(-4px); }
    }
</style>

<script>
    const CHAT_URL    = '<?= $chatUrl ?>';
    const MAX_CHARS   = 200;

    const chatInput   = document.getElementById('chatInput');
    const charCounter = document.getElementById('charCounter');
    const sendBtn     = document.getElementById('chatSend');

    // ── Character counter ─────────────────────────────────────
    chatInput.addEventListener('input', function () {
        const len = this.value.length;
        charCounter.textContent = len + '/' + MAX_CHARS;
        charCounter.classList.remove('near-limit', 'at-limit');
        if (len >= MAX_CHARS) {
            charCounter.classList.add('at-limit');
        } else if (len >= Math.floor(MAX_CHARS * 0.85)) {
            charCounter.classList.add('near-limit');
        }
    });

    // ── Event listeners ───────────────────────────────────────
    sendBtn.addEventListener('click', sendChat);
    chatInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendChat();
        }
    });

    // ── Send message ──────────────────────────────────────────
    function sendChat() {
        const question = chatInput.value.trim();
        if (!question) return;

        if (question.length > MAX_CHARS) {
            appendMsg('ai', '<span class="text-danger">Message cannot exceed 200 characters.</span>');
            return;
        }

        appendMsg('user', escapeHtml(question));
        resetInput();
        sendBtn.disabled = true;

        const typingEl = appendMsg('ai',
            '<div class="qcp-typing-dots"><span></span><span></span><span></span></div>'
        );

        const body = 'question=' + encodeURIComponent(question) +
            '&_csrf=' + encodeURIComponent(getCsrfToken());

        fetch(CHAT_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body,
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            sendBtn.disabled   = false;
            typingEl.innerHTML = data.success
                ? data.data.answer
                : '<span class="text-danger">' + escapeHtml(data.message) + '</span>';
            if (data.success && window.qcpCredits) {
                window.qcpCredits.consume(<?= (int) (CREDIT_COSTS['chat'] ?? 1) ?>);
            }
            scrollToBottom();
        })
        .catch(function (err) {
            sendBtn.disabled   = false;
            typingEl.innerHTML = '<span class="text-danger">Error: ' + escapeHtml(err.message) + '</span>';
        });
    }

    // ── Append message bubble ─────────────────────────────────
    function appendMsg(role, html) {
        const icon = role === 'user'
            ? '<i class="bi bi-person-fill"></i>'
            : '<i class="bi bi-robot"></i>';

        const copyBtn = role === 'ai'
            ? '<div class="qcp-msg-actions">' +
              '<button class="qcp-copy-btn" onclick="copyMsg(this)" title="Copy">' +
              '<i class="bi bi-clipboard"></i></button></div>'
            : '';

        const div     = document.createElement('div');
        div.className = 'qcp-msg qcp-msg-' + role;
        div.innerHTML =
            '<div class="qcp-msg-avatar">' + icon + '</div>' +
            '<div class="qcp-msg-wrapper">' +
                '<div class="qcp-msg-bubble">' + html + '</div>' +
                copyBtn +
            '</div>';

        document.getElementById('chatMessages').appendChild(div);
        scrollToBottom();
        return div.querySelector('.qcp-msg-bubble');
    }

    // ── Copy AI message to clipboard ──────────────────────────
    function copyMsg(btn) {
        const bubble   = btn.closest('.qcp-msg-wrapper').querySelector('.qcp-msg-bubble');
        const text     = bubble.innerText;

        navigator.clipboard.writeText(text).then(function () {
            btn.innerHTML = '<i class="bi bi-check"></i>';
            btn.classList.add('copied');
            setTimeout(function () {
                btn.innerHTML = '<i class="bi bi-clipboard"></i>';
                btn.classList.remove('copied');
            }, 2000);
        });
    }

    // ── Reset input & counter ─────────────────────────────────
    function resetInput() {
        chatInput.value         = '';
        charCounter.textContent = '0/' + MAX_CHARS;
        charCounter.classList.remove('near-limit', 'at-limit');
    }

    // ── Scroll chat to bottom ─────────────────────────────────
    function scrollToBottom() {
        const el     = document.getElementById('chatMessages');
        el.scrollTop = el.scrollHeight;
    }

    // ── Get CSRF token ────────────────────────────────────────
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf"]')?.content || '';
    }

    // ── Escape HTML to prevent XSS ────────────────────────────
    function escapeHtml(str) {
        return String(str)
            .replace(/&/g,  '&amp;')
            .replace(/</g,  '&lt;')
            .replace(/>/g,  '&gt;')
            .replace(/"/g,  '&quot;')
            .replace(/'/g,  '&#039;');
    }
</script>

<?php
$extraScripts = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
