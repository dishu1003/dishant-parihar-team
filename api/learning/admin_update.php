<?php
require_once __DIR__ . '/../bootstrap.php';

// Protect this endpoint
ApiSecurity::protect(['allowed_method' => 'POST', 'role' => 'admin']);

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
$title = sanitize_string($data['title'] ?? '');
$category = sanitize_string($data['category'] ?? '');
$summary = sanitize_string($data['summary'] ?? '');
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
