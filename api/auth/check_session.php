<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "unauthenticated"]);
    exit;
}

echo json_encode([
    "status" => "authenticated",
    "user_id" => $_SESSION['user_id'],
    "full_name" => $_SESSION['full_name']
]);
