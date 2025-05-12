<?php 
include 'config.php';

if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $content = sanitizeInput($_POST['content']);
    $category = sanitizeInput($_POST['category']);
    $image_path = '';
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadImage($_FILES['image']);
        if ($upload_result['success']) {
            $image_path = $upload_result['path'];
        } else {
            $error = $upload_result['message'];
        }
    }
    
    if (empty($error)) {
        $sql = "INSERT INTO posts (title, content, image_path, category) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $title, $content, $image_path, $category);
        
        if ($stmt->execute()) {
            $success = "Post added successfully!";
            // Clear form
            $title = $content = $category = '';
        } else {
            $error = "Error adding post: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Post | Tosdev</title>
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
        <h2>Add New Blog Post</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form action="add_post.php" method="POST" enctype="multipart/form-data" class="post-form">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">Select a category</option>
                    <option value="Web Development" <?php echo (isset($category) && $category === 'Web Development') ? 'selected' : ''; ?>>Web Development</option>
                    <option value="PHP" <?php echo (isset($category) && $category === 'PHP') ? 'selected' : ''; ?>>PHP</option>
                    <option value="Projects" <?php echo (isset($category) && $category === 'Projects') ? 'selected' : ''; ?>>Projects</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="image">Featured Image (optional)</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>
            
            <div class="form-group">
                <label for="content">Content</label>
                <div class="editor-toolbar">
                    <button  background ="blue" type="button" class="editor-btn" data-command="bold" title="Bold"><strong  color="blue">B</strong></button>
                    <button type="button" class="editor-btn" data-command="italic" title="Italic"><em>I</em></button>
                    <button type="button" class="editor-btn" data-command="insertLink" title="Insert Link"><i class="fas fa-link"></i></button>
                    <button type="button" class="editor-btn" data-command="insertImage" title="Insert Image"><i class="fas fa-image"></i></button>
                </div>
                <textarea id="content" name="content" rows="10" required><?php echo isset($content) ? htmlspecialchars($content) : ''; ?></textarea>
            </div>
            
            <button type="submit">Publish Post</button>
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