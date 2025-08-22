<?php
require_once __DIR__ . '/../bootstrap.php';

// Protect this endpoint
ApiSecurity::protect(['allowed_method' => 'POST', 'role' => 'admin']);


try {
    // 3. Check for file upload presence and errors
    if (!isset($_FILES['resource_file']) || $_FILES['resource_file']['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload error or no file uploaded. Error code: ' . ($_FILES['resource_file']['error'] ?? 'N/A'));
    }

    $file = $_FILES['resource_file'];
    $title = sanitize_string($_POST['title'] ?? '');
    $category = sanitize_string($_POST['category'] ?? '');

    if (empty($title) || empty($category)) {
        throw new RuntimeException('Title and category are required.');
    }

    // 4. Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new RuntimeException('File exceeds maximum size of ' . (MAX_FILE_SIZE / 1024 / 1024) . ' MB.');
    }

    // 5. Validate MIME type using file content, not browser-provided type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    if (!in_array($mime_type, ALLOWED_MIME_TYPES)) {
        throw new RuntimeException('Invalid file type. Allowed types: ' . implode(', ', ALLOWED_MIME_TYPES));
    }

    // 6. Create a secure, unique filename
    $original_name = basename($file['name']);
    $safe_name = preg_replace('/[^A-Za-z0-9.\-_]/', '', $original_name); // Sanitize filename
    $unique_filename = uniqid('', true) . '_' . $safe_name;

    // Ensure the upload directory exists
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    $destination_path = UPLOAD_DIR . '/' . $unique_filename;
    $db_path = '/uploads/' . $unique_filename; // Path to store in DB

    // 7. Move the file
    if (!move_uploaded_file($file['tmp_name'], $destination_path)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    // 8. Insert record into the database
    $pdo = db();
    $sql = "INSERT INTO resources (title, category, file_path, mime_type, size, uploaded_by)
            VALUES (:title, :category, :file_path, :mime_type, :size, :uploaded_by)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => $title,
        ':category' => $category,
        ':file_path' => $db_path,
        ':mime_type' => $mime_type,
        ':size' => $file['size'],
        ':uploaded_by' => get_current_user_id()
    ]);

    echo json_encode(['success' => true, 'message' => 'File uploaded successfully.']);

} catch (RuntimeException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    // If DB insert fails, try to delete the orphaned file
    if (isset($destination_path) && file_exists($destination_path)) {
        unlink($destination_path);
    }
    error_log("Database error during file upload: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
}
