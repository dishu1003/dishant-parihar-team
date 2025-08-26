<?php
/**
 * CSRF (Cross-Site Request Forgery) Protection
 *
 * This class handles the generation and validation of CSRF tokens.
 */

// Depends on secure sessions and random token generation.
require_once __DIR__ . '/auth.php'; // For start_secure_session
require_once __DIR__ . '/security.php'; // For generate_random_token

class CSRF {
    private static string $token_name = 'csrf_token';

    /**
     * Generates a new CSRF token and stores it in the session.
     * Should be called on pages with forms.
     *
     * @return string The generated token.
     * @throws Exception
     */
    public static function generateToken(): string {
        // Ensure session is started securely
        if (session_status() === PHP_SESSION_NONE) {
            start_secure_session();
        }

        $token = generate_random_token(32);
        $_SESSION[self::$token_name] = $token;

        return $token;
    }

    /**
     * Validates a submitted CSRF token against the one in the session.
     *
     * @param string $submittedToken The token from the form/request body.
     * @return bool True if valid, false otherwise.
     */
    public static function validateToken(string $submittedToken): bool {
        if (session_status() === PHP_SESSION_NONE) {
            start_secure_session();
        }

        if (!isset($_SESSION[self::$token_name])) {
            return false;
        }

        // Use hash_equals for timing-attack-safe comparison
        return hash_equals($_SESSION[self::$token_name], $submittedToken);
    }

    /**
     * Returns the current token from the session.
     *
     * @return string|null
     */
    public static function getToken(): ?string {
        if (session_status() === PHP_SESSION_NONE) {
            start_secure_session();
        }
        return $_SESSION[self::$token_name] ?? null;
    }

    /**
     * Generates a hidden input field with the CSRF token.
     *
     * @return string HTML hidden input field.
     */
    public static function getInputField(): string {
        $token = self::getToken();
        if (!$token) {
            // If no token exists, generate one.
            try {
                $token = self::generateToken();
            } catch (Exception $e) {
                // Handle error appropriately, maybe log it and return an empty string
                error_log('CSRF token generation failed: ' . $e->getMessage());
                return '';
            }
        }
        return '<input type="hidden" name="' . self::$token_name . '" value="' . e($token) . '">';
    }

    /**
     * A utility function to check the token from POST data and die if invalid.
     * Useful for API endpoints.
     */
    public static function verifyRequest(): void {
        $token = $_POST[self::$token_name] ?? '';

        if (empty($token)) {
            // For AJAX requests, the token is sent in a header.
            // The header name is 'X-CSRF-Token', which PHP makes available as
            // $_SERVER['HTTP_X_CSRF_TOKEN']. This is more reliable than getallheaders().
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        }

        if (!self::validateToken($token)) {
            http_response_code(403); // Forbidden
            // In a real app, you'd have a nice error page or JSON response.
            die(json_encode(['error' => 'Invalid or missing CSRF token.']));
        }
    }
}
