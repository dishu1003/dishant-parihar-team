<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/router.php';

// Protect this page
protect_page();

$page_title = "Learning Hub - " . SITE_NAME;
$body_class = "learning-page";
include __DIR__ . '/../partials/header.php';
?>

<div class="learning-container container">
    <div class="page-header">
        <h1 class="page-title">Learning Hub</h1>
    </div>
    <p class="page-subtitle">Expand your knowledge and master the skills for success.</p>

    <div id="learning-modules-container">
        <!-- Learning modules will be dynamically rendered here by learning.js -->
        <p>Loading learning modules...</p>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
