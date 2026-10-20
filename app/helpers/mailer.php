<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Sends the password-reset email via Resend. Returns true/false and never
 * throws -- failures are logged, not shown to the user (callers always show
 * the same generic "if that email is registered..." message either way, to
 * avoid revealing whether an email address exists in the system).
 *
 * @param string $toEmail
 * @param string $toName
 * @param string $resetLink
 * @return bool
 */
function sendPasswordResetEmail($toEmail, $toName, $resetLink){
    try{
        $apiKey = $_ENV['RESEND_API_KEY'] ?? getenv('RESEND_API_KEY');
        if(empty($apiKey)){
            error_log('sendPasswordResetEmail: RESEND_API_KEY is not set.');
            return false;
        }

        $fromAddress = $_ENV['MAIL_FROM_ADDRESS'] ?? getenv('MAIL_FROM_ADDRESS') ?: 'onboarding@resend.dev';
        $fromName = $_ENV['MAIL_FROM_NAME'] ?? getenv('MAIL_FROM_NAME') ?: 'LIMSACS';

        $resend = Resend::client($apiKey);

        $safeName = htmlspecialchars($toName, ENT_QUOTES);
        $safeLink = htmlspecialchars($resetLink, ENT_QUOTES);

        $html = "
            <div style=\"font-family: Arial, sans-serif; max-width: 480px; margin: 0 auto; color:#1a1a1a;\">
                <h2 style=\"color:#696cff;\">Reset your LIMSACS password</h2>
                <p>Hi {$safeName},</p>
                <p>We received a request to reset the password for your LIMSACS account. Click the button below to choose a new password. This link expires in 1 hour.</p>
                <p style=\"text-align:center; margin: 24px 0;\">
                    <a href=\"{$safeLink}\" style=\"background:#696cff; color:#fff; padding:12px 24px; border-radius:6px; text-decoration:none; display:inline-block;\">Reset Password</a>
                </p>
                <p>If the button doesn't work, copy and paste this link into your browser:<br>
                <a href=\"{$safeLink}\">{$safeLink}</a></p>
                <p>If you didn't request this, you can safely ignore this email -- your password won't be changed.</p>
                <p style=\"color:#888; font-size:12px; margin-top:32px;\">Aurora Central School &middot; LIMSACS</p>
            </div>
        ";

        $text = "Hi {$toName},\n\n"
            . "We received a request to reset the password for your LIMSACS account.\n"
            . "Open this link to choose a new password (expires in 1 hour):\n\n"
            . $resetLink . "\n\n"
            . "If you didn't request this, you can safely ignore this email.\n\n"
            . "Aurora Central School - LIMSACS";

        $resend->emails->send([
            'from' => "{$fromName} <{$fromAddress}>",
            'to' => [$toEmail],
            'subject' => 'Reset your LIMSACS password',
            'html' => $html,
            'text' => $text,
        ]);

        return true;
    }catch(Throwable $e){
        error_log('sendPasswordResetEmail failed: ' . $e->getMessage());
        return false;
    }
}
