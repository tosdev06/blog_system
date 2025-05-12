<?php 
include 'config.php';

if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin.php");
    exit();
}

$post_id = sanitizeInput($_GET['id']);
$error = '';
$success = '';

// Get post data
$sql = "SELECT * FROM posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: admin.php");
    exit();
}

$post = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $content = sanitizeInput($_POST['content']);
    $category = sanitizeInput($_POST['category']);
    $image_path = $post['image_path'];
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadImage($_FILES['image']);
        if ($upload_result['success']) {
            // Delete old image if it exists
            if (!empty($image_path)) {
                @unlink($image_path);
            }
            $image_path = $upload_result['path'];
        } else {
            $error = $upload_result['message'];
        }
    }
    
    // Handle image removal
    if (isset($_POST['remove_image']) && $_POST['remove_image'] === 'on') {
        if (!empty($image_path)) {
            @unlink($image_path);
        }
        $image_path = '';
    }
    
    if (empty($error)) {
        $sql = "UPDATE posts SET title = ?, content = ?, image_path = ?, category = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $title, $content, $image_path, $category, $post_id);
        
        if ($stmt->execute()) {
            $success = "Post updated successfully!";
            // Refresh post data
            $post['title'] = $title;
            $post['content'] = $content;
            $post['category'] = $category;
            $post['image_path'] = $image_path;
        } else {
            $error = "Error updating post: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post | Tech Blog</title>
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
        <h2>Edit Blog Post</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form action="edit_post.php?id=<?php echo $post_id; ?>" method="POST" enctype="multipart/form-data" class="post-form">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="Web Development" <?php echo ($post['category'] === 'Web Development') ? 'selected' : ''; ?>>Web Development</option>
                    <option value="PHP" <?php echo ($post['category'] === 'PHP') ? 'selected' : ''; ?>>Python</option>
                    <option value="Projects" <?php echo ($post['category'] === 'Projects') ? 'selected' : ''; ?>>Projects</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="image">Featured Image (optional)</label>
                <input type="file" id="image" name="image" accept="image/*">
                <?php if (!empty($post['image_path'])): ?>
                    <div class="current-image">
                        <p>Current Image:</p>
                        <img src="<?php echo $post['image_path']; ?>" alt="Current featured image" style="max-width: 200px;">
                        <label>
                            <input type="checkbox" name="remove_image"> Remove image
                        </label>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="content">Content</label>
                <div class="editor-toolbar">
                    <button type="button" class="editor-btn" data-command="bold" title="Bold"><strong>B</strong></button>
                    <button type="button" class="editor-btn" data-command="italic" title="Italic"><em>I</em></button>
                    <button type="button" class="editor-btn" data-command="insertLink" title="Insert Link"><i class="fas fa-link"></i></button>
                    <button type="button" class="editor-btn" data-command="insertImage" title="Insert Image"><i class="fas fa-image"></i></button>
                </div>
                <textarea id="content" name="content" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
            </div>
            
            <button type="submit">Update Post</button>
        </form>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Tosdev. All rights reserved.</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>