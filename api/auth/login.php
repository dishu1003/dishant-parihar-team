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

// 2. Start session and verify CSRF token
start_secure_session();
CSRF::verifyRequest();

// 3. Get and sanitize input
$input = json_decode(file_get_contents('php://input'), true);
$email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
$password = $input['password'] ?? '';

if (!$email || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input. Please provide a valid email and password.']);
    exit();
}

// 4. Attempt to log in the user
if (login($email, $password)) {
    // Login successful, now handle 2FA (OTP)
    $user = find_user_by_email($email);

    try {
        $otp = generate_otp($user['id']);
        if (send_otp_email($user['email'], $otp)) {
            echo json_encode([
                'success' => true,
                'message' => 'Login successful. An OTP has been sent to your email.'
            ]);
        } else {
            // Log this error. The user is logged in session-wise but can't get OTP.
            error_log("Failed to send OTP email to {$email}.");
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Could not send OTP. Please contact support.']);
        }
    } catch (Exception $e) {
        error_log("OTP generation failed for {$email}: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'An error occurred while generating your OTP.']);
    }

} else {
    // Login failed
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
}
