<?php
namespace Services;

use Database;
use Core\RateLimiter;
use Core\Request;

class AuthService
{
    // ── Send OTP ──────────────────────────────────────────────
    public function sendOTP(string $email): array
    {
        if (RateLimiter::isBlocked(Request::ip(), 'otp_verify_block')) {
            return ['success' => false, 'message' => 'Too many wrong OTP attempts. This IP is blocked for 24 hours.'];
        }

        if (!RateLimiter::check(Request::ip(), 'otp_send')) {
            return ['success' => false, 'message' => 'Too many OTP requests. Please wait 10 minutes.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address.'];
        }

        $email = strtolower(trim($email));
        $db    = Database::getInstance();

        // Create user if not exists
        $st = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $st->execute(['email' => $email]);
        if (!$st->fetch()) {
            $db->prepare("INSERT INTO users (email, plan, credits) VALUES (:email, 'free', :credits)")
                ->execute(['email' => $email, 'credits' => PLANS['free']['monthly_credits']]);
        }

        // Invalidate all old unused OTPs for this email
        $db->prepare("UPDATE otps SET used = 1 WHERE email = :email AND used = 0")
            ->execute(['email' => $email]);

        // Generate new OTP
        $otp       = str_pad(random_int(0, 999999), OTP_LENGTH, '0', STR_PAD_LEFT);
        $expiresAt = gmdate('Y-m-d H:i:s', time() + OTP_EXPIRY);

        $db->prepare("
            INSERT INTO otps (email, otp, expires_at)
            VALUES (:email, :otp, :expires_at)
        ")->execute(['email' => $email, 'otp' => $otp, 'expires_at' => $expiresAt]);

        // Send email
        $sent = (new MailService())->sendOTP($email, $otp);
        if (!$sent) {
            return ['success' => false, 'message' => 'Failed to send OTP email. Please try again.'];
        }

        return ['success' => true, 'message' => 'OTP sent to your email.'];
    }

    // ── Verify OTP ────────────────────────────────────────────
    public function verifyOTP(string $email, string $otp): array
    {
        $ip = Request::ip();
        if (RateLimiter::isBlocked($ip, 'otp_verify_block')) {
            return ['success' => false, 'message' => 'Too many wrong OTP attempts. This IP is blocked for 24 hours.'];
        }

        $email = strtolower(trim($email));
        $otp   = trim($otp);
        $db    = Database::getInstance();

        $st = $db->prepare("
            SELECT id, otp, attempts, expires_at
            FROM otps
            WHERE email = :email
              AND used  = 0
            ORDER BY id DESC
            LIMIT 1
        ");
        $st->execute(['email' => $email]);
        $row = $st->fetch();

        if (!$row) {
            return ['success' => false, 'message' => 'OTP has expired. Please request a new one.'];
        }

        if (strtotime($row['expires_at']) <= time()) {
            $db->prepare("UPDATE otps SET used = 1 WHERE id = :id")
                ->execute(['id' => $row['id']]);
            return ['success' => false, 'message' => 'OTP has expired. Please request a new one.'];
        }

        if ((int) $row['attempts'] >= OTP_MAX_ATTEMPTS) {
            $db->prepare("UPDATE otps SET used = 1 WHERE id = :id")
                ->execute(['id' => $row['id']]);
            RateLimiter::block($ip, 'otp_verify_block');
            return ['success' => false, 'message' => 'Too many wrong OTP attempts. This IP is blocked for 24 hours.'];
        }

        if (!hash_equals($row['otp'], $otp)) {
            $db->prepare("
                UPDATE otps
                SET attempts = attempts + 1,
                    expires_at = :expires_at
                WHERE id = :id
            ")->execute([
                'id' => $row['id'],
                'expires_at' => $row['expires_at'],
            ]);

            $newAttempts = (int) $row['attempts'] + 1;
            $remaining   = OTP_MAX_ATTEMPTS - $newAttempts;

            if ($remaining <= 0) {
                $db->prepare("UPDATE otps SET used = 1 WHERE id = :id")
                    ->execute(['id' => $row['id']]);
                RateLimiter::block($ip, 'otp_verify_block');
                return ['success' => false, 'message' => 'Too many wrong OTP attempts. This IP is blocked for 24 hours.'];
            }

            $word = $remaining === 1 ? 'attempt' : 'attempts';
            return ['success' => false, 'message' => "Incorrect OTP. {$remaining} {$word} remaining."];
        }

        $db->prepare("UPDATE otps SET used = 1 WHERE id = :id")
            ->execute(['id' => $row['id']]);

        $st = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $st->execute(['email' => $email]);
        $user = $st->fetch();

        return ['success' => true, 'user_id' => $user['id']];
    }


    // ── Get user by ID ────────────────────────────────────────
    public function getUserById(int $id): ?array
    {
        $db = Database::getInstance();
        $st = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $st->execute(['id' => $id]);
        return $st->fetch() ?: null;
    }
}
