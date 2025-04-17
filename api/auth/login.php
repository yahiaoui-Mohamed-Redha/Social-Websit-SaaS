<?php
// Enable full error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session with error handling
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
} catch (Exception $e) {
    error_log("Session start error: " . $e->getMessage());
    header('Location: /public/login.php?error=db1');
    exit;
}

// Include required files with path verification
$required_files = [
    '../security.php',
    '../db.php'
];

foreach ($required_files as $file) {
    if (!file_exists($file)) {
        error_log("Critical file missing: " . $file);
        header('Location: ../../public/login.php?error=db2');
        exit;
    }
    require_once $file;
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (!function_exists('validateSession')) {
        error_log("Security function validateSession missing");
        header('Location: ../../public/login.php?error=db3');
        exit;
    }
    
    if (validateSession()) {
        header('Location: ../../dist/dashboard.php');
        exit;
    }
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
            error_log("CSRF token missing");
            header('Location: ../../public/login.php?error=csrf');
            exit;
        }
        
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            error_log("CSRF token mismatch");
            header('Location: ../../public/login.php?error=csrf');
            exit;
        }

        // Validate required fields
        if (empty($_POST['email'])) {
            error_log("Empty email in login attempt");
            header('Location: ../../public/login.php?error=missing');
            exit;
        }
        
        if (empty($_POST['password'])) {
            error_log("Empty password in login attempt");
            header('Location: ../../public/login.php?error=missing');
            exit;
        }

        // Sanitize inputs
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $remember_me = isset($_POST['remember_me']);

        // Database connection with detailed error handling
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            if (!$db) {
                throw new Exception("Database connection failed - null returned");
            }
            
            // Test connection with a simple query
            $testQuery = $db->query("SELECT 1");
            if (!$testQuery) {
                throw new Exception("Database test query failed");
            }
            
        } catch (PDOException $e) {
            error_log("PDO Connection Error: " . $e->getMessage());
            error_log("PDO Error Info: " . print_r(isset($db) ? $db->errorInfo() : [], true));
            throw $e;
        }

        // Check login attempts
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!checkLoginAttempts($ip)) {
            error_log("Too many login attempts from IP: " . $ip);
            header('Location: ../../public/login.php?error=attempts');
            exit;
        }

        // Prepare and execute user query
        $query = "SELECT id, username, email, password, is_active, login_attempts 
                  FROM users 
                  WHERE email = :email 
                  LIMIT 1";
                  
        $stmt = $db->prepare($query);
        if (!$stmt) {
            error_log("Failed to prepare statement: " . print_r($db->errorInfo(), true));
            throw new Exception("Database statement preparation failed");
        }
        
        $stmt->bindParam(':email', $email);
        
        if (!$stmt->execute()) {
            error_log("Query execution failed: " . print_r($stmt->errorInfo(), true));
            throw new Exception("Database query execution failed");
        }

        // Check if user exists
        if ($stmt->rowCount() === 0) {
            logFailedAttempt($ip, $email);
            error_log("Login attempt for non-existent email: " . $email);
            header('Location: ../../public/login.php?error=invalid');
            exit;
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password
        if (!password_verify($password, $user['password'])) {
            incrementLoginAttempts($user['id']);
            logFailedAttempt($ip, $email);
            error_log("Invalid password for user: " . $email);
            header('Location: ../../public/login.php?error=invalid');
            exit;
        }

        // Check if account is active
        if (!$user['is_active']) {
            error_log("Login attempt for inactive account: " . $email);
            header('Location: ../../public/login.php?error=inactive');
            exit;
        }

        // Successful login - create session
        session_regenerate_id(true);
        
        $_SESSION = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'logged_in' => true,
            'ip_address' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'last_activity' => time()
        ];

        // Generate new CSRF token for the session
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        // Remember me functionality
        if ($remember_me) {
            $cookieParams = session_get_cookie_params();
            setcookie(
                session_name(),
                session_id(),
                time() + 60 * 60 * 24 * 30, // 30 days
                $cookieParams["path"],
                $cookieParams["domain"],
                $cookieParams["secure"],
                $cookieParams["httponly"]
            );
        }

        // Reset login attempts
        resetLoginAttempts($user['id']);
        logSuccessfulAttempt($ip, $email);

        // Redirect to dashboard
        header('Location: ../../dist/dashboard.php');
        exit;

    } catch (PDOException $e) {
        error_log("Login PDOException: " . $e->getMessage());
        error_log("SQL State: " . $e->getCode());
        error_log("Error Info: " . print_r(isset($db) ? $db->errorInfo() : [], true));
        
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            die("Database error details: " . $e->getMessage());
        }
        
        header('Location: /public/login.php?error=db4');
        exit;
    } catch (Exception $e) {
        error_log("Login Exception: " . $e->getMessage());
        header('Location: ../../public/login.php?error=db5');
        exit;
    }
}

// If not a POST request or other direct access
header('Location: /public/login.php');
exit;