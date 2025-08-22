<?php
require_once __DIR__ . '/../bootstrap.php';

// Protect this endpoint
ApiSecurity::protect(['allowed_method' => 'GET']);


// 3. Get current user ID
$user_id = get_current_user_id();
$today = date('Y-m-d');

$pdo = db();

try {
    // --- Task Generation Logic ---
    // Check if tasks for today have already been generated for this user
    $stmt_check = $pdo->prepare("SELECT 1 FROM user_tasks WHERE user_id = :user_id AND due_date = :due_date LIMIT 1");
    $stmt_check->execute([':user_id' => $user_id, ':due_date' => $today]);

    if ($stmt_check->fetch() === false) {
        // Not generated yet, so let's generate them from the master tasks list.
        $pdo->beginTransaction();

        // Get all active daily tasks from the main `tasks` table
        $stmt_daily_tasks = $pdo->query("SELECT id FROM tasks WHERE is_active = 1 AND is_daily = 1");
        $daily_tasks = $stmt_daily_tasks->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($daily_tasks)) {
            $sql_insert = "INSERT INTO user_tasks (user_id, task_id, due_date, status) VALUES (:user_id, :task_id, :due_date, 'pending')";
            $stmt_insert = $pdo->prepare($sql_insert);

            foreach ($daily_tasks as $task_id) {
                $stmt_insert->execute([
                    ':user_id' => $user_id,
                    ':task_id' => $task_id,
                    ':due_date' => $today
                ]);
            }
        }
        $pdo->commit();
    }

    // --- Fetch Today's Tasks ---
    $sql_fetch = "SELECT t.id, t.title, t.description, t.type, t.xp_reward, ut.status, ut.id as user_task_id
                  FROM tasks t
                  JOIN user_tasks ut ON t.id = ut.task_id
                  WHERE ut.user_id = :user_id AND ut.due_date = :due_date
                  ORDER BY t.type, t.id";

    $stmt_fetch = $pdo->prepare($sql_fetch);
    $stmt_fetch->execute([':user_id' => $user_id, ':due_date' => $today]);
    $tasks = $stmt_fetch->fetchAll();

    echo json_encode(['success' => true, 'data' => $tasks]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Database error fetching/generating tasks for user {$user_id}: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
}
