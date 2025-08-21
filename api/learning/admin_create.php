<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/security.php';

// 1. Start session and check authentication & authorization
start_secure_session();
if (!is_logged_in() || !is_otp_verified() || !has_role('admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden: Admin access required.']);
    exit();
}

// 2. Verify Request Method
verify_request_method('POST');

// 3. Get and Decode JSON Input
$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input.']);
    exit();
}

// 4. Validate and Sanitize Input
$title = sanitize_string($data['title'] ?? '');
$category = sanitize_string($data['category'] ?? '');
$summary = sanitize_string($data['summary'] ?? '');
$content = sanitize_html($data['content'] ?? ''); // Use the new HTML sanitizer
$video_url = filter_var($data['video_url'] ?? '', FILTER_SANITIZE_URL);
$order_no = filter_var($data['order_no'] ?? 0, FILTER_VALIDATE_INT);
$is_active = filter_var($data['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);

if (empty($title) || empty($category) || empty($summary)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title, Category, and Summary are required fields.']);
    exit();
}

// 5. Slug Generation
function create_unique_slug($title, $pdo) {
    $base_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $slug = $base_slug;
    $counter = 1;

    $sql = "SELECT COUNT(*) FROM learning_modules WHERE slug = :slug";
    $stmt = $pdo->prepare($sql);

    while (true) {
        $stmt->execute([':slug' => $slug]);
        if ($stmt->fetchColumn() == 0) {
            return $slug;
        }
        $slug = $base_slug . '-' . $counter++;
    }
}

try {
    $pdo = db();
    $slug = create_unique_slug($title, $pdo);

    // 6. Database Insertion
    $sql = "INSERT INTO learning_modules (title, slug, category, summary, content, video_url, order_no, is_active, created_at, updated_at)
            VALUES (:title, :slug, :category, :summary, :content, :video_url, :order_no, :is_active, NOW(), NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => $title,
        ':slug' => $slug,
        ':category' => $category,
        ':summary' => $summary,
        ':content' => $content,
        ':video_url' => $video_url,
        ':order_no' => $order_no,
        ':is_active' => $is_active ? 1 : 0
    ]);

    $new_module_id = $pdo->lastInsertId();

    // 7. Success Response
    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Learning module created successfully.', 'new_module_id' => $new_module_id]);

} catch (PDOException $e) {
    error_log("Database error creating learning module: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred while creating the module.']);
}
