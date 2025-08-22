<?php
require_once __DIR__ . '/../bootstrap.php';

// Protect this endpoint
ApiSecurity::protect(['allowed_method' => 'GET']);


// 3. Fetch resources
try {
    $pdo = db();

    // The query fetches all resources, joining with users to get the uploader's name.
    // A LEFT JOIN is used in case the uploader's account has been deleted.
    $sql = "SELECT
                r.id,
                r.title,
                r.category,
                r.file_path,
                r.mime_type,
                r.size,
                r.created_at,
                COALESCE(u.name, 'N/A') AS uploader_name
            FROM
                resources r
            LEFT JOIN
                users u ON r.uploaded_by = u.id
            ORDER BY
                r.category, r.title";

    $stmt = $pdo->query($sql);
    $resources = $stmt->fetchAll();

    // Group resources by category for a better frontend experience
    $grouped_resources = [];
    foreach ($resources as $resource) {
        $grouped_resources[$resource['category']][] = $resource;
    }

    echo json_encode(['success' => true, 'data' => $grouped_resources]);

} catch (PDOException $e) {
    error_log("Database error fetching resources: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred while fetching resources.']);
}
