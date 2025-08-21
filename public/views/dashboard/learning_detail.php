<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/router.php';

// Protect this page
protect_page();

// Get the slug from the URL to pass to our JS
$slug = isset($_GET['slug']) ? sanitize_string($_GET['slug']) : '';

$page_title = "Learning Module - " . SITE_NAME; // Title will be updated by JS
$body_class = "learning-detail-page";
include __DIR__ . '/../partials/header.php';
?>

<div class="learning-detail-container container" data-slug="<?php echo e($slug); ?>">
    <div class="page-header">
        <a href="learning.php">&larr; Back to Learning Hub</a>
    </div>

    <div id="module-content-container">
        <!-- Module content will be loaded here by learning_detail.js -->
        <p>Loading module...</p>
    </div>

    <div id="module-actions" class="module-actions" style="display: none;">
        <button id="mark-complete-btn" class="btn btn-success">Mark as Complete</button>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
