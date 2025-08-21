<?php
// This is the main public entry point of the application.

// Load essential files
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Start a secure session
start_secure_session();

// If the user is logged in and has verified their OTP, redirect them to the dashboard.
if (is_logged_in() && is_otp_verified()) {
    // Use the router's redirect function for consistency
    require_once __DIR__ . '/../includes/router.php';
    redirect('views/dashboard/index.php');
}

// If the user is logged in but hasn't verified OTP, redirect to the OTP page.
if (is_logged_in() && !is_otp_verified()) {
    require_once __DIR__ . '/../includes/router.php';
    redirect('views/auth/verify_otp.php');
}

// --- If we reach here, the user is not logged in. ---
// We will display the public landing page.

// Set page-specific variables
$page_title = "Welcome to " . SITE_NAME;
$page_description = "Your digital headquarters for success with Asclepius Wellness.";

// Include the header
// We'll assume the header can handle both logged-out and logged-in states.
include __DIR__ . '/views/partials/header.php';

?>

<main class="landing-page">
    <section class="hero" style="background-image: url('assets/img/hero-background.jpg');">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">Automated Income System</h1>
            <p class="hero-subtitle">Your Digital HQ for Growth with Asclepius Wellness</p>
            <div class="hero-cta">
                <a href="/views/auth/login.php" class="btn btn-primary">Login</a>
                <a href="/views/auth/register.php" class="btn btn-secondary">Join the Team</a>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <h2 class="section-title">Why Join Us?</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <img src="assets/img/icon-fast-start.svg" alt="Fast Starts Icon" class="feature-icon">
                    <h3 class="feature-title">Fast Starts</h3>
                    <p class="feature-description">Get started quickly with our guided training and resources.</p>
                </div>
                <div class="feature-card">
                    <img src="assets/img/icon-products.svg" alt="Natural Products Icon" class="feature-icon">
                    <h3 class="feature-title">Natural Products</h3>
                    <p class="feature-description">Promote high-quality, trusted wellness products from Asclepius.</p>
                </div>
                <div class="feature-card">
                    <img src="assets/img/icon-mentorship.svg" alt="AI Mentorship Icon" class="feature-icon">
                    <h3 class="feature-title">AI Mentorship</h3>
                    <p class="feature-description">Receive personalized guidance to help you succeed every day.</p>
                </div>
                <div class="feature-card">
                    <img src="assets/img/icon-community.svg" alt="Supportive Community Icon" class="feature-icon">
                    <h3 class="feature-title">Supportive Community</h3>
                    <p class="feature-description">Connect with fellow team members and grow together.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- More landing page sections can be added here -->

</main>

<?php
// Include the footer
include __DIR__ . '/views/partials/footer.php';
?>
