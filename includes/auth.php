<?php
/**
 * Authentication and Session Management
 *
 * Handles user login, logout, session security, OTP verification, and access control.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';
// mail.php will be included when needed to avoid circular dependencies if any
// require_once __DIR__ . '/mail.php';

/**
 * Starts a secure session with appropriate cookie settings.
 */
function start_secure_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // Remove port number from host, if present.
        $host = preg_replace('/:\d+$/', '', $host);
        // Prepending a dot is a common practice for cross-subdomain compatibility.
        // For 'localhost', we don't set a domain attribute.
        $domain = ($host !== 'localhost') ? '.' . $host : '';

        $cookieParams = [
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'domain' => $domain,
            'secure' => isset($_SERVER['HTTPS']), // Only send cookies over HTTPS
            'httponly' => true, // Prevent client-side script access
            'samesite' => 'Strict' // Prevent CSRF
        ];
        session_set_cookie_params($cookieParams);
        session_name(SESSION_NAME);
        session_start();
    }
}

/**
 * Regenerates the session ID to prevent session fixation attacks.
 */
function regenerate_session(): void {
    session_regenerate_id(true);
}

/**
 * Checks if a user is logged in (i.e., has a user ID in the session).
 *
 * @return bool
 */
function is_logged_in(): bool {
    start_secure_session();
    return isset($_SESSION['user_id']);
}

/**
 * Checks if the logged-in user has successfully passed OTP verification.
 *
 * @return bool
 */
function is_otp_verified(): bool {
    return isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true;
}

/**
 * Finds a user by their email address.
 *
 * @param string $email
 * @return array|false
 */
function find_user_by_email(string $email) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

/**
 * Finds a user by their ID.
 *
 * @param int $id
 * @return array|false
 */
function find_user_by_id(int $id) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Attempts to log in a user.
 *
 * @param string $email
 * @param string $password
 * @return bool True on success, false on failure.
 */
function login(string $email, string $password): bool {
    $user = find_user_by_email($email);

    if ($user && $user['is_active'] && password_verify($password, $user['password_hash'])) {
        start_secure_session();
        regenerate_session();

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['otp_verified'] = false; // Require OTP verification next
        $_SESSION['force_password_reset'] = (bool)$user['force_password_reset'];

        return true;
    }

    return false;
}

/**
 * Logs out the current user and destroys their session.
 */
function logout(): void {
    start_secure_session();
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Generates an OTP, stores it in the database, and returns it.
 *
 * @param int $userId
 * @return string The generated OTP.
 * @throws Exception
 */
function generate_otp(int $userId): string {
    $pdo = db();
    $otp = (string)random_int(100000, 999999);
    $expires = date('Y-m-d H:i:s', time() + OTP_LIFETIME);

    $stmt = $pdo->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE id = ?");
    $stmt->execute([$otp, $expires, $userId]);

    return $otp;
}

/**
 * Verifies a user-submitted OTP.
 *
 * @param int $userId
 * @param string $otp
 * @return bool
 */
function verify_otp(int $userId, string $otp): bool {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT otp_code, otp_expires_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if ($user && $user['otp_code'] === $otp && strtotime($user['otp_expires_at']) > time()) {
        // OTP is correct and not expired. Clear it.
        $stmt = $pdo->prepare("UPDATE users SET otp_code = NULL, otp_expires_at = NULL, last_login_at = NOW() WHERE id = ?");
        $stmt->execute([$userId]);

        $_SESSION['otp_verified'] = true;
        return true;
    }

    return false;
}

/**
 * Gets the current logged-in user's data array.
 *
 * @return array|null
 */
function get_current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    if (isset($_SESSION['user_data'])) {
        return $_SESSION['user_data'];
    }
    $user = find_user_by_id($_SESSION['user_id']);
    $_SESSION['user_data'] = $user;
    return $user;
}

/**
 * Gets the current logged-in user's ID.
 *
 * @return int|null
 */
function get_current_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Checks if the current user has a specific role.
 *
 * @param string $role ('admin' or 'member')
 * @return bool
 */
function has_role(string $role): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}
