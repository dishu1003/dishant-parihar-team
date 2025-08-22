<?php
require_once __DIR__ . '/bootstrap_auth.php';

// 1. Verify Request Method
api_verify_request_method('POST');

// 2. Get and sanitize input
$input = json_decode(file_get_contents('php://input'), true);

$name = sanitize_string($input['name'] ?? '');
$email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
$password = $input['password'] ?? '';
$phone = sanitize_string($input['phone'] ?? '');
$city = sanitize_string($input['city'] ?? '');

// 3. Validate input
if (empty($name) || !$email || empty($password) || empty($phone) || empty($city)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit();
}

// Basic password strength check
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
    exit();
}

// 4. Check if user already exists
$pdo = db();
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    http_response_code(409); // Conflict
    echo json_encode(['success' => false, 'message' => 'An account with this email already exists.']);
    exit();
}

// 5. Hash password
$password_hash = password_hash($password, PASSWORD_ALGO);
if ($password_hash === false) {
    error_log("Password hashing failed for registration attempt: {$email}");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not process registration. Please try again later.']);
    exit();
}

// 6. Insert new user into the database
try {
    $is_active = REQUIRE_ADMIN_APPROVAL_FOR_REGISTRATION ? 0 : 1;

    $stmt = $pdo->prepare(
        "INSERT INTO users (name, email, password_hash, phone, city, role, is_active)
         VALUES (?, ?, ?, ?, ?, 'member', ?)"
    );
    $stmt->execute([$name, $email, $password_hash, $phone, $city, $is_active]);

    $message = $is_active
        ? 'Registration successful! You can now log in.'
        : 'Registration successful! Your account is pending admin approval.';

    echo json_encode(['success' => true, 'message' => $message]);

} catch (PDOException $e) {
    error_log("Database error during registration: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred. Please try again later.']);
}
