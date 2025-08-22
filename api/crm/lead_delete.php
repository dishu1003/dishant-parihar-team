<?php
require_once __DIR__ . '/../bootstrap.php';

// 1. Protect this endpoint
ApiSecurity::protect(['allowed_method' => 'POST']); // Using POST for simplicity, could also be DELETE

// 2. Get user ID and input payload
$user_id = get_current_user_id();
$input = json_decode(file_get_contents('php://input'), true);
$lead_id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);

if (!$lead_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid lead ID provided.']);
    exit();
}

// 3. Verify lead ownership and execute deletion in a transaction
$pdo = db();
try {
    $pdo->beginTransaction();

    // First, verify ownership
    $stmt_check = $pdo->prepare("SELECT id FROM leads WHERE id = :lead_id AND user_id = :user_id");
    $stmt_check->execute([':lead_id' => $lead_id, ':user_id' => $user_id]);

    if ($stmt_check->fetch() === false) {
        $pdo->rollBack();
        http_response_code(403); // Forbidden
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this lead.']);
        exit();
    }

    // If ownership is verified, proceed with deletion
    $stmt_delete = $pdo->prepare("DELETE FROM leads WHERE id = :lead_id AND user_id = :user_id");
    $stmt_delete->execute([':lead_id' => $lead_id, ':user_id' => $user_id]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Lead deleted successfully.']);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Database error deleting lead {$lead_id} for user {$user_id}: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred while deleting the lead.']);
}
