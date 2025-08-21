<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/router.php';

// Protect this page - only logged-in members and admins can see it.
protect_page(['role' => null, 'check_otp' => true]);

$page_title = "Dashboard - " . SITE_NAME;
$body_class = "dashboard-page"; // Used by JS to init dashboard-specific scripts
include __DIR__ . '/../partials/header.php';
?>

<div class="dashboard-container container">
    <h1 class="page-title">Welcome to Your Dashboard, <?php echo e($_SESSION['user_name']); ?>!</h1>
    <p class="page-subtitle">Here's your snapshot for today. Let's make it a great one.</p>

    <!-- Quick Links -->
    <section class="quick-links">
        <div class="grid-container">
            <a href="crm.php" class="card quick-link-card">
                <div class="card__body">
                    <h3>My Personal CRM</h3>
                    <p>Manage your leads and follow-ups.</p>
                </div>
            </a>
            <a href="tasks.php" class="card quick-link-card">
                <div class="card__body">
                    <h3>Daily Tasks</h3>
                    <p>View your action plan for today.</p>
                </div>
            </a>
            <a href="learning.php" class="card quick-link-card">
                <div class="card__body">
                    <h3>Learning Hub</h3>
                    <p>Sharpen your skills and knowledge.</p>
                </div>
            </a>
            <a href="resources.php" class="card quick-link-card">
                <div class="card__body">
                    <h3>Resources</h3>
                    <p>Access scripts, brochures, and more.</p>
                </div>
            </a>
        </div>
    </section>

    <div class="dashboard-main-grid">
        <!-- AI Mentor Section -->
        <section id="ai-mentor-section" class="dashboard-section">
            <div id="ai-mentor-tips">
                <!-- AI Mentor tips will be rendered here by ai_mentor.js -->
                <div class="card"><div class="card__body">Loading your personalized tips...</div></div>
            </div>
        </section>

        <!-- Today's Tasks Section -->
        <section id="today-tasks-section" class="dashboard-section">
            <h3 class="subsection-title">Today's Top Tasks</h3>
            <div id="today-tasks-list" class="task-list">
                <!-- Tasks will be rendered here by a new dashboard.js -->
                <p>Loading tasks...</p>
            </div>
            <a href="tasks.php" class="btn btn-secondary btn-sm">View All Tasks</a>
        </section>

        <!-- Progress Section -->
        <section id="progress-section" class="dashboard-section">
            <h3 class="subsection-title">Your Weekly Progress</h3>
            <div class="progress-bar-container">
                <label for="task-progress">Task Completion</label>
                <progress id="task-progress" max="100" value="0"></progress>
                <span id="task-progress-label">0%</span>
            </div>
            <div class="progress-bar-container">
                <label for="learning-progress">Learning Progress</label>
                <progress id="learning-progress" max="100" value="0"></progress>
                <span id="learning-progress-label">0%</span>
            </div>
        </section>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
