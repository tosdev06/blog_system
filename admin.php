<?php 
include 'config.php';

if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Handle post deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = sanitizeInput($_GET['delete']);
    
    // First, delete comments associated with the post
    $delete_comments_sql = "DELETE FROM comments WHERE post_id = ?";
    $stmt = $conn->prepare($delete_comments_sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    
    // Then delete the post
    $delete_sql = "DELETE FROM posts WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $success = "Post deleted successfully.";
    } else {
        $error = "Error deleting post.";
    }
}

// Get all posts
$sql = "SELECT * FROM posts ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Tosdev</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php">Tosdev</a></h1>
            <p>Admin Dashboard</p>
        </div>
    </header>

    <nav>
        <div class="container">
            <ul>
                <li><a href="index.php">View Blog</a></li>
                <li><a href="admin.php">Manage Posts</a></li>
                <li><a href="add_post.php">Add New Post</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <?php if (isset($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <h2>Manage Blog Posts</h2>
        <a href="add_post.php" class="add-post-btn">Add New Post</a>
        
        <div class="posts-list">
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="edit_post.php?id=<?php echo $row['id']; ?>" class="edit-btn"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="admin.php?delete=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this post?');"><i class="fas fa-trash-alt"></i> Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No blog posts found.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Tosdev. All rights reserved.</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>