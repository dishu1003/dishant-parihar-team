<?php
require_once __DIR__ . '/../bootstrap.php';

// 1. Protect this endpoint
ApiSecurity::protect(['allowed_method' => 'POST']);

// 2. Get input and current user ID
$user_id = get_current_user_id();
$input = json_decode(file_get_contents('php://input'), true);

$title = sanitize_string($input['title'] ?? '');
$body = sanitize_string($input['body'] ?? ''); // Stripping all tags for security

// 3. Validate input
if (empty($title) || empty($body)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title and body are required.']);
    exit();
}

// 4. Insert new post into the database
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
