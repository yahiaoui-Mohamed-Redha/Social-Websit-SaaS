<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../db.php';

// تمكين تسجيل الأخطاء للتصحيح
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// التحقق من أن الطلب POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير مسموح بها']);
    exit;
}

// الحصول على بيانات JSON من الطلب
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// التحقق من وجود البيانات المطلوبة
if (!isset($data['full_name']) || !isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'بيانات ناقصة']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // تنظيف البيانات
    $full_name = trim($data['full_name']);
    $username = trim($data['username']);
    $email = trim($data['email']);
    $password = $data['password'];

    // التحقق من صحة البريد الإلكتروني
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'بريد إلكتروني غير صالح']);
        exit;
    }

    // التحقق من عدم وجود اسم مستخدم أو بريد مكرر
    $query = "SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'اسم المستخدم أو البريد الإلكتروني موجود مسبقًا']);
        exit;
    }

    // تشفير كلمة المرور
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // إدراج المستخدم الجديد
    $query = "INSERT INTO users (full_name, username, email, password) VALUES (:full_name, :username, :email, :password)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);

    if ($stmt->execute()) {
        // بدء الجلسة
        session_start();
        $_SESSION['user_id'] = $db->lastInsertId();
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        
        echo json_encode([
            'success' => true, 
            'redirect' => '../public/dashboard.php',
            'message' => 'تم إنشاء الحساب بنجاح'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'خطأ في إنشاء الحساب']);
    }
} catch(PDOException $e) {
    // تسجيل الخطأ في ملف السجلات
    error_log("PDOException in register.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false, 
        'message' => 'حدث خطأ في قاعدة البيانات',
        'error' => $e->getMessage() // فقط لأغراض التصحيح، أزل هذا في الإنتاج
    ]);
} catch(Exception $e) {
    error_log("Exception in register.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false, 
        'message' => 'حدث خطأ غير متوقع',
        'error' => $e->getMessage() // فقط لأغراض التصحيح
    ]);
}