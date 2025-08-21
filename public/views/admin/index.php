<?php
require_once __DIR__ . '/../../partials/header.php';

// The header includes session start, auth checks, and CSRF token generation.
// It will redirect if user is not logged in or not an admin.
?>

<main class="container">
    <div class="admin-dashboard-page">
        <h1 class="page-title">Admin Dashboard</h1>
        <p class="page-description">Welcome to the admin dashboard. Please use the navigation above to manage different parts of the application.</p>

        <div class="dashboard-widgets">
            <div class="widget">
                <h2>Users</h2>
                <p>Manage all registered users.</p>
                <a href="/views/admin/users.php" class="btn">Go to Users</a>
            </div>
            <div class="widget">
                <h2>Learning</h2>
                <p>Manage learning modules.</p>
                <a href="/views/admin/learning.php" class="btn btn-secondary">Go to Learning</a>
            </div>
            <div class="widget">
                <h2>Tasks</h2>
                <p>Manage daily tasks.</p>
                <a href="/views/admin/tasks.php" class="btn btn-secondary">Go to Tasks</a>
            </div>
             <div class="widget">
                <h2>Resources</h2>
                <p>Manage resources.</p>
                <a href="/views/admin/resources.php" class="btn btn-secondary">Go to Resources</a>
            </div>
             <div class="widget">
                <h2>Announcements</h2>
                <p>Manage announcements.</p>
                <a href="/views/admin/announcements.php" class="btn btn-secondary">Go to Announcements</a>
            </div>
             <div class="widget">
                <h2>Analytics</h2>
                <p>View application analytics.</p>
                <a href="/views/admin/analytics.php" class="btn btn-secondary">Go to Analytics</a>
            </div>
        </div>
    </div>
</main>

<?php
require_once __DIR__ . '/../../partials/footer.php';
?>
