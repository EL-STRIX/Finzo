<?php
/**
 * Finzo Banking System — PHPMailer Configuration
 * SMTP email wrapper using PHPMailer library.
 * Version 1.0 — April 2026
 */

require_once __DIR__ . '/config.php';

// PHPMailer autoload — adjust path based on installation method
// If using Composer: require_once dirname(__DIR__) . '/vendor/autoload.php';
// If manual installation, require each file:
$phpmailer_path = dirname(__DIR__) . '/vendor/phpmailer/';

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
} elseif (file_exists($phpmailer_path . 'PHPMailer.php')) {
    require_once $phpmailer_path . 'PHPMailer.php';
    require_once $phpmailer_path . 'SMTP.php';
    require_once $phpmailer_path . 'Exception.php';
} else {
    // PHPMailer not found — define a fallback that logs errors
    function send_email($to_email, $to_name, $subject, $html_body) {
        error_log("PHPMailer not installed. Email to {$to_email} with subject '{$subject}' was not sent.");
        return ['success' => false, 'message' => 'Email service not configured. PHPMailer library not found.'];
    }
    return;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send an email using PHPMailer with SMTP.
 *
 * @param string $to_email Recipient email address
 * @param string $to_name Recipient name
 * @param string $subject Email subject
 * @param string $html_body HTML email body content
 * @return array ['success' => bool, 'message' => string]
 */
if (!function_exists('send_email')) {
    function send_email($to_email, $to_name, $subject, $html_body) {
        // In testing mode, do not send actual emails
        if (defined('FINZO_TESTING') && FINZO_TESTING) {
            return ['success' => true, 'message' => 'Email sent successfully (mocked).'];
        }

        $mail = new PHPMailer(true);

        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Timeout    = 3;
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_ENCRYPTION;
            $mail->Port       = SMTP_PORT;

            // Sender and recipient
            $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
            $mail->addAddress($to_email, $to_name);

            // Email content
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $html_body;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html_body));

            $mail->send();

            return ['success' => true, 'message' => 'Email sent successfully'];

        } catch (Exception $e) {
            error_log("Finzo Mailer Error: " . $mail->ErrorInfo);
            return ['success' => false, 'message' => 'Email could not be sent. Error: ' . $mail->ErrorInfo];
        }
    }
}

/**
 * Load an email template file and replace placeholders.
 *
 * @param string $template_name Template filename without extension (e.g., 'welcome-email')
 * @param array $variables Associative array of placeholder => value pairs
 * @return string Rendered HTML email content
 */
function load_email_template($template_name, $variables = []) {
    $template_path = dirname(__DIR__) . '/resources/views/emails/' . $template_name . '.php';

    if (!file_exists($template_path)) {
        error_log("Email template not found: {$template_path}");
        return '';
    }

    $html = file_get_contents($template_path);

    // Replace all {{PLACEHOLDER}} patterns with values
    foreach ($variables as $key => $value) {
        $html = str_replace('{{' . strtoupper($key) . '}}', htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), $html);
    }

    return $html;
}

/**
 * Send a welcome email to a newly approved customer.
 *
 * @param PDO $pdo Database connection (unused here but kept for consistency)
 * @param string $email Customer email
 * @param string $name Customer full name
 * @param string $account_no Assigned account number
 * @return array Result from send_email
 */
function send_welcome_email($pdo, $email, $name, $account_no) {
    $html = load_email_template('welcome-email', [
        'name' => $name,
        'account_no' => $account_no,
        'app_url' => APP_URL
    ]);

    if (empty($html)) {
        $html = "<h2>Welcome to Finzo, {$name}!</h2>"
              . "<p>Your account has been approved.</p>"
              . "<p>Your Account Number: <strong>{$account_no}</strong></p>"
              . "<p>You can now login and start banking at <a href='" . APP_URL . "'>" . APP_URL . "</a></p>";
    }

    return send_email($email, $name, 'Welcome to Finzo — Your Account is Ready!', $html);
}

/**
 * Send an under-review notification email.
 *
 * @param string $email Customer email
 * @param string $name Customer full name
 * @return array Result from send_email
 */
function send_under_review_email($email, $name) {
    $html = load_email_template('under-review-email', [
        'name' => $name,
        'app_url' => APP_URL
    ]);

    if (empty($html)) {
        $html = "<h2>Hi {$name},</h2>"
              . "<p>Your Finzo application has been received and is currently under review.</p>"
              . "<p>We will notify you once it has been processed. This usually takes 1-2 business days.</p>";
    }

    return send_email($email, $name, 'Finzo — Your Application is Under Review', $html);
}

/**
 * Send an approval notification email with account number.
 *
 * @param string $email Customer email
 * @param string $name Customer full name
 * @param string $account_no Assigned account number
 * @return array Result from send_email
 */
function send_approved_email($email, $name, $account_no) {
    $html = load_email_template('approved-email', [
        'name' => $name,
        'account_no' => $account_no,
        'app_url' => APP_URL
    ]);

    if (empty($html)) {
        $html = "<h2>Congratulations, {$name}!</h2>"
              . "<p>Your KYC verification has been approved.</p>"
              . "<p>Your Account Number: <strong>{$account_no}</strong></p>"
              . "<p>Login now to start banking: <a href='" . APP_URL . "'>" . APP_URL . "</a></p>";
    }

    return send_email($email, $name, 'Finzo — Your Account Has Been Approved!', $html);
}

/**
 * Send a rejection notification email with reason.
 *
 * @param string $email Customer email
 * @param string $name Customer full name
 * @param string $reason Rejection reason
 * @return array Result from send_email
 */
function send_rejected_email($email, $name, $reason) {
    $html = load_email_template('rejected-email', [
        'name' => $name,
        'reason' => $reason,
        'app_url' => APP_URL
    ]);

    if (empty($html)) {
        $html = "<h2>Hi {$name},</h2>"
              . "<p>Unfortunately, your Finzo KYC application has been rejected.</p>"
              . "<p><strong>Reason:</strong> {$reason}</p>"
              . "<p>You can update your profile and resubmit for review at <a href='" . APP_URL . "'>" . APP_URL . "</a></p>";
    }

    return send_email($email, $name, 'Finzo — Application Update', $html);
}

/**
 * Send a password reset email with token link.
 *
 * @param string $email Customer email
 * @param string $name Customer full name
 * @param string $token Reset token
 * @return array Result from send_email
 */
function send_password_reset_email($email, $name, $token) {
    $reset_link = APP_URL . '/views/auth/reset-password.html?token=' . $token;

    $html = "<h2>Hi {$name},</h2>"
          . "<p>We received a request to reset your Finzo account password.</p>"
          . "<p>Click the link below to reset your password:</p>"
          . "<p><a href='{$reset_link}' style='display:inline-block;padding:12px 24px;background:#1a237e;color:#fff;text-decoration:none;border-radius:6px;'>Reset Password</a></p>"
          . "<p>This link will expire in 1 hour.</p>"
          . "<p>If you did not request this, please ignore this email.</p>";

    return send_email($email, $name, 'Finzo — Password Reset Request', $html);
}
