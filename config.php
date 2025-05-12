<?php
session_start();

// Use environment variables
define('DB_HOST', getenv('host'));
define('DB_USER', getenv('User'));
define('DB_PASS', getenv('Password'));
define('DB_NAME', getenv('Database-name'));
define('DB_PORT', getenv('Port') ?: 3306);

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Helper functions
function sanitizeInput($data) {
    global $conn;
    return htmlspecialchars(stripcslashes(trim($conn->real_escape_string($data))));
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function uploadImage($file) {
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = uniqid() . '-' . basename($file["name"]);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ['success' => false, 'message' => 'File is not an image.'];
    }

    if ($file["size"] > 5000000) {
        return ['success' => false, 'message' => 'Sorry, your file is too large.'];
    }

    if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG & GIF files are allowed.'];
    }

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return ['success' => true, 'path' => $targetFile];
    } else {
        return ['success' => false, 'message' => 'Sorry, there was an error uploading your file.'];
    }
}
?>
