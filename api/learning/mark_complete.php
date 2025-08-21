<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';

// 1. Start session, check auth, and verify CSRF
start_secure_session();
CSRF::verifyRequest();

if (!is_logged_in() || !is_otp_verified()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit();
}

// 2. Verify Request Method
verify_request_method('POST');

// 3. Get input and current user ID
$user_id = get_current_user_id();
$input = json_decode(file_get_contents('php://input'), true);
$module_id = filter_var($input['module_id'] ?? null, FILTER_VALIDATE_INT);

if (!$module_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid module ID provided.']);
    exit();
}

$pdo = db();

try {
    // 4. Use INSERT...ON DUPLICATE KEY UPDATE to mark the module as complete.
    // This handles cases where the user has no record yet, or has a record 'in_progress'.
    // The unique key on (user_id, module_id) makes this work.
    $sql = "INSERT INTO user_learning (user_id, module_id, status, progress, completed_at, last_accessed_at)
            VALUES (:user_id, :module_id, 'completed', 100, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            status = 'completed',
            progress = 100,
            completed_at = IF(completed_at IS NULL, NOW(), completed_at),
            last_accessed_at = NOW()";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':module_id' => $module_id
    ]);

    if ($stmt->rowCount() > 0) {
        // Here you could trigger logic to check for achievements related to learning.
        echo json_encode(['success' => true, 'message' => 'Module marked as complete!']);
    } else {
        // This might happen if the module was already marked complete, which is not an error.
        echo json_encode(['success' => true, 'message' => 'Module was already complete.']);
    }

} catch (PDOException $e) {
    // Check for foreign key constraint violation (e.g., module_id doesn't exist)
    if ($e->getCode() == '23000') {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'The specified learning module does not exist.']);
    } else {
        error_log("Database error marking module {$module_id} complete for user {$user_id}: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
    }
}
