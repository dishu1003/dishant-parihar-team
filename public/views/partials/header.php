<?php
// This header is included on all pages.
// It starts the session and generates a CSRF token for use in forms and JS.
if (session_status() === PHP_SESSION_NONE) {
    // We need to require auth and csrf helpers to get the functions.
    require_once __DIR__ . '/../../../includes/auth.php';
    require_once __DIR__ . '/../../../includes/csrf.php';
    start_secure_session();
}
// Ensure a CSRF token is available for the page
$csrf_token = CSRF::getToken() ?? CSRF::generateToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Meta Tags -->
    <title><?php echo isset($page_title) ? e($page_title) : e(SITE_NAME); ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? e($page_description) : 'The Digital HQ for Asclepius Wellness.'; ?>">
    <meta name="csrf-token" content="<?php echo e($csrf_token); ?>">

    <!-- Social Media Meta Tags (Open Graph & Twitter) -->
    <meta property="og:title" content="<?php echo isset($page_title) ? e($page_title) : e(SITE_NAME); ?>">
    <meta property="og:description" content="<?php echo isset($page_description) ? e($page_description) : 'The Digital HQ for Asclepius Wellness.'; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo e(SITE_URL . $_SERVER['REQUEST_URI']); ?>">
    <!-- <meta property="og:image" content="<?php echo e(SITE_URL); ?>/assets/images/social-preview.jpg"> -->

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo isset($page_title) ? e($page_title) : e(SITE_NAME); ?>">
    <meta name="twitter:description" content="<?php echo isset($page_description) ? e($page_description) : 'The Digital HQ for Asclepius Wellness.'; ?>">
    <!-- <meta name="twitter:image" content="<?php echo e(SITE_URL); ?>/assets/images/social-preview.jpg"> -->

    <!-- PWA & Theme -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#002147">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- SEO / Social -->
    <!-- <meta property="og:title" content="..."> -->

</head>
<body class="<?php echo isset($body_class) ? e($body_class) : ''; ?>">

    <header class="main-header">
        <div class="container">
            <a href="/" class="logo"><?php echo e(SITE_NAME); ?></a>
            <nav class="main-nav" id="main-nav">
                <?php if (is_logged_in() && is_otp_verified()): ?>
                    <!-- Logged-in navigation is handled by topnav.php -->
                <?php else: ?>
                    <ul>
                        <li><a href="/views/auth/login.php">Login</a></li>
                        <li><a href="/views/auth/register.php" class="btn btn-primary">Join Now</a></li>
                    </ul>
                <?php endif; ?>
            </nav>
            <button class="mobile-nav-toggle" id="mobile-nav-toggle" aria-label="Open navigation menu" aria-controls="main-nav" aria-expanded="false">
                <span class="sr-only">Menu</span>
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>
        </div>
    </header>

    <?php if (is_logged_in() && is_otp_verified()): ?>
        <?php include __DIR__ . '/topnav.php'; // Include the secondary nav for logged-in users ?>
    <?php endif; ?>

    <!-- The main content of each page will start here -->
    <div id="toast-container"></div>
    <main>
