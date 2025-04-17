<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'social_portfolio');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DEBUG_MODE', true); // Set to false in production

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('SESSION_NAME', 'socialfolio_session');
define('MAX_LOGIN_ATTEMPTS', 5);

// Security Settings
define('BASE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));

// Initialize session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    
    // Start session with error handling
    try {
        if (!session_start()) {
            throw new RuntimeException('Failed to start session');
        }
    } catch (Exception $e) {
        error_log("Session start failed: " . $e->getMessage());
        if (DEBUG_MODE) {
            die("Session error: " . $e->getMessage());
        }
    }
}

// Auto-generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
