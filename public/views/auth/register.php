<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/router.php';

start_secure_session();

// If user is already logged in, redirect them away from this page
if (is_logged_in()) {
    redirect(has_role('admin') ? 'views/admin/index.php' : 'views/dashboard/index.php');
}

$page_title = "Register - " . SITE_NAME;
$body_class = "auth-page";
include __DIR__ . '/../partials/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Join the Team</h1>
        <p class="auth-subtitle">Create your account to get started.</p>

        <form id="register-form" method="POST" action="/api/auth/register.php">
            <div id="form-error-message" class="form-message error-message"></div>
            <div id="form-success-message" class="form-message success-message"></div>

            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required minlength="8">
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="city" class="form-label">City</label>
                <input type="text" id="city" name="city" class="form-control" required>
            </div>

            <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </div>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
