<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/router.php';
require_once __DIR__ . '/../../../includes/auth.php';

start_secure_session();

// If user is already fully logged in, redirect them away from login page
if (is_logged_in() && is_otp_verified()) {
    redirect(has_role('admin') ? 'views/admin/index.php' : 'views/dashboard/index.php');
}
// If user is logged in but needs OTP, send them to the OTP page
if (is_logged_in() && !is_otp_verified()) {
    redirect('views/auth/verify_otp.php');
}

$page_title = "Login - " . SITE_NAME;
$body_class = "auth-page";
include __DIR__ . '/../partials/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Member Login</h1>
        <p class="auth-subtitle">Welcome back! Please enter your credentials.</p>

        <form id="login-form" method="POST" action="/api/auth/login.php">
            <div id="form-error-message" class="form-message error-message"></div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </div>
        </form>

        <div class="auth-footer">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <p><a href="forgot_password.php">Forgot your password?</a></p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
