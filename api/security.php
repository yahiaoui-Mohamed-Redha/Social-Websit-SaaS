<?php
// Security Functions
require_once 'db.php';
/**
 * Validate user session
 */
function validateSession() {
    if (!isset($_SESSION['ip_address'], $_SESSION['user_agent'], $_SESSION['last_activity'])) {
        return false;
    }
    
    if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
        return false;
    }
    
    if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        return false;
    }
    
    if (time() - $_SESSION['last_activity'] > SESSION_LIFETIME) {
        session_unset();
        session_destroy();
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Validate CSRF token
 */

// Generate CSRF token on session start
function generateCsrfToken() {
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    return $token;
}

// Validate CSRF token
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Regenerate CSRF token on each request
function regenerateCsrfToken() {
    $newToken = generateCsrfToken();
    $_SESSION['csrf_token'] = $newToken;
    return $newToken;
}

/**
 * Check login attempts from an IP address
 */
function checkLoginAttempts($ip) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT COUNT(*) as attempts FROM login_attempts 
                  WHERE ip_address = :ip AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':ip', $ip);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['attempts'] < MAX_LOGIN_ATTEMPTS);
    } catch (PDOException $e) {
        error_log("Database error in checkLoginAttempts: " . $e->getMessage());
        return false;
    }
}

/**
 * Log failed login attempt
 */
function logFailedAttempt($ip, $email) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO login_attempts (ip_address, email, attempt_time) 
                  VALUES (:ip, :email, NOW())";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':email', $email);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Database error in logFailedAttempt: " . $e->getMessage());
        return false;
    }
}

/**
 * Increment user login attempts
 */
function incrementLoginAttempts($user_id) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "UPDATE users SET login_attempts = login_attempts + 1 WHERE id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Database error in incrementLoginAttempts: " . $e->getMessage());
        return false;
    }
}

/**
 * Reset user login attempts
 */
function resetLoginAttempts($user_id) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "UPDATE users SET login_attempts = 0 WHERE id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Database error in resetLoginAttempts: " . $e->getMessage());
        return false;
    }
}

/**
 * Log successful login
 */
function logSuccessfulAttempt($ip, $email) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO login_logs (ip_address, email, status, login_time) 
                  VALUES (:ip, :email, 'success', NOW())";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':email', $email);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Database error in logSuccessfulAttempt: " . $e->getMessage());
        return false;
    }
}