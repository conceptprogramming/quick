<?php
namespace Controllers;

use Core\Session;
use Core\Request;
use Core\Response;
use Services\AuthService;

class AuthController
{
    private AuthService $auth;
    private Session     $session;

    public function __construct()
    {
        $this->auth    = new AuthService();
        $this->session = new Session();
    }

    // ── Landing page ──────────────────────────────────────────
    public function landing(): void
    {
        if ($this->session->isAuthenticated()) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
        require __DIR__ . '/../../views/landing/index.php';
    }

    // ── Login form ────────────────────────────────────────────
    public function loginForm(): void
    {
        if ($this->session->isAuthenticated()) {
            Response::redirect('dashboard');
        }
        require __DIR__ . '/../../views/auth/login.php';
    }

    // ── Send OTP (POST) ───────────────────────────────────────
    public function sendOTP(): void
    {
        if ($this->session->isAuthenticated()) {
            Response::redirect('dashboard');
        }

        $email = trim(Request::post('email', ''));

        if (empty($email)) {
            $_SESSION['auth_error'] = 'Please enter your email address.';
            Response::redirect('login');
        }

        $result = $this->auth->sendOTP($email);

        if (!$result['success']) {
            $_SESSION['auth_error'] = $result['message'];
            Response::redirect('login');
        }

        $_SESSION['otp_email'] = strtolower(trim($email));
        unset($_SESSION['auth_error']);
        Response::redirect('verify');
    }

    // ── Verify OTP form ───────────────────────────────────────
    public function verifyForm(): void
    {
        if ($this->session->isAuthenticated()) {
            Response::redirect('dashboard');
        }

        if (empty($_SESSION['otp_email'])) {
            Response::redirect('login');
        }

        require __DIR__ . '/../../views/auth/verify.php';
    }


    // ── Verify OTP (POST) ─────────────────────────────────────
    public function verifyOTP(): void
    {
        if (empty($_SESSION['otp_email'])) {
            Response::redirect('login');
        }

        $email = $_SESSION['otp_email'];
        $otp   = Request::post('otp', '');

        if (empty($otp)) {
            $digits = '';
            for ($i = 1; $i <= 6; $i++) {
                $digits .= Request::post("otp_{$i}", '');
            }
            $otp = $digits;
        }

        if (strlen($otp) !== 6) {
            $_SESSION['auth_error'] = 'Please enter the full 6-digit OTP.';
            Response::redirect('verify');
        }

        $result = $this->auth->verifyOTP($email, $otp);

        if (!$result['success']) {
            $_SESSION['auth_error'] = $result['message'];
            Response::redirect('verify');
        }

        // ✅ Success
        unset($_SESSION['otp_email'], $_SESSION['auth_error']);
        $this->session->create($result['user_id']);
        Response::redirect('dashboard');
    }

    // ── Logout ────────────────────────────────────────────────
    public function logout(): void
    {
        $this->session->destroy();
        Response::redirect('login');
    }
}
