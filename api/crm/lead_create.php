<?php
require_once __DIR__ . '/../bootstrap.php';

// 1. Protect this endpoint
ApiSecurity::protect(['allowed_method' => 'POST']);

// 2. Get current user ID and input payload
$user_id = get_current_user_id();
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No data provided.']);
    exit();
}

// 3. Normalize input to handle both single object and array of objects
$leads_to_create = isset($input[0]) ? $input : [$input];
$created_count = 0;
$errors = [];

$pdo = db();

try {
    $pdo->beginTransaction();

    $sql = "INSERT INTO leads (user_id, name, mobile, city, work, age, meeting_date, interest_level, notes, follow_up_date, status)
            VALUES (:user_id, :name, :mobile, :city, :work, :age, :meeting_date, :interest_level, :notes, :follow_up_date, 'new')";

    $stmt = $pdo->prepare($sql);

    foreach ($leads_to_create as $index => $lead) {
        // Store raw input, escaping will be handled on output.
        $name = trim($lead['name'] ?? '');
        $mobile = trim($lead['mobile'] ?? '');

        if (empty($name) || empty($mobile)) {
            $errors[] = "Lead at index {$index} is missing a name or mobile number.";
            continue; // Skip this invalid lead
        }

        $stmt->execute([
            ':user_id' => $user_id,
            ':name' => $name,
            ':mobile' => $mobile,
            ':city' => isset($lead['city']) ? trim($lead['city']) : null,
            ':work' => isset($lead['work']) ? trim($lead['work']) : null,
            ':age' => filter_var($lead['age'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 150]]) ?: null,
            ':meeting_date' => !empty($lead['meeting_date']) ? date('Y-m-d', strtotime($lead['meeting_date'])) : null,
            ':interest_level' => in_array($lead['interest_level'], ['hot', 'warm', 'cold']) ? $lead['interest_level'] : 'warm',
            ':notes' => isset($lead['notes']) ? trim($lead['notes']) : null,
            ':follow_up_date' => !empty($lead['follow_up_date']) ? date('Y-m-d', strtotime($lead['follow_up_date'])) : null
        ]);
        $created_count++;
    }

    if (!empty($errors)) {
        // If there were validation errors but some leads might have been processed, roll back.
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Some leads had validation errors.', 'errors' => $errors]);
    } else {
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => "{$created_count} lead(s) created successfully."]);
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Database error creating lead for user {$user_id}: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred while creating leads.']);
}
