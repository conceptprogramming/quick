<?php
namespace Controllers;

use Core\Session;

class QAController
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function index(): void
    {
        if (empty($_SESSION['pdf_processed'])) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }

        // Load user for navbar
        $db = \Database::getInstance();
        $st = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $st->execute(['id' => $this->session->userId()]);
        $user = $st->fetch();

        require __DIR__ . '/../../views/chat/index.php';  // or summary/qa
    }

}
