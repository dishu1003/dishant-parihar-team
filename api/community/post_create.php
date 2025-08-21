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

$title = sanitize_string($input['title'] ?? '');
$body = sanitize_string($input['body'] ?? ''); // Stripping all tags for security

// 4. Validate input
if (empty($title) || empty($body)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title and body are required.']);
    exit();
}

// 5. Insert new post into the database
try {
    $pdo = db();

    // is_approved defaults to 1 (true) as per the schema, so posts are live immediately.
    // To implement moderation, the default in schema should be 0, and an admin would approve.
    $sql = "INSERT INTO community_posts (user_id, title, body) VALUES (:user_id, :title, :body)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':title' => $title,
        ':body' => $body
    ]);

    $post_id = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Post created successfully!',
        'post_id' => $post_id
    ]);

} catch (PDOException $e) {
    error_log("Database error creating post for user {$user_id}: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred while creating the post.']);
}
