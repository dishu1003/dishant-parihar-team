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

// 2. Verify Request Method and get input
verify_request_method('GET');
$slug = sanitize_string($_GET['slug'] ?? '');

if (empty($slug)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Module slug is required.']);
    exit();
}

// 3. Get current user ID
$user_id = get_current_user_id();
$pdo = db();

try {
    // 4. Fetch module details along with user progress
    $sql_fetch = "SELECT
                    lm.*,
                    COALESCE(ul.status, 'not_started') as status,
                    COALESCE(ul.progress, 0) as progress
                  FROM
                    learning_modules lm
                  LEFT JOIN
                    user_learning ul ON lm.id = ul.module_id AND ul.user_id = :user_id
                  WHERE
                    lm.slug = :slug AND lm.is_active = 1";

    $stmt_fetch = $pdo->prepare($sql_fetch);
    $stmt_fetch->execute([':user_id' => $user_id, ':slug' => $slug]);
    $module = $stmt_fetch->fetch();

    if (!$module) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Learning module not found.']);
        exit();
    }

    // 5. Update user progress to 'in_progress' if they are viewing it for the first time
    if ($module['status'] === 'not_started') {
        $sql_upsert = "INSERT INTO user_learning (user_id, module_id, status, last_accessed_at)
                       VALUES (:user_id, :module_id, 'in_progress', NOW())
                       ON DUPLICATE KEY UPDATE
                       status = IF(status = 'not_started', 'in_progress', status),
                       last_accessed_at = NOW()";

        $stmt_upsert = $pdo->prepare($sql_upsert);
        $stmt_upsert->execute([':user_id' => $user_id, ':module_id' => $module['id']]);

        // Update the status in the data we're about to send back
        $module['status'] = 'in_progress';
    } else {
        // Just update the last accessed time
        $sql_update_access = "UPDATE user_learning SET last_accessed_at = NOW() WHERE user_id = :user_id AND module_id = :module_id";
        $stmt_update_access = $pdo->prepare($sql_update_access);
        $stmt_update_access->execute([':user_id' => $user_id, ':module_id' => $module['id']]);
    }

    echo json_encode(['success' => true, 'data' => $module]);

} catch (PDOException $e) {
    error_log("Database error fetching module '{$slug}' for user {$user_id}: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
}
