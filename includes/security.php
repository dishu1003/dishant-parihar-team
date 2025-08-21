<?php
/**
 * Security Helper Functions
 *
 * Provides functions for input sanitization, output escaping, and setting security headers.
 */

// Ensure config is loaded, as it might be used for things like character sets in the future.
require_once __DIR__ . '/config.php';

/**
 * Sanitizes a string to prevent XSS.
 * Removes tags and optionally encodes special characters.
 *
 * @param string $input The string to sanitize.
 * @return string The sanitized string.
 */
function sanitize_string(string $input): string {
    return filter_var(trim($input), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
}

/**
 * Sanitizes a string to allow basic HTML tags for content.
 * Be careful with this, as it allows HTML. Use for trusted inputs (e.g., admins).
 *
 * @param string $input The HTML string to sanitize.
 * @return string The sanitized HTML string.
 */
function sanitize_html(string $input): string {
    $allowed_tags = '<p><h1><h2><h3><h4><h5><h6><b><i><u><ul><ol><li><a><br><strong><em><blockquote><code><pre>';
    // Need to also handle attributes like href for <a> tags.
    // A more robust solution like HTML Purifier is recommended for production apps.
    // For this context, we will do a simple strip_tags and then rely on CSP.
    return strip_tags($input, $allowed_tags);
}

/**
 * A comprehensive sanitization function for user-provided data.
 *
 * @param mixed $data The data to sanitize.
 * @return mixed The sanitized data.
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    if (is_string($data)) {
        return sanitize_string($data);
    }
    return $data;
}


/**
 * Escapes a string for safe HTML output.
 * A simple wrapper around htmlspecialchars for consistency.
 *
 * @param string|null $string The string to escape.
 * @return string The escaped string.
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generates a random, URL-safe string.
 *
 * @param int $length The length of the string to generate.
 * @return string
 * @throws Exception
 */
function generate_random_token(int $length = 32): string {
    return bin2hex(random_bytes($length));
}

/**
 * Sets crucial security headers.
 * While most are set in .htaccess, this provides a PHP-level backup and is good practice.
 * The Content-Security-Policy is complex and best managed in .htaccess for static files.
 * This function can be used to add dynamic values to CSP if needed.
 */
function set_security_headers(): void {
    // Prevent MIME-sniffing
    header('X-Content-Type-Options: nosniff');

    // Prevent clickjacking
    header('X-Frame-Options: DENY');

    // Control information sent to other sites
    header('Referrer-Policy: same-origin');

    // More restrictive than X-Frame-Options
    if (!headers_sent()) {
        // This header is powerful and can replace X-Frame-Options.
        // It's defined in .htaccess, but we set a restrictive default here.
        // header("Content-Security-Policy: frame-ancestors 'none'");
    }

    // Permissions Policy to disable features
    $permissions_policy = [
        'camera=()',
        'microphone=()',
        'geolocation=()',
        'payment=()',
        'usb=()',
        'magnetometer=()',
        'gyroscope=()',
        'accelerometer=()'
    ];
    header('Permissions-Policy: ' . implode(', ', $permissions_policy));
}

/**
 * Verifies that a request method is what is expected.
 *
 * @param string $method (e.g., 'POST', 'GET')
 */
function verify_request_method(string $method): void {
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
        http_response_code(405); // Method Not Allowed
        // In a real app, you'd have a nice error page or JSON response.
        die("Invalid request method.");
    }
}
