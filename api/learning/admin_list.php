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

// 3. Fetch all learning modules from the database
try {
    $pdo = db();

    // Select all modules, without filtering for is_active, for the admin panel.
    $sql = "SELECT
                id,
                title,
                slug,
                category,
                summary,
                content,
                video_url,
                order_no,
                is_active,
                created_at,
                updated_at
            FROM learning_modules
            ORDER BY category, order_no";

    $stmt = $pdo->query($sql);
    $modules = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $modules]);

} catch (PDOException $e) {
    error_log("Database error fetching all learning modules for admin panel: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred while fetching learning modules.']);
}
