<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';

// 1. Verify Request Method
verify_request_method('POST');

// 2. Start session and verify CSRF token
start_secure_session();
CSRF::verifyRequest();

// 3. Log the user out
logout();

// 4. Return success response
echo json_encode(['success' => true, 'message' => 'You have been successfully logged out.']);
