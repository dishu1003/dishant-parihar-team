<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/security.php';

// 1. Start session and check authentication & authorization
start_secure_session();
if (!is_logged_in() || !is_otp_verified() || !has_role('admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden: Admin access required.']);
    exit();
}

// 2. Verify Request Method
verify_request_method('GET');

// 3. Fetch all users from the database
try {
    $pdo = db();

    // Select all users, excluding sensitive information like password hash and OTP codes.
    $sql = "SELECT
                id,
                name,
                email,
                phone,
                city,
                role,
                last_login_at,
                created_at,
                is_active,
                force_password_reset
            FROM users
            ORDER BY created_at DESC";

    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $users]);

} catch (PDOException $e) {
    error_log("Database error fetching all users for admin panel: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred while fetching users.']);
}
