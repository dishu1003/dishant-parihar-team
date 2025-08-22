<?php
require_once __DIR__ . '/../bootstrap.php';

// Protect this endpoint
ApiSecurity::protect(['allowed_method' => 'POST', 'role' => 'admin']);


// 3. Get and Decode JSON Input
$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input.']);
    exit();
}

// 4. Validate Input
$module_id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);

if (!$module_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A valid module ID is required.']);
    exit();
}

try {
    $pdo = db();

    // 5. Database Deletion
    // The `user_learning` table has ON DELETE CASCADE, so related progress records will be deleted automatically.
    $sql = "DELETE FROM learning_modules WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $module_id]);

    // 6. Check Rows Affected
    if ($stmt->rowCount() > 0) {
        // 7. Success Response
        echo json_encode(['success' => true, 'message' => 'Learning module deleted successfully.']);
    } else {
        // The module with the given ID was not found
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Learning module not found.']);
    }

} catch (PDOException $e) {
    error_log("Database error deleting learning module {$module_id}: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred while deleting the module.']);
}
