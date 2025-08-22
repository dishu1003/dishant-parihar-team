<?php
/**
 * API Bootstrap & Security Handler
 *
 * This script provides a centralized security entry point for API endpoints.
 */

// Load core dependencies
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';

class ApiSecurity {
    /**
     * Protects an API endpoint with a series of security checks.
     *
     * @param array $options Configuration for the security checks.
     *                        'require_auth' (bool): If true, requires the user to be logged in. Defaults to true.
     *                        'require_otp' (bool): If true, requires the user to have passed OTP verification. Defaults to true.
     *                        'require_csrf' (bool): If true, verifies the CSRF token. Defaults to true for state-changing methods (POST, PUT, DELETE).
     *                        'allowed_method' (string|null): If set (e.g., 'POST'), verifies the request method.
     *                        'role' (string|null): If set (e.g., 'admin'), requires the user to have that role.
     */
    public static function protect(array $options = []): void {
        $request_method = $_SERVER['REQUEST_METHOD'];

        // Set default options
        $config = array_merge([
            'require_auth' => true,
            'require_otp' => true,
            'require_csrf' => in_array($request_method, ['POST', 'PUT', 'DELETE']),
            'allowed_method' => null,
            'role' => null,
        ], $options);

        // Set JSON header immediately
        header('Content-Type: application/json');

        // Start a secure session
        start_secure_session();

        // Verify CSRF Token if required
        if ($config['require_csrf']) {
            CSRF::verifyRequest();
        }

        // Verify request method if specified
        if ($config['allowed_method']) {
            self::verify_request_method($config['allowed_method']);
        }

        // Check Authentication if required
        if ($config['require_auth'] && !is_logged_in()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Authentication required.']);
            exit();
        }

        // Check OTP verification if required
        if ($config['require_auth'] && $config['require_otp'] && !is_otp_verified()) {
            http_response_code(403); // Forbidden, as they are logged in but not OTP-verified
            echo json_encode(['success' => false, 'message' => 'OTP verification required.']);
            exit();
        }

        // Check for required role if specified
        if ($config['role'] !== null) {
            if (!has_role($config['role'])) {
                http_response_code(403); // Forbidden
                echo json_encode(['success' => false, 'message' => 'You do not have permission to access this resource.']);
                exit();
            }
        }
    }

    /**
     * Verifies that the request method is the expected one, and returns a proper JSON response on failure.
     *
     * @param string $method The expected method (e.g., 'POST', 'GET').
     */
    private static function verify_request_method(string $method): void {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['success' => false, 'message' => "Invalid request method. Expected {$method}."]);
            exit();
        }
    }
}
