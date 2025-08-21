<?php
// This partial requires the user to be logged in and OTP verified.
// The check is performed in header.php before this file is included.
// We can safely assume $_SESSION['user_id'] and $_SESSION['user_role'] exist.
?>
<nav class="topnav">
    <div class="container">
        <div class="topnav__left">
            <ul class="topnav__links">
                <?php if (has_role('admin')): ?>
                    <!-- Admin Navigation -->
                    <li><a href="/views/admin/index.php">Dashboard</a></li>
                    <li><a href="/views/admin/users.php">Users</a></li>
                    <li><a href="/views/admin/learning.php">Learning</a></li>
                    <li><a href="/views/admin/tasks.php">Tasks</a></li>
                    <li><a href="/views/admin/resources.php">Resources</a></li>
                    <li><a href="/views/admin/announcements.php">Announcements</a></li>
                    <li><a href="/views/admin/analytics.php">Analytics</a></li>
                <?php else: ?>
                    <!-- Member Navigation -->
                    <li><a href="/views/dashboard/index.php">Dashboard</a></li>
                    <li><a href="/views/dashboard/crm.php">My CRM</a></li>
                    <li><a href="/views/dashboard/tasks.php">Daily Tasks</a></li>
                    <li><a href="/views/dashboard/learning.php">Learning Hub</a></li>
                    <li><a href="/views/dashboard/community.php">Community</a></li>
                    <li><a href="/views/dashboard/resources.php">Resources</a></li>
                    <li><a href="/views/dashboard/achievements.php">Achievements</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="topnav__right">
            <span class="topnav__welcome">Welcome, <?php echo e($_SESSION['user_name'] ?? 'User'); ?></span>
            <div class="theme-toggle-container">
                <button id="theme-toggle" class="theme-toggle-btn" title="Toggle Dark/Light Mode">
                    <span class="sr-only">Toggle Dark/Light Mode</span>
                    <!-- Icon will be handled by CSS -->
                </button>
            </div>
            <form id="logout-form" action="/api/auth/logout.php" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                <button type="submit" class="btn btn-secondary btn-sm">Logout</button>
            </form>
        </div>
    </div>
</nav>
