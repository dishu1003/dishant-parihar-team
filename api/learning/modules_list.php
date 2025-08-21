<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';

// 1. Start session and check authentication
start_secure_session();
if (!is_logged_in() || !is_otp_verified()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit();
}

// 2. Verify Request Method
verify_request_method('GET');

// 3. Get current user ID
$user_id = get_current_user_id();

// 4. Fetch learning modules with user progress
try {
    $pdo = db();

    // The LEFT JOIN ensures all active modules are returned, along with the user's specific progress if it exists.
    $sql = "SELECT
                lm.id,
                lm.title,
                lm.slug,
                lm.category,
                lm.summary,
                lm.order_no,
                COALESCE(ul.status, 'not_started') as status,
                COALESCE(ul.progress, 0) as progress
            FROM
                learning_modules lm
            LEFT JOIN
                user_learning ul ON lm.id = ul.module_id AND ul.user_id = :user_id
            WHERE
                lm.is_active = 1
            ORDER BY
                lm.category, lm.order_no";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);

    $modules = $stmt->fetchAll();

    // Group modules by category for a better frontend experience
    $grouped_modules = [];
    foreach ($modules as $module) {
        $grouped_modules[$module['category']][] = $module;
    }

    echo json_encode(['success' => true, 'data' => $grouped_modules]);

} catch (PDOException $e) {
    error_log("Database error fetching learning modules for user {$user_id}: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred while fetching learning modules.']);
}
