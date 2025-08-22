<?php
require_once __DIR__ . '/../bootstrap.php';

// Protect this endpoint
ApiSecurity::protect(['allowed_method' => 'GET']);


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
