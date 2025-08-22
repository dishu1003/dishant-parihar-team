<?php
require_once __DIR__ . '/../bootstrap.php';

// 1. Protect this endpoint
ApiSecurity::protect(['allowed_method' => 'GET']); // CSRF check is automatically skipped for GET requests

// 2. Fetch community posts
try {
    $pdo = db();

    // The query fetches all approved posts, joining with the users table to get the author's name.
    // Pinned posts are always shown first, then the rest are sorted by creation date.
    $sql = "SELECT
                p.id,
                p.title,
                p.body,
                p.created_at,
                p.is_pinned,
                u.name AS author_name
            FROM
                community_posts p
            JOIN
                users u ON p.user_id = u.id
            WHERE
                p.is_approved = 1
            ORDER BY
                p.is_pinned DESC, p.created_at DESC";

    $stmt = $pdo->query($sql);
    $posts = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $posts]);

} catch (PDOException $e) {
    error_log("Database error fetching community posts: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred while fetching posts.']);
}
