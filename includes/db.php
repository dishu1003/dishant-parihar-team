<?php
/**
 * Database Connection Manager
 *
 * Provides a single, persistent PDO database connection instance.
 */

// Require the configuration file, but only once.
require_once __DIR__ . '/config.php';

class DB {
    private static ?PDO $instance = null;

    /**
     * Get the PDO database connection instance.
     *
     * Creates the connection on the first call and returns the existing instance on subsequent calls.
     *
     * @return PDO The PDO database connection instance.
     * @throws PDOException if the connection fails.
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // In a real production environment, you would log this error and show a generic error page.
                // For development, it's okay to die and show the error.
                error_log('Database Connection Error: ' . $e->getMessage());
                // Never show detailed errors in production
                if (ini_get('display_errors') === '1') {
                    die('Database Connection Error: ' . $e->getMessage());
                } else {
                    die('A critical error occurred. Please try again later.');
                }
            }
        }

        return self::$instance;
    }

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct() {}

    /**
     * Private clone method to prevent cloning of the instance.
     */
    private function __clone() {}

    /**
     * Private unserialize method to prevent unserializing of the instance.
     */
    public function __wakeup() {}
}

/**
 * Helper function to easily get the PDO instance.
 * Example usage: $pdo = db();
 *
 * @return PDO
 */
function db(): PDO {
    return DB::getInstance();
}
