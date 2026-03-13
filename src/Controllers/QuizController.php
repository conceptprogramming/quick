<?php
namespace Controllers;

use Database;
use Core\Session;
use Core\Request;
use Core\RateLimiter;
use Services\CreditService;
use Services\AIService;

class QuizController
{

    private Session $session;
    private CreditService $creditService;
    private AIService $aiService;

    public function __construct()
    {
        $this->session = new Session();
        $this->creditService = new CreditService();
        $this->aiService = new AIService();
    }

    public function index(): void
    {
        if (empty($_SESSION['pdf_processed'])) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }

        $db = Database::getInstance();
        $st = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $st->execute(['id' => $this->session->userId()]);
        $user = $st->fetch();

        require __DIR__ . '/../../views/quiz/index.php';
    }

    public function mcq(): void
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

            $count = min((int) ($_POST['count'] ?? 5), 20);
            $db = Database::getInstance();
            $st = $db->prepare("SELECT plan FROM users WHERE id = :id LIMIT 1");
            $st->execute(['id' => $userId]);
            $user = $st->fetch();
            $plan = $user['plan'] ?? 'free';

            if (!$this->creditService->hasCredits($userId, 'quiz')) {
                echo json_encode(['success' => false, 'message' => 'Not enough credits.']);
                exit;
            }
            if (!$this->creditService->withinPlanLimit($userId, $plan, 'quizzes')) {
                echo json_encode(['success' => false, 'message' => 'Monthly quiz limit reached.']);
                exit;
            }
            if (!RateLimiter::check(Request::ip(), 'ai_request')) {
                echo json_encode(['success' => false, 'message' => 'Too many requests.']);
                exit;
            }

            $quiz = $this->aiService->generateMCQ($_SESSION['pdf_text'], $count);
            if (!$quiz) {
                echo json_encode(['success' => false, 'message' => 'AI failed to generate quiz.']);
                exit;
            }

            $this->creditService->deduct($userId, 'quiz');
            $this->creditService->incrementUsage($userId, 'quizzes');
            $_SESSION['last_quiz'] = ['type' => 'mcq', 'data' => $quiz];

            echo json_encode(['success' => true, 'data' => ['quiz' => $quiz, 'count' => count($quiz), 'type' => 'mcq']]);
            exit;
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function trueFalse(): void
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

            $count = min((int) ($_POST['count'] ?? 5), 20);
            $db = Database::getInstance();
            $st = $db->prepare("SELECT plan FROM users WHERE id = :id LIMIT 1");
            $st->execute(['id' => $userId]);
            $user = $st->fetch();
            $plan = $user['plan'] ?? 'free';

            if (!$this->creditService->hasCredits($userId, 'quiz')) {
                echo json_encode(['success' => false, 'message' => 'Not enough credits.']);
                exit;
            }
            if (!$this->creditService->withinPlanLimit($userId, $plan, 'quizzes')) {
                echo json_encode(['success' => false, 'message' => 'Monthly quiz limit reached.']);
                exit;
            }
            if (!RateLimiter::check(Request::ip(), 'ai_request')) {
                echo json_encode(['success' => false, 'message' => 'Too many requests.']);
                exit;
            }

            $quiz = $this->aiService->generateTrueFalse($_SESSION['pdf_text'], $count);
            if (!$quiz) {
                echo json_encode(['success' => false, 'message' => 'AI failed to generate quiz.']);
                exit;
            }

            $this->creditService->deduct($userId, 'quiz');
            $this->creditService->incrementUsage($userId, 'quizzes');
            $_SESSION['last_quiz'] = ['type' => 'truefalse', 'data' => $quiz];

            echo json_encode(['success' => true, 'data' => ['quiz' => $quiz, 'count' => count($quiz), 'type' => 'truefalse']]);
            exit;
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}
