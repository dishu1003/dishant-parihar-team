<?php
require_once __DIR__ . '/../bootstrap.php';

// 1. Protect this endpoint
ApiSecurity::protect(['allowed_method' => 'POST']); // Using POST for simplicity, could also be PUT/PATCH

// 2. Get user ID and input payload
$user_id = get_current_user_id();
$input = json_decode(file_get_contents('php://input'), true);
$lead_id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);

if (!$lead_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid lead ID provided.']);
    exit();
}

// 3. Verify lead ownership
$pdo = db();
try {
    $stmt = $pdo->prepare("SELECT id FROM leads WHERE id = :lead_id AND user_id = :user_id");
    $stmt->execute([':lead_id' => $lead_id, ':user_id' => $user_id]);
    if ($stmt->fetch() === false) {
        http_response_code(403); // Forbidden
        echo json_encode(['success' => false, 'message' => 'You do not have permission to update this lead.']);
        exit();
    }
} catch (PDOException $e) {
    error_log("Database error verifying lead ownership for user {$user_id}, lead {$lead_id}: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
    exit();
}

// 4. Build the dynamic UPDATE query
$allowed_fields = ['name', 'mobile', 'city', 'work', 'age', 'meeting_date', 'interest_level', 'notes', 'follow_up_date', 'status'];
$update_fields = [];
$params = [':lead_id' => $lead_id, ':user_id' => $user_id];
$sql_parts = [];

foreach ($allowed_fields as $field) {
    if (isset($input[$field])) {
        $sanitized_value = sanitize_string($input[$field]); // Basic sanitization

        // More specific validation/formatting
        if (in_array($field, ['meeting_date', 'follow_up_date']) && !empty($sanitized_value)) {
            $sanitized_value = date('Y-m-d', strtotime($sanitized_value));
        }
        if ($field === 'age' && !empty($sanitized_value)) {
            $sanitized_value = filter_var($sanitized_value, FILTER_VALIDATE_INT);
        }

        $sql_parts[] = "`{$field}` = :{$field}";
        $params[":{$field}"] = $sanitized_value;
    }
}

if (empty($sql_parts)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No fields to update were provided.']);
    exit();
}

// 5. Execute the update
try {
    $sql = "UPDATE leads SET " . implode(', ', $sql_parts) . ", last_contacted_at = NOW() WHERE id = :lead_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true, 'message' => 'Lead updated successfully.']);

} catch (PDOException $e) {
    error_log("Database error updating lead {$lead_id} for user {$user_id}: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred while updating the lead.']);
}
