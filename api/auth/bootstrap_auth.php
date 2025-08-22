<?php
/**
 * Auth API Bootstrap & Security Handler
 *
 * This script handles common tasks for public authentication API endpoints.
 * - Sets JSON content type header.
 * - Loads all required core files.
 * - Starts a secure session.
 * - Verifies CSRF token.
 */

// Set JSON header immediately
header('Content-Type: application/json');

// Load core files
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';

// --- Execute Security Checks ---

// 1. Start session
start_secure_session();

// 2. Verify CSRF Token
CSRF::verifyRequest();

/**
 * Verifies that the request method is the expected one, and returns a proper JSON response on failure.
 * This function is duplicated from the main bootstrap file for convenience.
 *
 * @param string $method The expected method (e.g., 'POST', 'GET').
 */
function api_verify_request_method(string $method): void {
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
        http_response_code(405); // Method Not Allowed
        echo json_encode(['success' => false, 'message' => "Invalid request method. Expected {$method}."]);
        exit();
    }
}
