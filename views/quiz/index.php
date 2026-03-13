<?php
$seo = ['title' => 'Quiz - QuickChatPDF', 'canonical' => '/quiz'];
ob_start();
$appUrl = APP_URL;
$creditBalance = (int) ($user['credits'] ?? 0);
$quizCost = (int) (CREDIT_COSTS['quiz'] ?? 2);
?>

<nav class="navbar navbar-light qcp-navbar sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= APP_URL ?>/dashboard">
            <div class="qcp-logo-icon small"><i class="bi bi-arrow-left"></i></div>
            <span class="text-dark fw-600">Quiz Studio</span>
        </a>
        <div class="d-flex align-items-center gap-2">
            <span class="qcp-plan-badge">
                <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
                <span class="js-credit-balance"><?= number_format($creditBalance) ?></span> credits
            </span>
            <a href="<?= APP_URL ?>/dashboard" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-grid me-1"></i>Dashboard
            </a>
        </div>
    </div>
</nav>

<div class="container py-4 py-lg-5 qcp-quiz-shell">
    <div id="setupScreen">
        <section class="qcp-quiz-hero mb-4">
            <div class="qcp-quiz-hero-copy">
                <span class="qcp-quiz-kicker">Adaptive quiz mode</span>
                <h1>Turn the current PDF into a focused, test-style quiz.</h1>
                <p>Choose a format, question count, and timer. Answers stay hidden until the final review so the session behaves like a real quiz, not a flashcard reveal.</p>
            </div>
            <div class="qcp-quiz-hero-metrics">
                <div class="qcp-hero-metric">
                    <span>Quiz cost</span>
                    <strong><?= $quizCost ?> credits</strong>
                </div>
                <div class="qcp-hero-metric">
                    <span>Mode</span>
                    <strong>MCQ or True/False</strong>
                </div>
                <div class="qcp-hero-metric">
                    <span>Review</span>
                    <strong>Detailed results after submission</strong>
                </div>
            </div>
        </section>

        <section class="qcp-setup-grid">
            <div class="qcp-setup-card">
                <div class="qcp-setup-header">
                    <div>
                        <span class="qcp-setup-eyebrow">Configure</span>
                        <h2>Build your quiz</h2>
                    </div>
                    <div class="qcp-setup-badge">
                        <i class="bi bi-stars"></i> Generated from your PDF
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="qcp-label">Quiz Type</label>
                        <select id="quizType" class="form-select qcp-select">
                            <option value="mcq">Multiple Choice</option>
                            <option value="truefalse">True / False</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="qcp-label">Question Count</label>
                        <select id="quizCount" class="form-select qcp-select">
                            <option value="5">5 questions</option>
                            <option value="10">10 questions</option>
                            <option value="15">15 questions</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="qcp-label">Time Per Question</label>
                        <select id="quizTimer" class="form-select qcp-select">
                            <option value="30">30 seconds</option>
                            <option value="60" selected>60 seconds</option>
                            <option value="90">90 seconds</option>
                            <option value="0">No timer</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="qcp-label">Quiz Style</label>
                        <div class="qcp-style-note">
                            <i class="bi bi-check2-circle"></i>
                            <span>Answers appear only in the results review.</span>
                        </div>
                    </div>
                </div>

                <div class="qcp-setup-actions">
                    <button id="startBtn" class="btn qcp-primary-btn btn-lg">
                        <i class="bi bi-rocket-takeoff-fill me-2"></i>Generate Quiz
                    </button>
                    <p class="text-muted mb-0">Each new quiz deducts <?= $quizCost ?> credits after successful generation.</p>
                </div>
            </div>

            <aside class="qcp-setup-side">
                <div class="qcp-side-card">
                    <h3>What changes now</h3>
                    <ul class="qcp-side-list">
                        <li>Questions render one at a time with a clear progress state.</li>
                        <li>Your selected answer is locked in, without exposing the solution instantly.</li>
                        <li>The results screen shows correct answers, your wrong answers, and explanations.</li>
                    </ul>
                </div>
            </aside>
        </section>
    </div>

    <div id="loadingScreen" class="qcp-loading-screen" style="display:none">
        <div class="qcp-loading-orb"></div>
        <h2>Generating your quiz</h2>
        <p>We are building questions from the current PDF. This usually takes a few seconds.</p>
    </div>

    <div id="quizScreen" style="display:none">
        <section class="qcp-quiz-stage">
            <aside class="qcp-quiz-sidebar">
                <div class="qcp-stage-card">
                    <span class="qcp-stage-label">Progress</span>
                    <h3 id="progressText">Question 1 of 5</h3>
                    <div class="progress qcp-progress-track">
                        <div id="progressBar" class="progress-bar qcp-progress-bar" style="width:0%"></div>
                    </div>
                </div>
                <div class="qcp-stage-card">
                    <span class="qcp-stage-label">Live score</span>
                    <h3 id="scoreText">0 correct</h3>
                    <p class="mb-0 text-muted">Final explanations appear only after the full quiz is complete.</p>
                </div>
                <div class="qcp-stage-card" id="timerWrap">
                    <span class="qcp-stage-label">Question timer</span>
                    <h3><span id="timerDisplay">60</span>s</h3>
                    <p class="mb-0 text-muted">Answer before the timer runs out.</p>
                </div>
            </aside>

            <div class="qcp-question-shell">
                <div class="qcp-question-card" id="questionCard">
                    <div class="qcp-question-meta">
                        <span class="qcp-question-chip" id="questionTypeChip">Multiple Choice</span>
                        <span class="qcp-question-chip muted" id="questionCounterChip">Question 1</span>
                    </div>
                    <h2 id="questionText" class="qcp-question-text"></h2>
                    <div id="optionsArea" class="qcp-options-grid"></div>
                    <div id="feedbackArea" class="qcp-answer-state" style="display:none"></div>
                </div>

                <div class="qcp-question-actions">
                    <button id="nextBtn" class="btn qcp-primary-btn" style="display:none">
                        Next Question <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </section>
    </div>

    <div id="resultScreen" style="display:none">
        <section class="qcp-result-hero">
            <div class="qcp-result-badge" id="resultIcon"></div>
            <div>
                <h2 id="resultTitle"></h2>
                <p id="resultSubtitle" class="mb-0 text-muted"></p>
            </div>
        </section>

        <section class="qcp-result-summary">
            <div class="qcp-result-stat">
                <span>Correct</span>
                <strong id="correctCount">0</strong>
            </div>
            <div class="qcp-result-stat">
                <span>Wrong</span>
                <strong id="wrongCount">0</strong>
            </div>
            <div class="qcp-result-stat">
                <span>Score</span>
                <strong id="percentScore">0%</strong>
            </div>
        </section>

        <section class="qcp-review-panel">
            <div class="qcp-review-head">
                <div>
                    <span class="qcp-stage-label">Review</span>
                    <h3>Answer breakdown</h3>
                </div>
                <p class="mb-0 text-muted">Wrong answers and missed questions are clearly separated below.</p>
            </div>
            <div id="reviewArea"></div>
        </section>

        <div class="qcp-result-actions">
            <button onclick="location.reload()" class="btn btn-outline-primary btn-lg px-4">
                <i class="bi bi-arrow-counterclockwise me-2"></i>New Quiz
            </button>
            <a href="<?= APP_URL ?>/dashboard" class="btn qcp-primary-btn btn-lg px-4">
                <i class="bi bi-grid me-2"></i>Dashboard
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$mcqUrl = $appUrl . '/quiz/mcq';
$tfUrl = $appUrl . '/quiz/truefalse';
ob_start();
?>
<style>
    .qcp-quiz-shell {
        max-width: 1180px;
    }

    .qcp-quiz-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(320px, .9fr);
        gap: 24px;
        padding: 28px;
        border-radius: 28px;
        border: 1px solid rgba(99, 102, 241, .14);
        background:
            radial-gradient(circle at top left, rgba(251, 191, 36, .18), transparent 32%),
            radial-gradient(circle at bottom right, rgba(14, 165, 233, .14), transparent 34%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 24px 60px rgba(15, 23, 42, .06);
    }

    .qcp-quiz-kicker,
    .qcp-setup-eyebrow,
    .qcp-stage-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #6366f1;
    }

    .qcp-quiz-hero-copy h1,
    .qcp-setup-header h2,
    .qcp-review-head h3,
    .qcp-result-hero h2 {
        font-family: var(--font-heading);
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 10px;
    }

    .qcp-quiz-hero-copy h1 {
        font-size: clamp(2rem, 4vw, 3.35rem);
        line-height: 1;
    }

    .qcp-quiz-hero-copy p,
    .qcp-setup-header p {
        font-size: 1rem;
        color: #475569;
        max-width: 680px;
        margin-bottom: 0;
    }

    .qcp-quiz-hero-metrics,
    .qcp-result-summary {
        display: grid;
        gap: 14px;
    }

    .qcp-hero-metric,
    .qcp-result-stat {
        padding: 18px 20px;
        border-radius: 20px;
        background: rgba(255, 255, 255, .78);
        border: 1px solid rgba(148, 163, 184, .18);
        backdrop-filter: blur(8px);
    }

    .qcp-hero-metric span,
    .qcp-result-stat span {
        display: block;
        font-size: .86rem;
        color: #64748b;
        margin-bottom: 8px;
    }

    .qcp-hero-metric strong,
    .qcp-result-stat strong {
        font-size: 1.2rem;
        color: #0f172a;
    }

    .qcp-setup-grid,
    .qcp-quiz-stage {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 320px;
        gap: 24px;
    }

    .qcp-setup-card,
    .qcp-side-card,
    .qcp-stage-card,
    .qcp-question-card,
    .qcp-review-panel {
        background: #fff;
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 24px;
        box-shadow: 0 18px 48px rgba(15, 23, 42, .06);
    }

    .qcp-setup-card {
        padding: 30px;
    }

    .qcp-setup-header,
    .qcp-review-head,
    .qcp-result-hero {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
    }

    .qcp-setup-badge,
    .qcp-question-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        font-size: .85rem;
        font-weight: 700;
        background: rgba(99, 102, 241, .08);
        color: #4f46e5;
    }

    .qcp-question-chip.muted {
        background: rgba(148, 163, 184, .12);
        color: #475569;
    }

    .qcp-select {
        min-height: 54px;
        border-radius: 16px;
        border-color: #dbe3f0;
        box-shadow: none;
    }

    .qcp-style-note {
        min-height: 54px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 16px;
        border-radius: 16px;
        color: #0f766e;
        background: #ecfeff;
        border: 1px solid #bae6fd;
        font-weight: 600;
    }

    .qcp-setup-actions,
    .qcp-result-actions,
    .qcp-question-actions {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        margin-top: 28px;
    }

    .qcp-primary-btn {
        background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
        border: 0;
        color: #fff;
        border-radius: 16px;
        padding: 14px 24px;
        font-weight: 700;
        box-shadow: 0 20px 40px rgba(79, 70, 229, .22);
    }

    .qcp-primary-btn:hover,
    .qcp-primary-btn:focus {
        color: #fff;
        transform: translateY(-1px);
    }

    .qcp-side-card,
    .qcp-stage-card {
        padding: 22px;
    }

    .qcp-side-card h3,
    .qcp-stage-card h3 {
        font-size: 1.2rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 12px;
    }

    .qcp-side-list {
        margin: 0;
        padding-left: 18px;
        color: #475569;
    }

    .qcp-side-list li + li {
        margin-top: 10px;
    }

    .qcp-loading-screen {
        text-align: center;
        padding: 82px 24px;
    }

    .qcp-loading-orb {
        width: 84px;
        height: 84px;
        margin: 0 auto 22px;
        border-radius: 50%;
        background:
            radial-gradient(circle at 30% 30%, rgba(255, 255, 255, .9), transparent 25%),
            linear-gradient(135deg, #22c55e, #3b82f6, #7c3aed);
        box-shadow: 0 18px 50px rgba(59, 130, 246, .22);
        animation: qcpPulse 1.6s ease-in-out infinite;
    }

    @keyframes qcpPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.08); }
    }

    .qcp-progress-track {
        height: 10px;
        border-radius: 999px;
        background: #e2e8f0;
    }

    .qcp-progress-bar {
        background: linear-gradient(90deg, #06b6d4 0%, #7c3aed 100%);
        transition: width .3s ease;
    }

    .qcp-question-shell {
        min-width: 0;
    }

    .qcp-question-card {
        position: relative;
        padding: 28px;
        overflow: hidden;
    }

    .qcp-question-card::before {
        content: "";
        position: absolute;
        inset: auto -40px -40px auto;
        width: 160px;
        height: 160px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(96, 165, 250, .12) 0%, transparent 70%);
    }

    .qcp-question-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }

    .qcp-question-text {
        font-size: clamp(1.4rem, 2vw, 2rem);
        font-weight: 800;
        line-height: 1.25;
        color: #0f172a;
        margin-bottom: 24px;
    }

    .qcp-options-grid {
        display: grid;
        gap: 14px;
    }

    .qcp-option-btn {
        width: 100%;
        text-align: left;
        padding: 18px 20px;
        border-radius: 18px;
        border: 1px solid #dbe3f0;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        color: #0f172a;
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .qcp-option-btn:hover:not(:disabled) {
        transform: translateY(-1px);
        border-color: #818cf8;
        box-shadow: 0 18px 36px rgba(99, 102, 241, .12);
    }

    .qcp-option-btn.selected {
        border-color: #4f46e5;
        background: rgba(99, 102, 241, .07);
        box-shadow: 0 18px 36px rgba(99, 102, 241, .12);
    }

    .qcp-option-btn.correct {
        border-color: #16a34a;
        background: #f0fdf4;
    }

    .qcp-option-btn.wrong {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .qcp-option-label {
        display: inline-flex;
        width: 34px;
        height: 34px;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(99, 102, 241, .1);
        color: #4338ca;
        font-weight: 800;
        margin-right: 12px;
    }

    .qcp-answer-state {
        margin-top: 18px;
        padding: 16px 18px;
        border-radius: 18px;
        border: 1px solid #dbe3f0;
        background: #f8fafc;
        color: #334155;
        font-weight: 600;
    }

    .qcp-answer-state.correct {
        border-color: #bbf7d0;
        background: #f0fdf4;
        color: #166534;
    }

    .qcp-answer-state.wrong {
        border-color: #fecaca;
        background: #fef2f2;
        color: #b91c1c;
    }

    .qcp-result-hero {
        margin-bottom: 22px;
        padding: 28px;
        border-radius: 24px;
        background: linear-gradient(135deg, #eff6ff 0%, #faf5ff 100%);
        border: 1px solid rgba(99, 102, 241, .15);
    }

    .qcp-result-badge {
        width: 84px;
        height: 84px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 24px;
        font-size: 2rem;
        background: #fff;
        box-shadow: 0 18px 40px rgba(99, 102, 241, .12);
    }

    .qcp-result-summary {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        margin-bottom: 24px;
    }

    .qcp-review-panel {
        padding: 26px;
    }

    .qcp-review-item {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 18px 20px;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
    }

    .qcp-review-item + .qcp-review-item {
        margin-top: 14px;
    }

    .qcp-review-item.correct-item {
        border-left: 4px solid #16a34a;
    }

    .qcp-review-item.wrong-item {
        border-left: 4px solid #ef4444;
    }

    .qcp-review-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        padding: 6px 12px;
        font-size: .8rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .qcp-review-status.correct {
        color: #166534;
        background: #dcfce7;
    }

    .qcp-review-status.wrong {
        color: #b91c1c;
        background: #fee2e2;
    }

    .qcp-review-meta {
        display: grid;
        gap: 8px;
        color: #475569;
        font-size: .95rem;
    }

    @media (max-width: 991.98px) {
        .qcp-quiz-hero,
        .qcp-setup-grid,
        .qcp-quiz-stage {
            grid-template-columns: 1fr;
        }

        .qcp-result-summary {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .qcp-setup-card,
        .qcp-question-card,
        .qcp-review-panel,
        .qcp-result-hero {
            padding: 22px;
        }

        .qcp-setup-header,
        .qcp-review-head,
        .qcp-result-hero {
            flex-direction: column;
        }

        .qcp-question-text {
            font-size: 1.25rem;
        }
    }
</style>
<script>
    const MCQ_URL = '<?= $mcqUrl ?>';
    const TF_URL = '<?= $tfUrl ?>';
    const QUIZ_COST = <?= $quizCost ?>;

    let quizData = [];
    let current = 0;
    let score = 0;
    let answered = [];
    let timerSecs = 60;
    let timerInterval = null;
    let quizType = 'mcq';

    document.getElementById('startBtn').addEventListener('click', function () {
        quizType = document.getElementById('quizType').value;
        const cnt = document.getElementById('quizCount').value;
        timerSecs = parseInt(document.getElementById('quizTimer').value, 10);

        const url = quizType === 'mcq' ? MCQ_URL : TF_URL;
        const body = 'count=' + cnt + '&_csrf=' + encodeURIComponent(document.querySelector('meta[name="csrf"]')?.content || '');

        show('loadingScreen');
        hide('setupScreen');

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body,
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    alert('Error: ' + data.message);
                    show('setupScreen');
                    hide('loadingScreen');
                    return;
                }

                if (window.qcpCredits) {
                    window.qcpCredits.consume(QUIZ_COST);
                }

                quizData = Array.isArray(data.data.quiz) ? data.data.quiz : [];
                current = 0;
                score = 0;
                answered = [];

                hide('loadingScreen');
                show('quizScreen');

                document.getElementById('questionTypeChip').textContent = quizType === 'mcq' ? 'Multiple Choice' : 'True / False';
                toggleTimerVisibility();
                renderQuestion();
            })
            .catch(function (err) {
                alert('Error: ' + err.message);
                show('setupScreen');
                hide('loadingScreen');
            });
    });

    function renderQuestion() {
        const q = quizData[current];
        const total = quizData.length;

        document.getElementById('progressText').textContent = 'Question ' + (current + 1) + ' of ' + total;
        document.getElementById('questionCounterChip').textContent = 'Question ' + (current + 1);
        document.getElementById('scoreText').textContent = score + ' correct';
        document.getElementById('progressBar').style.width = (((current) / total) * 100) + '%';
        document.getElementById('questionText').textContent = q.question;
        document.getElementById('feedbackArea').style.display = 'none';
        document.getElementById('feedbackArea').className = 'qcp-answer-state';
        document.getElementById('nextBtn').style.display = 'none';

        const opts = document.getElementById('optionsArea');
        opts.innerHTML = '';

        if (quizType === 'mcq') {
            Object.entries(q.options || {}).forEach(function (entry) {
                const key = entry[0];
                const value = entry[1];
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'qcp-option-btn';
                btn.dataset.key = key;
                btn.innerHTML = '<span class="qcp-option-label">' + escapeHtml(key) + '</span><span>' + escapeHtml(String(value)) + '</span>';
                btn.addEventListener('click', function () {
                    answerCurrentQuestion(key);
                });
                opts.appendChild(btn);
            });
        } else {
            [
                { key: true, label: 'True', icon: 'bi-check-circle-fill' },
                { key: false, label: 'False', icon: 'bi-x-circle-fill' }
            ].forEach(function (option) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'qcp-option-btn';
                btn.dataset.key = option.key ? 'true' : 'false';
                btn.innerHTML = '<i class="bi ' + option.icon + ' me-2"></i><span>' + option.label + '</span>';
                btn.addEventListener('click', function () {
                    answerCurrentQuestion(option.key);
                });
                opts.appendChild(btn);
            });
        }

        startTimer();
    }

    function answerCurrentQuestion(selected) {
        stopTimer();

        const q = quizData[current];
        const correct = quizType === 'mcq' ? q.answer : Boolean(q.answer);
        const isCorrect = selected === correct;
        const selectedLabel = formatAnswer(selected, q);
        const correctLabel = formatAnswer(correct, q);

        if (isCorrect) {
            score++;
        }

        document.querySelectorAll('#optionsArea .qcp-option-btn').forEach(function (btn) {
            btn.disabled = true;
            if (String(btn.dataset.key) === String(quizType === 'mcq' ? selected : (selected ? 'true' : 'false'))) {
                btn.classList.add('selected');
            }
        });

        answered.push({
            question: q.question,
            selected: selectedLabel,
            correct: correctLabel,
            isCorrect: isCorrect,
            explanation: q.explanation || '',
            options: q.options || null
        });

        showFeedback(
            isCorrect,
            isCorrect ? 'Answer locked in. Nice work.' : 'Answer recorded. Review the correction in the results section.'
        );
    }

    function showFeedback(isCorrect, message) {
        const feedback = document.getElementById('feedbackArea');
        feedback.className = 'qcp-answer-state ' + (isCorrect ? 'correct' : 'wrong');
        feedback.innerHTML = '<i class="bi ' + (isCorrect ? 'bi-check2-circle' : 'bi-info-circle') + ' me-2"></i>' + escapeHtml(message);
        feedback.style.display = 'block';
        document.getElementById('scoreText').textContent = score + ' correct';
        document.getElementById('nextBtn').style.display = 'inline-flex';
    }

    document.getElementById('nextBtn').addEventListener('click', function () {
        current++;
        if (current < quizData.length) {
            renderQuestion();
            return;
        }

        document.getElementById('progressBar').style.width = '100%';
        showResults();
    });

    function startTimer() {
        if (timerSecs === 0) {
            return;
        }

        let remaining = timerSecs;
        const timerWrap = document.getElementById('timerWrap');
        document.getElementById('timerDisplay').textContent = remaining;
        timerWrap.classList.remove('danger');

        timerInterval = setInterval(function () {
            remaining--;
            document.getElementById('timerDisplay').textContent = remaining;

            if (remaining <= 10) {
                timerWrap.classList.add('danger');
            }

            if (remaining <= 0) {
                stopTimer();
                timeOut();
            }
        }, 1000);
    }

    function stopTimer() {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
    }

    function timeOut() {
        const q = quizData[current];
        const correct = quizType === 'mcq' ? q.answer : Boolean(q.answer);

        document.querySelectorAll('#optionsArea .qcp-option-btn').forEach(function (btn) {
            btn.disabled = true;
        });

        answered.push({
            question: q.question,
            selected: 'No answer',
            correct: formatAnswer(correct, q),
            isCorrect: false,
            explanation: q.explanation || '',
            options: q.options || null
        });

        showFeedback(false, 'Time is up. Review the correct answer in the final results.');
    }

    function showResults() {
        stopTimer();
        hide('quizScreen');
        show('resultScreen');

        const total = quizData.length;
        const correct = score;
        const wrong = total - correct;
        const pct = total > 0 ? Math.round((correct / total) * 100) : 0;

        document.getElementById('correctCount').textContent = correct;
        document.getElementById('wrongCount').textContent = wrong;
        document.getElementById('percentScore').textContent = pct + '%';

        let icon = '💪';
        let title = 'Keep going';
        let subtitle = 'Review the answers below and try another round.';

        if (pct >= 80) {
            icon = '🏆';
            title = 'Excellent result';
            subtitle = 'You handled this PDF with confidence.';
        } else if (pct >= 60) {
            icon = '👏';
            title = 'Strong attempt';
            subtitle = 'A few corrections below will tighten your understanding.';
        } else if (pct >= 40) {
            icon = '📘';
            title = 'Solid practice round';
            subtitle = 'Use the review cards to sharpen the weak spots.';
        }

        document.getElementById('resultIcon').textContent = icon;
        document.getElementById('resultTitle').textContent = title;
        document.getElementById('resultSubtitle').textContent = subtitle;

        document.getElementById('reviewArea').innerHTML = answered.map(function (item, index) {
            const statusClass = item.isCorrect ? 'correct' : 'wrong';
            const statusLabel = item.isCorrect ? 'Correct' : 'Needs review';

            return '<article class="qcp-review-item ' + (item.isCorrect ? 'correct-item' : 'wrong-item') + '">' +
                '<div class="qcp-review-status ' + statusClass + '">' +
                '<i class="bi ' + (item.isCorrect ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill') + '"></i>' + statusLabel +
                '</div>' +
                '<h4 class="fw-700 mb-3">' + (index + 1) + '. ' + escapeHtml(item.question) + '</h4>' +
                '<div class="qcp-review-meta">' +
                '<div><strong>Your answer:</strong> ' + escapeHtml(item.selected) + '</div>' +
                '<div><strong>Correct answer:</strong> ' + escapeHtml(item.correct) + '</div>' +
                (item.explanation ? '<div><strong>Why:</strong> ' + escapeHtml(item.explanation) + '</div>' : '') +
                '</div>' +
                '</article>';
        }).join('');
    }

    function formatAnswer(answer, question) {
        if (quizType === 'mcq') {
            const optionText = question.options && question.options[answer] ? ' - ' + question.options[answer] : '';
            return String(answer) + optionText;
        }

        return answer ? 'True' : 'False';
    }

    function toggleTimerVisibility() {
        document.getElementById('timerWrap').style.display = timerSecs === 0 ? 'none' : 'block';
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function show(id) {
        document.getElementById(id).style.display = 'block';
    }

    function hide(id) {
        document.getElementById(id).style.display = 'none';
    }
</script>
<?php
$extraScripts = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
