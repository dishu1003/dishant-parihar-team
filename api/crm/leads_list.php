<?php
require_once __DIR__ . '/../bootstrap.php';

// 1. Protect this endpoint
ApiSecurity::protect(['allowed_method' => 'GET']);

// 2. Get current user ID
$user_id = get_current_user_id();

// 3. Fetch leads from the database with priority sorting
try {
    $pdo = db();

    // The query sorts by:
    // 1. Overdue status (overdue tasks first)
    // 2. Interest Level (hot > warm > cold)
    // 3. Follow-up date (sooner dates first)
    $sql = "SELECT * FROM leads
            WHERE user_id = :user_id
            ORDER BY
                CASE WHEN follow_up_date IS NOT NULL AND follow_up_date < CURDATE() THEN 0 ELSE 1 END ASC,
                CASE interest_level
                    WHEN 'hot' THEN 1
                    WHEN 'warm' THEN 2
                    WHEN 'cold' THEN 3
                END ASC,
                follow_up_date ASC,
                created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);

    $leads = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $leads]);

} catch (PDOException $e) {
    error_log("Database error fetching leads for user {$user_id}: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred while fetching leads.']);
}
