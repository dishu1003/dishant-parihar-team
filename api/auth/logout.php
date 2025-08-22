<?php
require_once __DIR__ . '/bootstrap_auth.php';

// 1. Verify Request Method
api_verify_request_method('POST');

// 2. Log the user out
logout();

// 3. Return success response
echo json_encode(['success' => true, 'message' => 'You have been successfully logged out.']);
