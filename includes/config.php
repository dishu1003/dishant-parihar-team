<?php
/**
 * Global Configuration File
 *
 * Contains all the configuration variables for the application.
 * It's crucial to update these settings for your environment.
 */

// --- ERROR REPORTING ---
// In production, this should be 0 to avoid leaking server info. Errors should be logged to a file.
error_reporting(0);
ini_set('display_errors', 0);

// --- SITE CONFIGURATION ---
// IMPORTANT: Use https:// for production. This URL is used for generating absolute links.
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost/automated-income-system/public'); // Example: https://team.yourdomain.com
define('SITE_NAME', 'Asclepius Wellness HQ');

// --- DATABASE CONFIGURATION ---
// It is strongly recommended to use environment variables for sensitive data.
// In your hosting (e.g., Hostinger hPanel), you can set these variables.
// Example: DB_HOST=localhost, DB_NAME=u12345_dbname, DB_USER=u12345_user, DB_PASS=YourStrongPassword
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'asclepius_ais');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// --- AUTHENTICATION & SESSION ---
define('SESSION_LIFETIME', 1800); // Session lifetime in seconds (30 minutes)
define('SESSION_NAME', 'ASCLEPIUS_SESSID');
define('PASSWORD_ALGO', PASSWORD_ARGON2ID); // Algorithm for password hashing
define('OTP_LIFETIME', 600); // OTP validity in seconds (10 minutes)

// --- EMAIL CONFIGURATION ---
// This email address must be a valid sender on your hosting account (e.g., created in hPanel).
define('EMAIL_FROM', 'no-reply@asclepius.local');
define('EMAIL_FROM_NAME', 'Asclepius Wellness HQ');

// --- FILE UPLOADS ---
// IMPORTANT: This path should ideally be OUTSIDE the public web root for security.
// If using a shared host like Hostinger, you might create a directory parallel to 'public_html'.
// Example: /home/u123456789/uploads
// For this project structure, we place it one level above the public root.
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads');
define('ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/png',
    'application/pdf',
    'text/plain'
]);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB

// --- APPLICATION SETTINGS ---
// Toggle whether admin approval is required for new user registrations.
define('REQUIRE_ADMIN_APPROVAL_FOR_REGISTRATION', false);

// Set the application's default timezone
date_default_timezone_set('Asia/Kolkata');

// --- SECURITY ---
// A secret key for hashing or other security-related functions.
// This MUST be a long, random string. Set this as an environment variable.
// You can generate a suitable key using: openssl rand -hex 32
define('APP_SECRET_KEY', getenv('APP_SECRET_KEY') ?: 'default-insecure-key-change-me');
