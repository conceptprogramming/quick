<?php
namespace Controllers;

use Database;
use Core\Session;
use Core\Request;
use Core\Response;
use Core\RateLimiter;
use Services\PDFRuntimeService;
use Services\CreditService;
use Services\OCRService;
use Services\AIService;

class PDFController
{

    private Session $session;
    private PDFRuntimeService $pdfService;
    private CreditService $creditService;
    private AIService $aiService;

    public function __construct()
    {
        $this->session = new Session();
        $this->pdfService = new PDFRuntimeService();
        $this->creditService = new CreditService();
        $this->aiService = new AIService();
    }

    // ── Upload ────────────────────────────────────────────────
    public function upload(): void
    {
        header('Content-Type: application/json');
        try {
            $userId = $this->session->userId();
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Not authenticated']);
                exit;
            }

            if (empty($_FILES)) {
                echo json_encode(['success' => false, 'message' => 'No file received.']);
                exit;
            }
            if (!isset($_FILES['pdf'])) {
                echo json_encode(['success' => false, 'message' => 'File key missing.']);
                exit;
            }

            if ($_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
                $errors = [1 => 'File too large (php.ini)', 2 => 'File too large (form)', 3 => 'Partial upload', 4 => 'No file', 6 => 'No temp folder', 7 => 'Write failed', 8 => 'Extension blocked'];
                echo json_encode(['success' => false, 'message' => $errors[$_FILES['pdf']['error']] ?? 'Upload error']);
                exit;
            }

            if (!RateLimiter::check(Request::ip(), 'upload')) {
                echo json_encode(['success' => false, 'message' => 'Too many uploads. Please wait.']);
                exit;
            }

            $db = Database::getInstance();
            $st = $db->prepare("SELECT plan FROM users WHERE id = :id LIMIT 1");
            $st->execute(['id' => $userId]);
            $user = $st->fetch();
            $plan = $user['plan'] ?? 'free';

            if (!$this->creditService->withinPlanLimit($userId, $plan, 'pdfs_per_month')) {
                echo json_encode(['success' => false, 'message' => 'Monthly PDF limit reached. Please upgrade.']);
                exit;
            }

            $validation = $this->pdfService->validate($_FILES['pdf'], $plan);
            if (!$validation['success']) {
                echo json_encode(['success' => false, 'message' => $validation['message']]);
                exit;
            }

            $pdfPath = $this->pdfService->store($_FILES['pdf']);
            $pageCount = $this->pdfService->getPageCount($pdfPath);
            $maxPages = PDF_LIMITS[$plan]['pages'];

            if ($pageCount === 0) {
                $this->pdfService->cleanup();
                echo json_encode(['success' => false, 'message' => 'Could not read PDF pages.']);
                exit;
            }
            if ($pageCount > $maxPages) {
                $this->pdfService->cleanup();
                echo json_encode(['success' => false, 'message' => "PDF has {$pageCount} pages. Plan limit is {$maxPages}."]);
                exit;
            }

            $_SESSION['pdf_uploaded'] = true;
            $_SESSION['pdf_page_count'] = $pageCount;
            $_SESSION['pdf_plan'] = $plan;

            echo json_encode(['success' => true, 'message' => 'PDF uploaded.', 'data' => ['pages' => $pageCount, 'max_pages' => $maxPages, 'plan' => $plan]]);
            exit;

        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }

    // ── Process ───────────────────────────────────────────────
    public function process(): void
    {
        header('Content-Type: application/json');
        try {
            $userId = $this->session->userId();
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Not authenticated']);
                exit;
            }
            if (empty($_SESSION['pdf_uploaded'])) {
                echo json_encode(['success' => false, 'message' => 'No PDF uploaded.']);
                exit;
            }
            if (empty($_SESSION['pdf_page_count'])) {
                echo json_encode(['success' => false, 'message' => 'Page count missing from session.']);
                exit;
            }

            $pageCount = $_SESSION['pdf_page_count'];
            $plan = $_SESSION['pdf_plan'] ?? 'free';
            $maxPages = PDF_LIMITS[$plan]['pages'];
            $pages = min($pageCount, $maxPages);

            if (!RateLimiter::check(Request::ip(), 'ai_request')) {
                echo json_encode(['success' => false, 'message' => 'Too many requests.']);
                exit;
            }

            $pdfPath = TMP_BASE . '/pdf/pdf_' . session_id() . '.pdf';
            if (!file_exists($pdfPath)) {
                echo json_encode(['success' => false, 'message' => 'Temp PDF missing. Please re-upload.']);
                exit;
            }

            $images = $this->pdfService->convertToImages($pdfPath, $pages);
            if (empty($images)) {
                echo json_encode(['success' => false, 'message' => 'Failed to convert PDF pages. GS path: ' . $this->pdfService->getGsPath()]);
                exit;
            }

            $ocrService = new OCRService();
            $fullText = $ocrService->extractAllPages($images);
            $this->pdfService->cleanup();

            if (empty(trim($fullText))) {
                echo json_encode(['success' => false, 'message' => 'OCR returned empty text.']);
                exit;
            }

            $_SESSION['pdf_text'] = $fullText;
            $_SESSION['pdf_processed'] = true;

           Database::getAliveInstance(); // reconnect if stale after OCR
$this->creditService->incrementUsage($userId, 'pdfs_uploaded');


            echo json_encode(['success' => true, 'message' => 'PDF processed.', 'data' => ['pages' => $pages, 'text_length' => strlen($fullText)]]);
            exit;

        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Exception: ' . $e->getMessage()]);
            exit;
        }
    }

    // ── Chat ──────────────────────────────────────────────────
    public function chat(): void
    {
        header('Content-Type: application/json');
        try {
            $userId = $this->session->userId();
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Not authenticated']);
                exit;
            }

            if (empty($_SESSION['pdf_processed'])) {
                echo json_encode(['success' => false, 'message' => 'No processed PDF in session. Please upload first.']);
                exit;
            }

            $question = trim($_POST['question'] ?? '');
            if (empty($question)) {
                echo json_encode(['success' => false, 'message' => 'Question is required.']);
                exit;
            }
            if (strlen($question) > 1000) {
                echo json_encode(['success' => false, 'message' => 'Question too long. Max 1000 characters.']);
                exit;
            }

$db = Database::getAliveInstance(); // ✅ reconnect if stale after large file upload
$st = $db->prepare("SELECT plan FROM users WHERE id = :id LIMIT 1");

            $st->execute(['id' => $userId]);
            $user = $st->fetch();
            $plan = $user['plan'] ?? 'free';

            if (!$this->creditService->hasCredits($userId, 'chat')) {
                $walletMessage = $this->creditService->getWalletAccessMessage($userId);
                echo json_encode(['success' => false, 'message' => $walletMessage ?: 'Not enough credits. Please top up.']);
                exit;
            }

            if (!$this->creditService->withinPlanLimit($userId, $plan, 'chat_messages')) {
                echo json_encode(['success' => false, 'message' => 'Monthly chat limit reached. Please upgrade.']);
                exit;
            }

            if (!RateLimiter::check(Request::ip(), 'ai_request')) {
                echo json_encode(['success' => false, 'message' => 'Too many requests. Please wait.']);
                exit;
            }

            $fullText = $_SESSION['pdf_text'];
            $answer = $this->aiService->chat($fullText, $question);

            if (!$answer) {
                echo json_encode(['success' => false, 'message' => 'AI failed to respond. Please try again.']);
                exit;
            }

            $this->creditService->deduct($userId, 'chat');
            $this->creditService->incrementUsage($userId, 'chat_messages');

            echo json_encode(['success' => true, 'data' => ['question' => $question, 'answer' => $answer]]);
            exit;

        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Exception: ' . $e->getMessage()]);
            exit;
        }
    }

    // ── Summary ───────────────────────────────────────────────
    public function summary(): void
    {
        header('Content-Type: application/json');
        try {
            $userId = $this->session->userId();
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Not authenticated']);
                exit;
            }
            if (empty($_SESSION['pdf_processed'])) {
                echo json_encode(['success' => false, 'message' => 'No PDF processed.']);
                exit;
            }

            $type = trim($_POST['type'] ?? 'detailed');
            $allowed = ['brief', 'detailed', 'comprehensive', 'keypoints', 'technical', 'simple', 'chapterwise', 'abstract'];
            if (!in_array($type, $allowed))
                $type = 'detailed';

            $db = Database::getInstance();
            $st = $db->prepare("SELECT plan, credits FROM users WHERE id = :id LIMIT 1");
            $st->execute(['id' => $userId]);
            $user = $st->fetch();
            $plan = $user['plan'] ?? 'free';

            if (!$this->creditService->hasCredits($userId, 'summary')) {
                $walletMessage = $this->creditService->getWalletAccessMessage($userId);
                echo json_encode(['success' => false, 'message' => $walletMessage ?: 'Not enough credits to generate a summary.']);
                exit;
            }
            if (!$this->creditService->withinPlanLimit($userId, $plan, 'summaries')) {
                echo json_encode(['success' => false, 'message' => 'Monthly summary limit reached.']);
                exit;
            }
            if (!RateLimiter::check(Request::ip(), 'ai_request')) {
                echo json_encode(['success' => false, 'message' => 'Too many requests. Please wait.']);
                exit;
            }

            $summary = $this->aiService->summarize($_SESSION['pdf_text'], $type);
            if (!$summary) {
                echo json_encode(['success' => false, 'message' => 'AI failed to generate summary.']);
                exit;
            }

            $this->creditService->deduct($userId, 'summary');
            $this->creditService->incrementUsage($userId, 'summaries');

            // get updated credits
            $st2 = $db->prepare("SELECT credits FROM users WHERE id = :id LIMIT 1");
            $st2->execute(['id' => $userId]);
            $remaining = $st2->fetchColumn();

            echo json_encode([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'type' => $type,
                    'credits_remaining' => $remaining,
                ],
            ]);
            exit;
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }


    // ── Q&A ───────────────────────────────────────────────────
    public function qa(): void
    {
        header('Content-Type: application/json');
        try {
            $userId = $this->session->userId();
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Not authenticated']);
                exit;
            }

            if (empty($_SESSION['pdf_processed'])) {
                echo json_encode(['success' => false, 'message' => 'No processed PDF. Please upload first.']);
                exit;
            }

            $count = min((int) ($_POST['count'] ?? 5), 20);

            $db = Database::getInstance();
            $st = $db->prepare("SELECT plan FROM users WHERE id = :id LIMIT 1");
            $st->execute(['id' => $userId]);
            $user = $st->fetch();
            $plan = $user['plan'] ?? 'free';

            if (!$this->creditService->hasCredits($userId, 'qa')) {
                $walletMessage = $this->creditService->getWalletAccessMessage($userId);
                echo json_encode(['success' => false, 'message' => $walletMessage ?: 'Not enough credits.']);
                exit;
            }

            if (!$this->creditService->withinPlanLimit($userId, $plan, 'qa_questions')) {
                echo json_encode(['success' => false, 'message' => 'Monthly Q&A limit reached. Please upgrade.']);
                exit;
            }

            if (!RateLimiter::check(Request::ip(), 'ai_request')) {
                echo json_encode(['success' => false, 'message' => 'Too many requests.']);
                exit;
            }

            $qa = $this->aiService->generateQA($_SESSION['pdf_text'], $count);
            if (!$qa) {
                echo json_encode(['success' => false, 'message' => 'AI failed to generate Q&A.']);
                exit;
            }

            $this->creditService->deduct($userId, 'qa');
            $this->creditService->incrementUsage($userId, 'qa_questions');

            echo json_encode(['success' => true, 'data' => ['qa' => $qa, 'count' => count($qa)]]);
            exit;

        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Exception: ' . $e->getMessage()]);
            exit;
        }
    }

    // ── Test ──────────────────────────────────────────────────
    public function test(): void
    {
        putenv('PATH=' . getenv('PATH') . ':/opt/homebrew/bin:/usr/local/bin');
        Response::json([
            'user_id' => $this->session->userId(),
            'tmp_writable' => is_writable(TMP_BASE . '/pdf'),
            'gs_direct' => trim(shell_exec('/opt/homebrew/bin/gs --version 2>/dev/null') ?? 'not found'),
            'pdfinfo' => !empty(shell_exec('pdfinfo -v 2>&1')),
            'pdf_uploaded' => $_SESSION['pdf_uploaded'] ?? false,
            'pdf_processed' => $_SESSION['pdf_processed'] ?? false,
            'text_length' => isset($_SESSION['pdf_text']) ? strlen($_SESSION['pdf_text']) : 0,
            'session_id' => session_id(),
        ]);
    }

    public function reset(): void
    {
        if (!$this->session->userId()) {
            Response::json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $this->pdfService->cleanup();
        unset(
            $_SESSION['pdf_uploaded'],
            $_SESSION['pdf_page_count'],
            $_SESSION['pdf_plan'],
            $_SESSION['pdf_text'],
            $_SESSION['pdf_processed']
        );

        Response::json(['success' => true, 'message' => 'PDF session cleared.']);
    }
}
