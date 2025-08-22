<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';

// 1. Verify Request Method
verify_request_method('POST');

// 2. Start session, check login status, and verify CSRF
start_secure_session();
CSRF::verifyRequest();

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You are not logged in.']);
    exit();
}

// 3. Get and sanitize input
$input = json_decode(file_get_contents('php://input'), true);
$otp = $input['otp'] ?? '';

if (empty($otp) || !ctype_digit($otp) || strlen($otp) !== 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid OTP format.']);
    exit();
}

// 4. Get user ID from session and attempt to verify OTP
$user_id = get_current_user_id();

if (verify_otp($user_id, $otp)) {
    // OTP verification successful
    echo json_encode(['success' => true, 'message' => 'Verification successful. Redirecting to your dashboard...']);
} else {
    // OTP verification failed
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP. Please try again.']);
}
