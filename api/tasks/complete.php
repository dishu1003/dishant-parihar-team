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
$user_task_id = filter_var($input['user_task_id'] ?? null, FILTER_VALIDATE_INT);
$status = $input['status'] ?? '';

if (!$user_task_id || !in_array($status, ['done', 'skipped'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input. Please provide a valid task ID and status.']);
    exit();
}

$pdo = db();

try {
    $pdo->beginTransaction();

    // 4. Verify ownership and that the task is pending
    $sql_check = "SELECT ut.id, ut.status, t.xp_reward
                  FROM user_tasks ut
                  JOIN tasks t ON ut.task_id = t.id
                  WHERE ut.id = :user_task_id AND ut.user_id = :user_id";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([':user_task_id' => $user_task_id, ':user_id' => $user_id]);
    $task = $stmt_check->fetch();

    if (!$task) {
        $pdo->rollBack();
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Task not found or you do not have permission to update it.']);
        exit();
    }

    if ($task['status'] !== 'pending') {
        $pdo->rollBack();
        http_response_code(409); // Conflict
        echo json_encode(['success' => false, 'message' => 'This task has already been completed or skipped.']);
        exit();
    }

    // 5. Update the task
    $points_earned = 0;
    $completed_at = null;

    if ($status === 'done') {
        $points_earned = $task['xp_reward'];
        $completed_at = date('Y-m-d H:i:s');
    }

    $sql_update = "UPDATE user_tasks
                   SET status = :status, points_earned = :points_earned, completed_at = :completed_at
                   WHERE id = :user_task_id";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([
        ':status' => $status,
        ':points_earned' => $points_earned,
        ':completed_at' => $completed_at,
        ':user_task_id' => $user_task_id
    ]);

    // Here you could add logic to check for new achievements/badges based on task completion.
    // For example: check_streak_achievement($user_id);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Task status updated.',
        'new_status' => $status,
        'points_earned' => $points_earned
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Database error completing task {$user_task_id} for user {$user_id}: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
}
