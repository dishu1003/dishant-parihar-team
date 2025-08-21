# Automated Income System for Asclepius Wellness Pvt. Ltd.

This is a complete, production-ready Digital HQ, Personal CRM, AI Mentor, and Training Hub for Asclepius Wellness team members. This document provides all necessary instructions for deployment on Hostinger, configuration, and maintenance.

## Table of Contents

1.  [Technology Stack](#technology-stack)
2.  [Project Structure](#project-structure)
3.  [Deployment to Hostinger](#deployment-to-hostinger)
    - [Step 1: Database Setup](#step-1-database-setup)
    - [Step 2: Upload Files](#step-2-upload-files)
    - [Step 3: Configure the Application](#step-3-configure-the-application)
    - [Step 4: Set Up Cron Job (Optional but Recommended)](#step-4-set-up-cron-job-optional-but-recommended)
4.  [Admin Credentials](#admin-credentials)
5.  [Security Checklist](#security-checklist)
6.  [PWA (Progressive Web App) Features](#pwa-features)
7.  [Developer Notes](#developer-notes)

---

## 1. Technology Stack

-   **Backend:** PHP 8.1+ (OOP)
-   **Database:** MySQL 8+
-   **Frontend:** HTML5, CSS3, Vanilla JavaScript (ES6 Modules)
-   **Constraints:** No frameworks (e.g., Laravel, React). No external paid APIs.

## 2. Project Structure

The project follows a clean separation of concerns:

-   `/public/`: The web root. Contains all publicly accessible files.
    -   `.htaccess`: Handles security headers, caching, and CSP.
    -   `index.php`: Main application entry point/router.
    -   `assets/`: Compiled CSS, JS, and images.
    -   `views/`: PHP template files for the UI.
-   `/includes/`: Core backend logic (DB, Auth, Security, etc.). **This directory should not be publicly accessible.**
-   `/api/`: Backend endpoints for AJAX requests.
-   `/sql/`: Database schema and seed files.

## 3. Deployment to Hostinger

Follow these steps to get the application running on your Hostinger account.

### Step 1: Database Setup

1.  **Log in to your Hostinger hPanel.**
2.  Navigate to **Databases** -> **MySQL Databases**.
3.  Create a new database. Note down the **database name**, **user**, and **password**. Hostinger often prefixes them (e.g., `u123456789_dbname`).
4.  Once the database is created, click **Enter phpMyAdmin** next to your new database.
5.  Inside phpMyAdmin, select your database from the left sidebar.
6.  Click the **Import** tab.
7.  Under "File to import", click **Choose File** and select the `sql/schema.sql` file from this project. Click **Go**.
8.  After the schema is imported, click **Import** again.
9.  This time, choose the `sql/seed.sql` file and import it. This will load the initial data, including the admin user and sample content.

### Step 2: Upload Files

1.  In your hPanel, go to **Files** -> **File Manager**.
2.  Navigate to your domain's root directory (usually `public_html`).
3.  **IMPORTANT**: The web root should be the `/public` directory of this project for security reasons. You have two options:
    -   **Option A (Recommended - Subdomain):** Create a subdomain (e.g., `team.yourdomain.com`). In the hPanel, point the subdomain's root directory to `public_html/team_app/public`. Then upload the entire project into `public_html/team_app/`.
    -   **Option B (Main Domain):** Upload the *contents* of the `/public` directory into `public_html`. Then, create a directory *outside* `public_html` (e.g., at the same level) called `app_includes` and upload the `/includes`, `/api`, and `/sql` directories there. You will need to adjust the paths in `includes/config.php` accordingly.
4.  For simplicity, we'll assume you are uploading the entire project folder and can point your domain/subdomain root to the `/public` folder.
5.  Use the File Manager's **Upload Files** feature. It's best to ZIP the project on your computer and upload the single ZIP file, then use the **Extract** tool in the File Manager.

### Step 3: Configure the Application

1.  In the File Manager, navigate to the `/includes` directory.
2.  Right-click on `config.php` and select **Edit**.
3.  Update the following values with the details from Step 1 and your domain information:

    ```php
    // --- DATABASE CONFIGURATION ---
    define('DB_HOST', 'localhost'); // Usually 'localhost' on Hostinger
    define('DB_NAME', 'u123456789_dbname'); // Your database name
    define('DB_USER', 'u123456789_dbuser'); // Your database user
    define('DB_PASS', 'YourDatabasePassword'); // Your database password

    // --- SITE CONFIGURATION ---
    define('SITE_URL', 'https://team.yourdomain.com'); // IMPORTANT: Use https://
    define('SITE_NAME', 'Asclepius Wellness HQ');

    // --- EMAIL CONFIGURATION ---
    define('EMAIL_FROM', 'no-reply@yourdomain.com'); // An email address that exists on your Hostinger account
    ```

4.  Save the file. The application should now be live.

### Step 4: Set Up Cron Job (Optional but Recommended)

To send daily email reminders for CRM follow-ups, you can set up a cron job.

1.  In hPanel, go to **Advanced** -> **Cron Jobs**.
2.  Under **"PHP script to run"**, enter the full path to the cron script. The path will be something like:
    `php /home/u123456789/domains/yourdomain.com/public_html/api/cron/send_reminders.php`
    *(You may need to verify this path with Hostinger support or by checking your account details)*
3.  Choose a "Common schedule" like **Once a day**.
4.  Save the cron job.

## 4. Admin Credentials

After seeding the database, the default admin account is:

-   **Email:** `admin@asclepius.local`
-   **Password:** `ChangeMe@123`

**IMPORTANT:** The system is designed to force a password reset on the first login for this default account. You will be prompted to change it immediately for security.

## 5. Security Checklist

This application is built with security in mind.

-   **CSP & Security Headers:** The `.htaccess` file implements a strict Content-Security-Policy (CSP), HSTS, and other security headers. **Do not use inline JavaScript (`<script>...</script>` or `onclick="..."`) or inline CSS (`style="..."`) as it will be blocked by the CSP.**
-   **Passwords:** Passwords are hashed using `Argon2id`.
-   **CSRF Protection:** All state-changing forms are protected with CSRF tokens.
-   **2FA:** Two-Factor Authentication via email OTP is enforced on login.
-   **File Uploads:** File uploads are restricted to whitelisted MIME types, given random filenames, and stored in a secure location.
-   **Database:** All database queries are executed using prepared statements (PDO) to prevent SQL injection.

## 6. PWA (Progressive Web App) Features

The application includes a service worker to enable PWA functionality:

-   **Installable:** Users can "Add to Home Screen" on supported devices for an app-like experience.
-   **Offline Access:** The app shell, critical pages, and static assets are cached, allowing the app to load even without an internet connection.
-   **Offline CRM Sync:** New leads added while offline are stored locally in the browser (IndexedDB) and will be automatically synced to the server when the connection is restored.

## 7. Developer Notes

-   **CSS:** The main stylesheet is `/public/assets/css/style.css`. It is written using a BEM-like methodology for clear and maintainable component styles. It includes a dark mode theme.
-   **JavaScript:** All JavaScript is modular and located in `/public/assets/js/`. The entry point is `main.js`. All scripts are loaded with `defer` to prevent render-blocking.
-   **AI-Mentor:** The logic for the AI-Mentor tips is contained in `/public/assets/js/ai_mentor.js`. It is a simple, client-side rule engine that can be easily extended with new rules.
-   **No Build Step:** This project is intentionally designed to run without any build or compilation steps (like Node.js, Webpack, etc.). You can edit the files directly and see the changes. Minified assets are provided for production, but development versions are also included.
