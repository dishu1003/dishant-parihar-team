<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/mail.php';

// 1. Verify Request Method
verify_request_method('POST');

// 2. Start session and verify CSRF
start_secure_session();
CSRF::verifyRequest();

// 3. Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to request an OTP.']);
    exit();
}

// 4. Rate Limiting
$now = time();
$last_otp_request = $_SESSION['last_otp_request'] ?? 0;

if (($now - $last_otp_request) < 60) { // 60-second cooldown
    http_response_code(429); // Too Many Requests
    echo json_encode(['success' => false, 'message' => 'Please wait a minute before requesting a new OTP.']);
    exit();
}

// 5. Generate and send new OTP
$user_id = get_current_user_id();
$user = find_user_by_id($user_id);

if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit();
}

try {
    $otp = generate_otp($user['id']);
    if (send_otp_email($user['email'], $otp)) {
        $_SESSION['last_otp_request'] = $now; // Update rate limit timestamp
        echo json_encode([
            'success' => true,
            'message' => 'A new OTP has been sent to your email.'
        ]);
    } else {
        error_log("Failed to re-send OTP email to {$user['email']}.");
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Could not send OTP. Please contact support.']);
    }
} catch (Exception $e) {
    error_log("OTP re-generation failed for {$user['email']}: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while generating your OTP.']);
}
