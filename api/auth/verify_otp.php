<?php
require_once __DIR__ . '/../bootstrap.php';

// 1. Protect this endpoint
ApiSecurity::protect([
    'require_otp' => false, // User is here to verify an OTP
    'allowed_method' => 'POST'
]);

// 2. Get and sanitize input
$input = json_decode(file_get_contents('php://input'), true);
$otp = sanitize_string($input['otp'] ?? '');

if (empty($otp) || !ctype_digit($otp) || strlen($otp) !== 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid OTP format.']);
    exit();
}

// 3. Get user ID from session and attempt to verify OTP
$user_id = get_current_user_id();

if (verify_otp($user_id, $otp)) {
    // OTP verification successful
    echo json_encode(['success' => true, 'message' => 'Verification successful. Redirecting to your dashboard...']);
} else {
    // OTP verification failed
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP. Please try again.']);
}
