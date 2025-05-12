<?php include 'config.php'; ?>
<?php 
// Simple Markdown link parser
function parseMarkdownLinks($text) {
    // Convert markdown links [text](url) to HTML
    $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank">$1</a>', $text);
    // Convert markdown images ![alt](url) to HTML
    $text = preg_replace('/!\[([^\]]+)\]\(([^)]+)\)/', '<img src="$2" alt="$1" class="post-image">', $text);
    // Convert bold **text** to HTML
    $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);
    // Convert italic _text_ to HTML
    $text = preg_replace('/_([^_]+)_/', '<em>$1</em>', $text);
    return nl2br($text);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tosdev</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php">Tosdev</a></h1>
            <p>A software engineer's blog about development, projects, and more</p>
        </div>
    </header>

    <nav>
        <div class="container">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php?category=Web Development">Web Dev</a></li>
                <li><a href="index.php?category=PHP">PHP</a></li>
                <li><a href="index.php?category=Projects">Projects</a></li>
                <?php if (isAdminLoggedIn()): ?>
                    <li><a href="admin.php">Admin</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container main-content">
        <main>
            <?php
            $category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
            
            if ($category) {
                $sql = "SELECT * FROM posts WHERE category = ? ORDER BY created_at DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $category);
                $stmt->execute();
                $result = $stmt->get_result();
                echo "<h2>Category: $category</h2>";
            } else {
                $sql = "SELECT * FROM posts ORDER BY created_at DESC";
                $result = $conn->query($sql);
            }
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $excerpt = strlen ($row['content']) > 200 ? substr($row['content'], 0, 200) . '...' : $row['content'];
                    ?>
                    <article class="post">
                        <?php if ($row['image_path']): ?>
                            <div class="post-image">
                                <img src="<?php echo $row['image_path']; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="post-content">
                            <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                            <div class="post-meta">
                                <span class="category"><?php echo htmlspecialchars($row['category']); ?></span>
                                <span class="date"><?php echo date('F j, Y', strtotime($row['created_at'])); ?></span>
                            </div>
                            <p><?php echo parseMarkdownLinks(htmlspecialchars($excerpt)); ?></p>
                            <a href="post.php?id=<?php echo $row['id']; ?>" class="read-more">Read More</a>
                        </div>
                    </article>
                    <?php
                }
            } else {
                echo "<p>No blog posts found.</p>";
            }
            ?>
        </main>

        <aside class="sidebar">
            <div class="sidebar-widget">
                <h3>About the Author</h3>
                <p>Olawuyi Tofunmi, also known as TOSDEV, is a passionate 
                    Software Engineering student at Babcock University. 
                    With a strong dedication to innovation and problem-solving, 
                    TOSDEV specializes in developing cutting-edge software solutions
                     that enhance user experiences and drive business growth.</p>
            </div>
            
            <div class="sidebar-widget">
                <h3>Categories</h3>
                <ul class="categories">
                    <li><a href="index.php?category=Web Development">Web Development</a></li>
                    <li><a href="index.php?category=PHP">Python</a></li>
                    <li><a href="index.php?category=Projects">Projects</a></li>
                </ul>
            </div>
            
            <div class="sidebar-widget">
                <h3>Subscribe</h3>
                <form id="subscribe-form">
                    <input type="email" placeholder="Your email" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </aside>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Tosdev. All rights reserved.</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
