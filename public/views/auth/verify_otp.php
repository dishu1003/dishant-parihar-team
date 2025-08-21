<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/router.php';

start_secure_session();

// Protect this page: user must be logged in, but must NOT have verified OTP yet.
if (!is_logged_in()) {
    redirect('views/auth/login.php');
}
if (is_otp_verified()) {
    redirect(has_role('admin') ? 'views/admin/index.php' : 'views/dashboard/index.php');
}

$page_title = "Verify Your Identity - " . SITE_NAME;
$body_class = "auth-page";
include __DIR__ . '/../partials/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Two-Factor Authentication</h1>
        <p class="auth-subtitle">An OTP has been sent to your registered email address. Please enter it below.</p>

        <form id="otp-form" method="POST" action="/api/auth/verify_otp.php">
            <div id="form-error-message" class="form-message error-message"></div>
            <div id="form-success-message" class="form-message success-message"></div>

            <div class="form-group">
                <label for="otp" class="form-label">6-Digit OTP</label>
                <input type="text" id="otp" name="otp" class="form-control" required maxlength="6" pattern="\d{6}" inputmode="numeric">
            </div>

            <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Verify Code</button>
            </div>
        </form>

        <div class="auth-footer">
            <p>Didn't receive the code?</p>
            <button id="resend-otp-btn" class="btn-link">Resend OTP</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
