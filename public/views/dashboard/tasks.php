<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/router.php';

// Protect this page
protect_page();

$page_title = "Daily Tasks - " . SITE_NAME;
$body_class = "tasks-page";
include __DIR__ . '/../partials/header.php';
?>

<div class="tasks-container container">
    <div class="page-header">
        <h1 class="page-title">Today's Action Plan</h1>
    </div>
    <p class="page-subtitle">Complete these tasks to build momentum and earn points.</p>

    <div class="task-stats">
        <div class="stat-card">
            <span class="stat-value" id="tasks-completed">0/0</span>
            <span class="stat-label">Tasks Completed</span>
        </div>
        <div class="stat-card">
            <span class="stat-value" id="points-earned">0</span>
            <span class="stat-label">Points Earned Today</span>
        </div>
        <div class="stat-card">
            <span class="stat-value" id="current-streak">0 Days</span>
            <span class="stat-label">Current Streak</span>
        </div>
    </div>

    <div id="tasks-list-container" class="tasks-list">
        <!-- Tasks will be dynamically rendered here by tasks.js -->
        <p>Loading your daily tasks...</p>
    </div>
</div>


<?php include __DIR__ . '/../partials/footer.php'; ?>
