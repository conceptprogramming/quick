<?php
namespace Services;

class MailService
{

    private string $host;
    private int $port;
    private string $user;
    private string $pass;
    private string $fromName;
    private string $encryption;

    public function __construct()
    {
        $this->host = $_ENV['MAIL_HOST'] ?? 'smtp.hostinger.com';
        $this->port = (int) ($_ENV['MAIL_PORT'] ?? 465);
        $this->user = $_ENV['MAIL_USER'] ?? '';
        $this->pass = $_ENV['MAIL_PASS'] ?? '';
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? 'QuickChatPDF';
        $this->encryption = $_ENV['MAIL_ENCRYPTION'] ?? 'ssl';
    }

    // ── Send OTP Email ────────────────────────────────────────
    public function sendOTP(string $to, string $otp): bool
    {
        $subject = 'Your QuickChatPDF Login Code';
        $body = $this->otpTemplate($otp);
        return $this->send($to, $subject, $body);
    }

    public function sendSubscriptionReceipt(
        string $to,
        string $planName,
        int $creditsAdded,
        float $amount,
        string $currency,
        ?string $startsAt = null,
        ?string $endsAt = null
    ): bool
    {
        $subject = 'Your QuickChatPDF Subscription Is Active';
        $body = $this->purchaseTemplate(
            'Subscription',
            'Subscription Confirmed',
            "Your {$planName} plan is now active.",
            [
                'Plan' => $planName,
                'Amount' => strtoupper($currency) . ' ' . number_format($amount, 2),
                'Credits Added' => number_format($creditsAdded) . ' credits',
                'Starts' => $this->formatDisplayDate($startsAt),
                'Ends' => $this->formatDisplayDate($endsAt),
                'Access' => 'Your remaining wallet credits stay available.',
            ],
            'Manage your plan anytime from your QuickChatPDF account.'
        );
        return $this->send($to, $subject, $body);
    }

    public function sendTopupReceipt(string $to, string $packName, string $unitsLabel, float $amount, string $currency): bool
    {
        $subject = 'Your QuickChatPDF Add-On Purchase Is Ready';
        $body = $this->purchaseTemplate(
            'Add-On',
            'Add-On Purchase Confirmed',
            "Your {$packName} purchase has been applied to your account.",
            [
                'Pack' => $packName,
                'Amount' => strtoupper($currency) . ' ' . number_format($amount, 2),
                'Added' => $unitsLabel,
                'Expiry' => str_contains(strtolower($unitsLabel), 'credits') ? 'Does not expire' : 'Valid for the current month',
            ],
            'Open QuickChatPDF to start using your purchase right away.'
        );
        return $this->send($to, $subject, $body);
    }

    // ── Core SMTP Sender ──────────────────────────────────────
    private function send(string $to, string $subject, string $htmlBody): bool
    {
        $protocol = $this->encryption === 'ssl' ? 'ssl' : 'tcp';
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);

        $socket = @stream_socket_client(
            "{$protocol}://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            10,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$socket)
            return false;

        $boundary = md5(uniqid());

        $this->smtpSend($socket, null);                               // greeting
        $this->smtpSend($socket, "EHLO " . gethostname());
        $this->smtpSend($socket, "AUTH LOGIN");
        $this->smtpSend($socket, base64_encode($this->user));
        $this->smtpSend($socket, base64_encode($this->pass));
        $this->smtpSend($socket, "MAIL FROM:<{$this->user}>");
        $this->smtpSend($socket, "RCPT TO:<{$to}>");
        $this->smtpSend($socket, "DATA");

        $headers = "From: {$this->fromName} <{$this->user}>\r\n";
        $headers .= "To: {$to}\r\n";
        $headers .= "Subject: {$subject}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        fwrite($socket, $headers . "\r\n" . $htmlBody . "\r\n.\r\n");
        $this->smtpRead($socket);
        $this->smtpSend($socket, "QUIT");
        fclose($socket);

        return true;
    }

    private function smtpSend($socket, ?string $cmd): void
    {
        if ($cmd !== null) {
            fwrite($socket, $cmd . "\r\n");
        }
        $this->smtpRead($socket);
    }

    private function smtpRead($socket): string
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ')
                break;
        }
        return $response;
    }

    // ── OTP Email Template ────────────────────────────────────
    private function otpTemplate(string $otp): string
    {
        $year = date('Y');

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
        </head>
        <body style="margin:0;padding:0;background:#f3f6fb;font-family:'Inter',Arial,sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f6fb;padding:40px 20px;">
                <tr><td align="center">
                    <table width="560" cellpadding="0" cellspacing="0"
                        style="background:#ffffff;border-radius:24px;border:1px solid #e2e8f0;overflow:hidden;max-width:560px;width:100%;box-shadow:0 20px 60px rgba(15,23,42,.08);">

                        <!-- Header -->
                        <tr>
                            <td style="background:linear-gradient(135deg,#6c47ff,#b05cff);padding:32px;text-align:center;">
                                <div style="font-size:22px;font-weight:800;color:#fff;letter-spacing:-0.5px;">
                                    QuickChatPDF
                                </div>
                            </td>
                        </tr>

                        <!-- Body -->
                        <tr>
                            <td style="padding:40px 36px 28px;">
                                <div style="display:inline-block;padding:8px 14px;border-radius:999px;background:#eef2ff;color:#6c47ff;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;margin-bottom:16px;">Secure Login</div>
                                <h2 style="color:#0f172a;font-size:20px;margin:0 0 10px;">Your QuickChatPDF login code</h2>
                                <p style="color:#475569;font-size:14px;margin:0 0 28px;line-height:1.7;">
                                    Use this one-time code to sign in. It stays valid for <strong style="color:#6c47ff;">10 minutes</strong>.
                                </p>

                                <!-- OTP Box -->
                                <div style="background:#f8fafc;border:2px solid #c4b5fd;border-radius:18px;
                                            text-align:center;padding:24px;margin-bottom:28px;">
                                    <div style="font-size:42px;font-weight:800;letter-spacing:12px;
                                                color:#0f172a;font-family:monospace;">
                                        {$otp}
                                    </div>
                                </div>

                                <table width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #e2e8f0;">
                                    <tr>
                                        <td style="padding:16px 0;color:#64748b;font-size:14px;border-bottom:1px solid #e2e8f0;">Purpose</td>
                                        <td style="padding:16px 0;color:#0f172a;font-size:14px;font-weight:700;text-align:right;border-bottom:1px solid #e2e8f0;">Passwordless sign-in</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:16px 0;color:#64748b;font-size:14px;border-bottom:1px solid #e2e8f0;">Expires</td>
                                        <td style="padding:16px 0;color:#0f172a;font-size:14px;font-weight:700;text-align:right;border-bottom:1px solid #e2e8f0;">In 10 minutes</td>
                                    </tr>
                                </table>

                                <p style="color:#475569;font-size:13px;margin:24px 0 0;line-height:1.7;">
                                    If you did not request this code, you can safely ignore this email. Your account stays protected.
                                </p>
                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td style="padding:20px 36px;border-top:1px solid #e2e8f0;text-align:center;background:#f8fafc;">
                                <p style="color:#64748b;font-size:12px;margin:0;">&copy; {$year} QuickChatPDF · support@quickchatpdf.com</p>
                            </td>
                        </tr>

                    </table>
                </td></tr>
            </table>
        </body>
        </html>
        HTML;
    }

    private function purchaseTemplate(string $eyebrow, string $title, string $intro, array $items, string $footerNote): string
    {
        $rows = '';
        foreach ($items as $label => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $rows .= '<tr>'
                . '<td style="padding:16px 0;color:#64748b;font-size:14px;border-bottom:1px solid #e2e8f0;">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td style="padding:16px 0;color:#0f172a;font-size:14px;font-weight:700;text-align:right;border-bottom:1px solid #e2e8f0;">' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '</td>'
                . '</tr>';
        }

        $year = date('Y');

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
        </head>
        <body style="margin:0;padding:0;background:#f3f6fb;font-family:'Inter',Arial,sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f6fb;padding:40px 20px;">
                <tr><td align="center">
                    <table width="560" cellpadding="0" cellspacing="0"
                        style="background:#ffffff;border-radius:24px;border:1px solid #e2e8f0;overflow:hidden;max-width:560px;width:100%;box-shadow:0 20px 60px rgba(15,23,42,.08);">
                        <tr>
                            <td style="background:linear-gradient(135deg,#6c47ff,#b05cff);padding:32px;text-align:center;">
                                <div style="font-size:22px;font-weight:800;color:#fff;letter-spacing:-0.5px;">QuickChatPDF</div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:40px 36px 28px;">
                                <div style="display:inline-block;padding:8px 14px;border-radius:999px;background:#eef2ff;color:#6c47ff;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;margin-bottom:16px;">{$eyebrow}</div>
                                <h2 style="color:#0f172a;font-size:20px;margin:0 0 10px;">{$title}</h2>
                                <p style="color:#475569;font-size:14px;margin:0 0 24px;line-height:1.7;">{$intro}</p>
                                <table width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #e2e8f0;">
                                    {$rows}
                                </table>
                                <p style="color:#475569;font-size:13px;margin:24px 0 0;line-height:1.7;">
                                    {$footerNote}
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:20px 36px;border-top:1px solid #e2e8f0;text-align:center;background:#f8fafc;">
                                <p style="color:#64748b;font-size:12px;margin:0;">&copy; {$year} QuickChatPDF · support@quickchatpdf.com</p>
                            </td>
                        </tr>
                    </table>
                </td></tr>
            </table>
        </body>
        </html>
        HTML;
    }

    private function formatDisplayDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        $timestamp = strtotime($date);
        if (!$timestamp) {
            return $date;
        }

        return date('M d, Y · g:i A', $timestamp) . ' UTC';
    }
}
