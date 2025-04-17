<?php

require_once 'db.php';

function getUser($user_id) {
    $db = new Database();
    $conn = $db->getConnection();
    // Prepare the SQL query
    $stmt = $conn ->prepare("SELECT * FROM users WHERE id = ?");

    // Execute the query
    $stmt->execute([$user_id]);

    // Fetch the user data
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return the user data
    return $user;
}