<?php
/**
 * Page Access Control / Router Guard
 *
 * Provides functions to protect pages by checking authentication status and user roles.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

/**
 * Redirects the user to a specified URL.
 *
 * @param string $path The path to redirect to (e.g., '/auth/login.php').
 */
function redirect(string $path): void {
    // Ensure the path is relative to the site URL
    $url = SITE_URL . '/' . ltrim($path, '/');
    header('Location: ' . $url);
    exit();
}

/**
 * The main page protection function. Call this at the top of any protected page.
 *
 * It checks for:
 * 1. Required login status.
 * 2. Required OTP verification status.
 * 3. Required user role.
 * 4. Required "force password reset" completion.
 *
 * @param array $options An array of protection rules:
 *  - 'auth' (bool): Is login required? Defaults to true.
 *  - 'role' (string|null): 'admin' or 'member'. Null allows any authenticated user.
 *  - 'check_otp' (bool): Must the user have passed OTP verification? Defaults to true for protected pages.
 *  - 'check_password_reset' (bool): Check if a password reset is forced. Defaults to true.
 */
function protect_page(array $options = []): void {
    start_secure_session();

    // Default options for a standard protected page
    $defaults = [
        'auth' => true,
        'role' => null, // any authenticated role
        'check_otp' => true,
        'check_password_reset' => true
    ];
    $rules = array_merge($defaults, $options);

    // --- Rule 1: Check for forced password reset ---
    // This check should happen early.
    if ($rules['check_password_reset'] && isset($_SESSION['force_password_reset']) && $_SESSION['force_password_reset'] === true) {
        // Allow access only to the password reset page
        if (strpos($_SERVER['PHP_SELF'], 'reset_password.php') === false) {
             // For now, we don't have a dedicated reset page, let's assume it's part of a profile page.
             // This logic needs to be fleshed out with the views. Let's redirect to a placeholder.
             // redirect('auth/force-reset.php');
        }
    }

    // --- Rule 2: Check if login is required ---
    if ($rules['auth'] && !is_logged_in()) {
        $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
        redirect('views/auth/login.php');
    }

    // If login is not required, no other rules apply.
    if (!$rules['auth']) {
        return;
    }

    // --- Rule 3: Check if OTP verification is required ---
    if ($rules['check_otp'] && !is_otp_verified()) {
        // Allow access only to the OTP verification page
        if (strpos($_SERVER['PHP_SELF'], 'verify_otp.php') === false) {
            redirect('views/auth/verify_otp.php');
        }
        // If we are on the OTP page, stop further checks
        return;
    }

    // --- Rule 4: Check for required role ---
    if ($rules['role'] !== null) {
        if (!has_role($rules['role'])) {
            // User is logged in but has the wrong role.
            // Redirect to their own dashboard or show a 403 Forbidden error.
            http_response_code(403);
            // You can create a dedicated 403.php view
            die('Forbidden: You do not have permission to access this page.');
        }
    }
}
