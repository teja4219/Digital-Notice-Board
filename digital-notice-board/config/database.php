<?php
// config/database.php
// Database configuration — use environment variables in production.
// For local XAMPP, set values below or export env variables.

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'digital_notice_board');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME', 'Digital Notice Board');
define('APP_SHORT', 'DNB');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', '/digital-notice-board/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_TYPES', ['application/pdf']);
define('ALLOWED_EXT', ['pdf']);

/**
 * Returns a singleton PDO instance.
 */
function getPDO(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Do NOT expose DB errors publicly in production.
            error_log('Database connection failed: ' . $e->getMessage());
            http_response_code(503);
            die('<h2 style="font-family:sans-serif;text-align:center;margin-top:10vh">Service Temporarily Unavailable</h2><p style="text-align:center">Please check the database configuration.</p>');
        }
    }
    return $pdo;
}
