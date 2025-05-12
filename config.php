<?php
// Important: No whitespace or output before <?php
session_start();

// Use environment variables
define('DB_HOST', getenv('Host'));
define('DB_USER', getenv('User'));
define('DB_PASS', getenv('Password'));
define('DB_NAME', getenv('Database_name'));
define('DB_PORT', getenv('Port') ?: 3306);

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// ==============================================
// DATABASE SCHEMA SETUP
// ==============================================

// Create database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS ".DB_NAME." CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db(DB_NAME);

// Table for blog posts
$conn->query("CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_path VARCHAR(255),
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Table for comments
$conn->query("CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    author_email VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Admin user table
$conn->query("CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Insert default admin (password: admin123)
// Note: In production, you should change this!
$defaultAdminQuery = $conn->prepare("INSERT IGNORE INTO admin_users (username, password_hash) VALUES (?, ?)");
$defaultAdminQuery->bind_param("ss", "admin", '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
$defaultAdminQuery->execute();

// ==============================================
// HELPER FUNCTIONS
// ==============================================

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

    // Verify image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ['success' => false, 'message' => 'File is not an image.'];
    }

    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        return ['success' => false, 'message' => 'Sorry, your file is too large.'];
    }

    // Allow certain file formats
    if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG & GIF files are allowed.'];
    }

    // Upload file
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return ['success' => true, 'path' => $targetFile];
    } else {
        return ['success' => false, 'message' => 'Sorry, there was an error uploading your file.'];
    }
}
?>
