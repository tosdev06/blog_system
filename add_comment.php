<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = sanitizeInput($_POST['post_id']);
    $author_name = sanitizeInput($_POST['author_name']);
    $author_email = sanitizeInput($_POST['author_email']);
    $content = sanitizeInput($_POST['content']);
    
    // Validate inputs
    if (empty($author_name) || empty($author_email) || empty($content)) {
        $_SESSION['comment_error'] = "All fields are required.";
        header("Location: post.php?id=$post_id");
        exit();
    }
    
    if (!filter_var($author_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['comment_error'] = "Please enter a valid email address.";
        header("Location: post.php?id=$post_id");
        exit();
    }
    
    // Insert comment
    $sql = "INSERT INTO comments (post_id, author_name, author_email, content) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $post_id, $author_name, $author_email, $content);
    
    if ($stmt->execute()) {
        $_SESSION['comment_success'] = "Comment submitted successfully!";
    } else {
        $_SESSION['comment_error'] = "Error submitting comment. Please try again.";
    }
    
    header("Location: post.php?id=$post_id");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>