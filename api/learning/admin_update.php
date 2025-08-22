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

// 2. Verify Request Method and Get Module ID
verify_request_method('POST'); // Using POST for simplicity, though PUT could also be used.
$module_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$module_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A valid module ID is required in the URL.']);
    exit();
}

// 3. Get and Decode JSON Input
$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input.']);
    exit();
}

// 4. Validate and Sanitize Input
$title = trim($data['title'] ?? '');
$category = trim($data['category'] ?? '');
$summary = trim($data['summary'] ?? '');
$content = sanitize_html($data['content'] ?? '');
$video_url = filter_var($data['video_url'] ?? '', FILTER_SANITIZE_URL);
$order_no = filter_var($data['order_no'] ?? 0, FILTER_VALIDATE_INT);
$is_active = filter_var($data['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);

if (empty($title) || empty($category) || empty($summary)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title, Category, and Summary are required fields.']);
    exit();
}

// 5. Slug Generation (if title changed)
function generate_unique_slug_for_update($title, $current_id, $pdo) {
    $base_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $slug = $base_slug;
    $counter = 1;

    $sql = "SELECT COUNT(*) FROM learning_modules WHERE slug = :slug AND id != :id";
    $stmt = $pdo->prepare($sql);

    while (true) {
        $stmt->execute([':slug' => $slug, ':id' => $current_id]);
        if ($stmt->fetchColumn() == 0) {
            return $slug;
        }
        $slug = $base_slug . '-' . $counter++;
    }
}

try {
    $pdo = db();

    // Check if module exists and get current title/slug
    $stmt_check = $pdo->prepare("SELECT title, slug FROM learning_modules WHERE id = :id");
    $stmt_check->execute([':id' => $module_id]);
    $current_module = $stmt_check->fetch();

    if (!$current_module) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Module not found.']);
        exit();
    }

    $slug = $current_module['slug'];
    if ($title !== $current_module['title']) {
        $slug = generate_unique_slug_for_update($title, $module_id, $pdo);
    }

    // 6. Database Update
    $sql = "UPDATE learning_modules SET
                title = :title,
                slug = :slug,
                category = :category,
                summary = :summary,
                content = :content,
                video_url = :video_url,
                order_no = :order_no,
                is_active = :is_active,
                updated_at = NOW()
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => $title,
        ':slug' => $slug,
        ':category' => $category,
        ':summary' => $summary,
        ':content' => $content,
        ':video_url' => $video_url,
        ':order_no' => $order_no,
        ':is_active' => $is_active ? 1 : 0,
        ':id' => $module_id
    ]);

    // 7. Success Response
    echo json_encode(['success' => true, 'message' => 'Learning module updated successfully.']);

} catch (PDOException $e) {
    error_log("Database error updating learning module {$module_id}: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred while updating the module.']);
}
