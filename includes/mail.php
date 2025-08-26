<?php
/**
 * Email Sending Service
 *
 * A simple wrapper for PHP's mail() function for sending application emails.
 */
require_once __DIR__ . '/config.php';

/**
 * Sends an email.
 *
 * @param string $to The recipient's email address.
 * @param string $subject The subject of the email.
 * @param string $message The HTML or text content of the email.
 * @param array $headers Additional headers for the email.
 * @return bool The result of the mail() function.
 */
function send_email(string $to, string $subject, string $message, array $headers = []): bool {
    $default_headers = [
        'MIME-Version' => '1.0',
        'Content-type' => 'text/html; charset=UTF-8',
        'From' => '"' . EMAIL_FROM_NAME . '" <' . EMAIL_FROM . '>',
        'Reply-To' => EMAIL_FROM,
        'X-Mailer' => 'PHP/' . phpversion()
    ];

    $final_headers = array_merge($default_headers, $headers);

    // Implode headers array to a string
    $header_string = '';
    foreach ($final_headers as $key => $value) {
        $header_string .= $key . ': ' . $value . "\r\n";
    }

    // mail() can be unreliable on local dev environments (like XAMPP) without configuration.
    // It is expected to work on live servers like Hostinger.

    // Simulate email sending if simulation is forced by config.
    if (defined('FORCE_EMAIL_SIMULATION') && FORCE_EMAIL_SIMULATION === true) {
        error_log("--- EMAIL SIMULATION (Forced by Config) ---");
        error_log("To: $to");
        error_log("Subject: $subject");
        error_log("Body: $message");
        error_log("--- END EMAIL SIMULATION ---");
        return true; // Simulate success
    }

    return mail($to, $subject, $message, $header_string);
}

/**
 * Sends a pre-formatted OTP email to a user.
 *
 * @param string $user_email The recipient's email address.
 * @param string $otp The One-Time Password to send.
 * @return bool The result of the mail() function.
 */
function send_otp_email(string $user_email, string $otp): bool {
    $subject = 'Your Verification Code for ' . SITE_NAME;

    // Using a simple, clean HTML template for the email.
    $message = '
    <html>
    <head>
        <title>' . $subject . '</title>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; }
            .container { max-width: 600px; margin: 20px auto; padding: 20px; background-color: #fff; border: 1px solid #ddd; }
            .header { background-color: #002147; color: #FFD700; padding: 10px; text-align: center; font-size: 24px; }
            .content { padding: 30px; text-align: center; }
            .otp-code { font-size: 36px; font-weight: bold; color: #006400; letter-spacing: 5px; margin: 20px 0; }
            .footer { font-size: 12px; color: #777; text-align: center; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">' . SITE_NAME . '</div>
            <div class="content">
                <p>Hello,</p>
                <p>Your one-time password (OTP) for verification is:</p>
                <div class="otp-code">' . e($otp) . '</div>
                <p>This code is valid for ' . (OTP_LIFETIME / 60) . ' minutes. Please do not share it with anyone.</p>
            </div>
            <div class="footer">
                <p>If you did not request this, please ignore this email.</p>
            </div>
        </div>
    </body>
    </html>';

    return send_email($user_email, $subject, $message);
}
