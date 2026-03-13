<?php
$seo = ['title' => 'Quiz — QuickChatPDF', 'canonical' => '/quiz'];
ob_start();
$appUrl = APP_URL;
$creditBalance = (int) ($user['credits'] ?? 0);
?>

<nav class="navbar navbar-light qcp-navbar sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= APP_URL ?>/dashboard">
            <div class="qcp-logo-icon small"><i class="bi bi-arrow-left"></i></div>
            <span class="text-dark fw-600">Quiz</span>
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

<div class="container py-5" style="max-width:760px">

    <!-- Setup Screen -->
    <div id="setupScreen">
        <div class="text-center mb-5">
            <div class="qcp-feature-icon qcp-icon-orange mx-auto mb-3" style="width:64px;height:64px;font-size:1.6rem">
                <i class="bi bi-ui-checks-grid"></i>
            </div>
            <h2 class="fw-800 mb-2">Quiz Generator</h2>
            <p class="text-muted">Test your knowledge from the PDF document</p>
        </div>

        <div class="qcp-panel-card">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="qcp-label">Quiz Type</label>
                    <select id="quizType" class="form-select">
                        <option value="mcq">Multiple Choice (MCQ)</option>
                        <option value="truefalse">True / False</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="qcp-label">Number of Questions</label>
                    <select id="quizCount" class="form-select">
                        <option value="5">5 questions</option>
                        <option value="10">10 questions</option>
                        <option value="15">15 questions</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="qcp-label">Time per Question</label>
                    <select id="quizTimer" class="form-select">
                        <option value="30">30 seconds</option>
                        <option value="60" selected>60 seconds</option>
                        <option value="90">90 seconds</option>
                        <option value="0">No timer</option>
                    </select>
                </div>
                <div class="col-12 text-center pt-2">
                    <button id="startBtn" class="btn btn-primary btn-lg px-5">
                        <i class="bi bi-play-fill me-2"></i>Generate &amp; Start Quiz
                    </button>
                    <p class="text-muted small mt-2">Costs
                        <?= CREDIT_COSTS['quiz'] ?? 3 ?> credits
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Screen -->
    <div id="loadingScreen" style="display:none" class="text-center py-5">
        <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem" role="status"></div>
        <h5 class="fw-700">Generating your quiz...</h5>
        <p class="text-muted">This may take a moment</p>
    </div>

    <!-- Quiz Screen -->
    <div id="quizScreen" style="display:none">

        <!-- Progress Bar -->
        <div class="qcp-quiz-progress mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-600 text-muted" id="progressText">Question 1 of 5</span>
                <div class="d-flex align-items-center gap-3">
                    <span class="small fw-600 text-success" id="scoreText">Score: 0</span>
                    <div class="qcp-timer-wrap" id="timerWrap">
                        <i class="bi bi-clock me-1"></i>
                        <span id="timerDisplay">60</span>s
                    </div>
                </div>
            </div>
            <div class="progress" style="height:6px;border-radius:100px">
                <div id="progressBar" class="progress-bar"
                    style="background:var(--qcp-gradient);width:0%;transition:width .4s"></div>
            </div>
        </div>

        <!-- Question Card -->
        <div class="qcp-quiz-card" id="questionCard">
            <div class="qcp-quiz-q" id="questionText"></div>
            <div id="optionsArea" class="mt-4"></div>
            <div id="feedbackArea" class="mt-3" style="display:none"></div>
        </div>

        <!-- Next Button -->
        <div class="text-center mt-4">
            <button id="nextBtn" class="btn btn-primary btn-lg px-5" style="display:none">
                Next Question <i class="bi bi-arrow-right ms-2"></i>
            </button>
        </div>

    </div>

    <!-- Result Screen -->
    <div id="resultScreen" style="display:none">
        <div class="text-center mb-5">
            <div id="resultIcon" class="mx-auto mb-4" style="font-size:4rem"></div>
            <h2 class="fw-800 mb-2" id="resultTitle"></h2>
            <p class="text-muted" id="resultSubtitle"></p>
        </div>

        <!-- Score Card -->
        <div class="qcp-result-card mb-4">
            <div class="row g-4 text-center">
                <div class="col-4">
                    <div class="qcp-result-stat">
                        <div class="qcp-result-num text-success" id="correctCount">0</div>
                        <div class="qcp-result-label">Correct</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="qcp-result-stat">
                        <div class="qcp-result-num text-danger" id="wrongCount">0</div>
                        <div class="qcp-result-label">Wrong</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="qcp-result-stat">
                        <div class="qcp-result-num text-primary" id="percentScore">0%</div>
                        <div class="qcp-result-label">Score</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review -->
        <div id="reviewArea"></div>

        <div class="text-center mt-4 d-flex gap-3 justify-content-center">
            <button onclick="location.reload()" class="btn btn-outline-primary btn-lg px-4">
                <i class="bi bi-arrow-counterclockwise me-2"></i>New Quiz
            </button>
            <a href="<?= APP_URL ?>/dashboard" class="btn btn-primary btn-lg px-4">
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
    .qcp-panel-card {
        background: #fff;
        border: 1px solid var(--qcp-border);
        border-radius: var(--qcp-radius);
        padding: 32px;
    }

    .qcp-timer-wrap {
        background: #fef3c7;
        color: #d97706;
        border: 1px solid #fde68a;
        border-radius: 8px;
        padding: 4px 10px;
        font-size: .82rem;
        font-weight: 700;
    }

    .qcp-timer-wrap.danger {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
        animation: blink .5s infinite;
    }

    @keyframes blink {

        0%,
        100% {
            opacity: 1
        }

        50% {
            opacity: .5
        }
    }

    .qcp-quiz-card {
        background: #fff;
        border: 1px solid var(--qcp-border);
        border-radius: 20px;
        padding: 36px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, .06);
    }

    .qcp-quiz-q {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.5;
    }

    .qcp-option {
        display: block;
        width: 100%;
        text-align: left;
        background: #f8f9fc;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 10px;
        font-size: .95rem;
        font-weight: 500;
        color: #334155;
        cursor: pointer;
        transition: all .2s;
    }

    .qcp-option:hover:not(:disabled) {
        border-color: var(--qcp-primary);
        background: rgba(108, 71, 255, .04);
        color: var(--qcp-primary);
    }

    .qcp-option.correct {
        border-color: #22c55e;
        background: #f0fdf4;
        color: #15803d;
    }

    .qcp-option.wrong {
        border-color: #ef4444;
        background: #fef2f2;
        color: #dc2626;
    }

    .qcp-option.reveal {
        border-color: #22c55e;
        background: #f0fdf4;
        color: #15803d;
        opacity: .7;
    }

    .qcp-tf-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 48%;
        padding: 20px;
        border-radius: 16px;
        font-size: 1.1rem;
        font-weight: 700;
        border: 2px solid #e2e8f0;
        background: #f8f9fc;
        cursor: pointer;
        transition: all .2s;
        color: #334155;
    }

    .qcp-tf-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, .1);
    }

    .qcp-tf-btn.true-btn:hover:not(:disabled) {
        border-color: #22c55e;
        background: #f0fdf4;
        color: #15803d;
    }

    .qcp-tf-btn.false-btn:hover:not(:disabled) {
        border-color: #ef4444;
        background: #fef2f2;
        color: #dc2626;
    }

    .qcp-tf-btn.correct {
        border-color: #22c55e;
        background: #f0fdf4;
        color: #15803d;
    }

    .qcp-tf-btn.wrong {
        border-color: #ef4444;
        background: #fef2f2;
        color: #dc2626;
    }

    .qcp-feedback {
        border-radius: 10px;
        padding: 12px 16px;
        font-size: .9rem;
        font-weight: 500;
    }

    .qcp-feedback.correct {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }

    .qcp-feedback.wrong {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .qcp-result-card {
        background: #fff;
        border: 1px solid var(--qcp-border);
        border-radius: 20px;
        padding: 32px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, .06);
    }

    .qcp-result-num {
        font-size: 2.2rem;
        font-weight: 800;
        font-family: var(--font-heading);
    }

    .qcp-result-label {
        font-size: .82rem;
        color: #64748b;
        font-weight: 500;
        margin-top: 4px;
    }

    .qcp-review-item {
        background: #fff;
        border: 1px solid var(--qcp-border);
        border-radius: 12px;
        padding: 18px 20px;
        margin-bottom: 12px;
    }

    .qcp-review-item.correct-item {
        border-left: 4px solid #22c55e;
    }

    .qcp-review-item.wrong-item {
        border-left: 4px solid #ef4444;
    }
</style>
<script>
    const MCQ_URL = '<?= $mcqUrl ?>';
    const TF_URL = '<?= $tfUrl ?>';

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
        timerSecs = parseInt(document.getElementById('quizTimer').value);

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
                if (!data.success) { alert('Error: ' + data.message); show('setupScreen'); hide('loadingScreen'); return; }
                if (window.qcpCredits) {
                    window.qcpCredits.consume(<?= (int) (CREDIT_COSTS['quiz'] ?? 2) ?>);
                }
                quizData = data.data.quiz;
                current = 0; score = 0; answered = [];
                hide('loadingScreen');
                show('quizScreen');
                if (timerSecs === 0) hide('timerWrap');
                renderQuestion();
            })
            .catch(function (err) {
                alert('Error: ' + err.message);
                show('setupScreen'); hide('loadingScreen');
            });
    });

    function renderQuestion() {
        const q = quizData[current];
        const tot = quizData.length;

        document.getElementById('progressText').textContent = 'Question ' + (current + 1) + ' of ' + tot;
        document.getElementById('scoreText').textContent = 'Score: ' + score;
        document.getElementById('progressBar').style.width = ((current / tot) * 100) + '%';
        document.getElementById('questionText').textContent = q.question;
        document.getElementById('feedbackArea').style.display = 'none';
        document.getElementById('nextBtn').style.display = 'none';

        const opts = document.getElementById('optionsArea');
        opts.innerHTML = '';

        if (quizType === 'mcq') {
            Object.entries(q.options).forEach(function (entry) {
                const k = entry[0], v = entry[1];
                const btn = document.createElement('button');
                btn.className = 'qcp-option';
                btn.innerHTML = '<strong>' + k + '.</strong> ' + v;
                btn.dataset.key = k;
                btn.addEventListener('click', function () { answerMCQ(k, q.answer, q.explanation || ''); });
                opts.appendChild(btn);
            });
        } else {
            opts.innerHTML =
                '<div class="d-flex gap-3 justify-content-center">' +
                '<button class="qcp-tf-btn true-btn" onclick="answerTF(true, ' + q.answer + ', \'' + (q.explanation || '').replace(/'/g, "\\'") + '\')"><i class="bi bi-check-circle-fill"></i> TRUE</button>' +
                '<button class="qcp-tf-btn false-btn" onclick="answerTF(false, ' + q.answer + ', \'' + (q.explanation || '').replace(/'/g, "\\'") + '\')"><i class="bi bi-x-circle-fill"></i> FALSE</button>' +
                '</div>';
        }

        startTimer();
    }

    function answerMCQ(selected, correct, explanation) {
        stopTimer();
        const isCorrect = selected === correct;
        if (isCorrect) score++;

        document.querySelectorAll('.qcp-option').forEach(function (btn) {
            btn.disabled = true;
            if (btn.dataset.key === correct) btn.classList.add('correct');
            if (btn.dataset.key === selected && !isCorrect) btn.classList.add('wrong');
        });

        answered.push({ question: quizData[current].question, selected: selected, correct: correct, isCorrect: isCorrect });
        showFeedback(isCorrect, 'Correct answer: ' + correct + (explanation ? '. ' + explanation : ''));
    }

    function answerTF(selected, correct, explanation) {
        stopTimer();
        const isCorrect = selected === correct;
        if (isCorrect) score++;

        document.querySelectorAll('.qcp-tf-btn').forEach(function (btn) {
            btn.disabled = true;
        });

        const trueBtn = document.querySelector('.true-btn');
        const falseBtn = document.querySelector('.false-btn');

        if (correct === true) trueBtn.classList.add('correct');
        else falseBtn.classList.add('correct');
        if (!isCorrect) {
            if (selected === true) trueBtn.classList.add('wrong');
            else falseBtn.classList.add('wrong');
        }

        answered.push({ question: quizData[current].question, selected: selected, correct: correct, isCorrect: isCorrect });
        showFeedback(isCorrect, 'Correct answer: ' + (correct ? 'TRUE' : 'FALSE') + (explanation ? '. ' + explanation : ''));
    }

    function showFeedback(isCorrect, msg) {
        const fb = document.getElementById('feedbackArea');
        fb.className = 'mt-3 qcp-feedback ' + (isCorrect ? 'correct' : 'wrong');
        fb.innerHTML = (isCorrect ? '<i class="bi bi-check-circle-fill me-2"></i>' : '<i class="bi bi-x-circle-fill me-2"></i>') + msg;
        fb.style.display = 'block';
        document.getElementById('nextBtn').style.display = 'inline-block';
        document.getElementById('scoreText').textContent = 'Score: ' + score;
    }

    document.getElementById('nextBtn').addEventListener('click', function () {
        current++;
        if (current < quizData.length) {
            renderQuestion();
        } else {
            showResults();
        }
    });

    function startTimer() {
        if (timerSecs === 0) return;
        let remaining = timerSecs;
        document.getElementById('timerDisplay').textContent = remaining;
        document.getElementById('timerWrap').className = 'qcp-timer-wrap';
        timerInterval = setInterval(function () {
            remaining--;
            document.getElementById('timerDisplay').textContent = remaining;
            if (remaining <= 10) document.getElementById('timerWrap').className = 'qcp-timer-wrap danger';
            if (remaining <= 0) {
                stopTimer();
                timeOut();
            }
        }, 1000);
    }

    function stopTimer() {
        if (timerInterval) { clearInterval(timerInterval); timerInterval = null; }
    }

    function timeOut() {
        const q = quizData[current];
        document.querySelectorAll('.qcp-option, .qcp-tf-btn').forEach(function (btn) { btn.disabled = true; });
        answered.push({ question: q.question, selected: null, correct: quizType === 'mcq' ? q.answer : q.answer, isCorrect: false });
        const fb = document.getElementById('feedbackArea');
        fb.className = 'mt-3 qcp-feedback wrong';
        fb.innerHTML = '<i class="bi bi-clock-fill me-2"></i>Time\'s up! Correct answer: ' + (quizType === 'mcq' ? q.answer : (q.answer ? 'TRUE' : 'FALSE'));
        fb.style.display = 'block';
        document.getElementById('nextBtn').style.display = 'inline-block';
    }

    function showResults() {
        stopTimer();
        hide('quizScreen');
        show('resultScreen');

        const total = quizData.length;
        const correct = score;
        const wrong = total - correct;
        const pct = Math.round((correct / total) * 100);

        document.getElementById('correctCount').textContent = correct;
        document.getElementById('wrongCount').textContent = wrong;
        document.getElementById('percentScore').textContent = pct + '%';

        let emoji, title, subtitle;
        if (pct >= 80) { emoji = '🏆'; title = 'Excellent!'; subtitle = 'Outstanding performance!'; }
        else if (pct >= 60) { emoji = '👍'; title = 'Good Job!'; subtitle = 'Keep it up!'; }
        else if (pct >= 40) { emoji = '📚'; title = 'Keep Studying'; subtitle = 'Review the material and try again.'; }
        else { emoji = '💪'; title = 'Keep Trying!'; subtitle = 'Practice makes perfect.'; }

        document.getElementById('resultIcon').textContent = emoji;
        document.getElementById('resultTitle').textContent = title;
        document.getElementById('resultSubtitle').textContent = subtitle;

        // Progress bar final
        document.getElementById('progressBar').style.width = '100%';

        // Review
        const review = document.getElementById('reviewArea');
        review.innerHTML = '<h6 class="fw-700 mb-3">Review Answers</h6>' +
            answered.map(function (a, i) {
                return '<div class="qcp-review-item ' + (a.isCorrect ? 'correct-item' : 'wrong-item') + '">' +
                    '<div class="d-flex align-items-start gap-2">' +
                    '<span class="' + (a.isCorrect ? 'text-success' : 'text-danger') + '">' +
                    '<i class="bi ' + (a.isCorrect ? 'bi-check-circle-fill' : 'bi-x-circle-fill') + '"></i></span>' +
                    '<div><p class="fw-600 mb-1 small">' + (i + 1) + '. ' + a.question + '</p>' +
                    '<p class="mb-0 small text-muted">Your answer: <strong>' + (a.selected !== null ? a.selected : 'No answer') + '</strong> · ' +
                    'Correct: <strong class="text-success">' + a.correct + '</strong></p>' +
                    '</div></div></div>';
            }).join('');
    }

    function show(id) { document.getElementById(id).style.display = 'block'; }
    function hide(id) { document.getElementById(id).style.display = 'none'; }

    const timerWrap = document.getElementById('timerWrap');
</script>
<?php
$extraScripts = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
