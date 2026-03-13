<?php
namespace Controllers;

use Database;
use Core\Session;

class SummaryController
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

        $db = \Database::getInstance();
        $st = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $st->execute(['id' => $this->session->userId()]);
        $user = $st->fetch();

        require __DIR__ . '/../../views/summary/index.php';
    }
}
