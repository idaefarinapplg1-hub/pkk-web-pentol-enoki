<?php
/**
 * Example Configuration File
 * Copy this to backend/config/config.php and modify as needed
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'siap_santap');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application Settings
define('APP_NAME', 'Siap Santap');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/siap-santap');

// Upload Settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Session Settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'siap_santap_session');

// Security Settings
define('ENABLE_CSRF', true);
define('ENABLE_RATE_LIMIT', true);
define('MAX_LOGIN_ATTEMPTS', 5);

// Email Settings (for future features)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-password');
define('SMTP_FROM', 'noreply@siapsantap.com');

// Payment Gateway Settings (for future features)
define('PAYMENT_GATEWAY_KEY', 'your-api-key');
define('PAYMENT_GATEWAY_SECRET', 'your-secret-key');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}
?>
