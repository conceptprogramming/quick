<?php
namespace Core;

use Database;

class Session
{

    // Check if user has valid active session
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id'], $_SESSION['token'])
            && $this->validateToken($_SESSION['token']);
    }

    // Get logged in user id
    public function userId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    // Create session after OTP verified
    public function create(int $userId): void
    {
        $token = bin2hex(random_bytes(64));
        $expiresAt = gmdate('Y-m-d H:i:s', time() + SESSION_LIFETIME);
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $db = Database::getInstance();
        $st = $db->prepare("
            INSERT INTO user_sessions (user_id, token, ip_address, user_agent, expires_at)
            VALUES (:user_id, :token, :ip, :ua, :expires_at)
        ");
        $st->execute([
            'user_id' => $userId,
            'token' => $token,
            'ip' => $ip,
            'ua' => $ua,
            'expires_at' => $expiresAt,
        ]);

        $_SESSION['user_id'] = $userId;
        $_SESSION['token'] = $token;
    }

    // Validate token exists in DB and not expired
    private function validateToken(string $token): bool
    {
        $db = Database::getInstance();
        $st = $db->prepare("
            SELECT id FROM user_sessions
            WHERE token = :token
              AND expires_at > UTC_TIMESTAMP()
            LIMIT 1
        ");
        $st->execute(['token' => $token]);

        if ($st->fetch()) {
            // Update last active
            $db->prepare("
                UPDATE user_sessions SET last_active = UTC_TIMESTAMP()
                WHERE token = :token
            ")->execute(['token' => $token]);
            return true;
        }
        return false;
    }

    // Destroy current session
    public function destroy(): void
    {
        if (isset($_SESSION['token'])) {
            $db = Database::getInstance();
            $db->prepare("DELETE FROM user_sessions WHERE token = :token")
                ->execute(['token' => $_SESSION['token']]);
        }
        $_SESSION = [];
        session_destroy();
    }
}
