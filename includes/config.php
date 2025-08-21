<?php
/**
 * Global Configuration File
 *
 * Contains all the configuration variables for the application.
 * It's crucial to update these settings for your environment.
 */

// --- ERROR REPORTING ---
// Set to 0 in production to avoid leaking server info. Set to E_ALL for development.
error_reporting(E_ALL);
ini_set('display_errors', 1); // Set to 0 in production

// --- SITE CONFIGURATION ---
// IMPORTANT: Use https:// for production. This URL is used for generating absolute links.
define('SITE_URL', 'http://localhost/automated-income-system/public'); // Example: https://team.yourdomain.com
define('SITE_NAME', 'Asclepius Wellness HQ');

// --- DATABASE CONFIGURATION ---
// Credentials for the MySQL database connection.
define('DB_HOST', 'localhost');
define('DB_NAME', 'asclepius_ais'); // Placeholder, change to your DB name
define('DB_USER', 'root'); // Placeholder, change to your DB user
define('DB_PASS', ''); // Placeholder, change to your DB password
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
// A secret key for hashing or other security-related functions. Change this to a long, random string.
define('APP_SECRET_KEY', 'def1$ec@d418a7*!ABC123XYZ');
