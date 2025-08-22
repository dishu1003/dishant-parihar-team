<?php
require_once __DIR__ . '/../bootstrap.php';

// Protect this endpoint
ApiSecurity::protect(['allowed_method' => 'GET', 'role' => 'admin']);

$module_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$module_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A valid module ID is required.']);
    exit();
}

// 3. Fetch the specific learning module from the database
try {
    $pdo = db();

    $sql = "SELECT
                id,
                title,
                slug,
                category,
                summary,
                content,
                video_url,
                order_no,
                is_active
            FROM learning_modules
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $module_id]);
    $module = $stmt->fetch();

    if ($module) {
        echo json_encode(['success' => true, 'data' => $module]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Learning module not found.']);
    }

} catch (PDOException $e) {
    error_log("Database error fetching module with id {$module_id} for admin panel: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred while fetching the module.']);
}
