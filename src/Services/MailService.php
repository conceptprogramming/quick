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
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
        </head>
        <body style="margin:0;padding:0;background:#0a0d14;font-family:'Inter',Arial,sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#0a0d14;padding:40px 20px;">
                <tr><td align="center">
                    <table width="560" cellpadding="0" cellspacing="0"
                        style="background:#131929;border-radius:16px;border:1px solid rgba(255,255,255,0.07);overflow:hidden;max-width:560px;width:100%;">

                        <!-- Header -->
                        <tr>
                            <td style="background:linear-gradient(135deg,#7c5cfc,#c471ed);padding:32px;text-align:center;">
                                <div style="font-size:22px;font-weight:800;color:#fff;letter-spacing:-0.5px;">
                                    ⚡ QuickChatPDF
                                </div>
                            </td>
                        </tr>

                        <!-- Body -->
                        <tr>
                            <td style="padding:40px 36px;">
                                <h2 style="color:#f1f5f9;font-size:20px;margin:0 0 10px;">Your Login Code</h2>
                                <p style="color:#64748b;font-size:14px;margin:0 0 30px;line-height:1.6;">
                                    Use the code below to log in to your QuickChatPDF account.
                                    This code expires in <strong style="color:#a78bfa;">10 minutes</strong>.
                                </p>

                                <!-- OTP Box -->
                                <div style="background:#0a0d14;border:2px solid #7c5cfc;border-radius:12px;
                                            text-align:center;padding:24px;margin-bottom:30px;">
                                    <div style="font-size:42px;font-weight:800;letter-spacing:12px;
                                                color:#fff;font-family:monospace;">
                                        {$otp}
                                    </div>
                                </div>

                                <p style="color:#64748b;font-size:13px;margin:0;line-height:1.6;">
                                    If you didn't request this code, you can safely ignore this email.
                                    Your account is secure.
                                </p>
                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td style="padding:20px 36px;border-top:1px solid rgba(255,255,255,0.07);text-align:center;">
                                <p style="color:#334155;font-size:12px;margin:0;">
                                    &copy; <?= date('Y') ?> QuickChatPDF &nbsp;·&nbsp;
                                    <span style="color:#475569;">Private by design. Zero document retention.</span>
                                </p>
                            </td>
                        </tr>

                    </table>
                </td></tr>
            </table>
        </body>
        </html>
        HTML;
    }
}
