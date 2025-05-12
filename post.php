
<?php 
include 'config.php'; 
// Enhanced Markdown parser with link titles
function parseMarkdownLinks($text) {
    // Convert markdown links [text](url "title") to HTML with title attribute
    $text = preg_replace_callback(
        '/\[([^\]]+)\]\(([^)"]+)(?:\s+"([^"]+)")?\)/',
        function($matches) {
            $linkText = $matches[1];
            $url = $matches[2];
            $title = isset($matches[3]) ? ' title="' . htmlspecialchars($matches[3]) . '"' : '';
            return '<a href="' . htmlspecialchars($url) . '"' . $title . ' target="_blank">' . htmlspecialchars($linkText) . '</a>';
        },
        $text
    );
    
    // Convert markdown images ![alt](url "title") to HTML
    $text = preg_replace_callback(
        '/!\[([^\]]+)\]\(([^)"]+)(?:\s+"([^"]+)")?\)/',
        function($matches) {
            $alt = $matches[1];
            $url = $matches[2];
            $title = isset($matches[3]) ? ' title="' . htmlspecialchars($matches[3]) . '"' : '';
            return '<img src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($alt) . '"' . $title . ' class="post-image">';
        },
        $text
    );
    
    // Convert bold **text** to HTML
    $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);
    // Convert italic _text_ to HTML
    $text = preg_replace('/_([^_]+)_/', '<em>$1</em>', $text);
    
    return nl2br($text);
}
?>
<?php 
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$post_id = sanitizeInput($_GET['id']);
$sql = "SELECT * FROM posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$post = $result->fetch_assoc();

// Get comments for this post
$comment_sql = "SELECT * FROM comments WHERE post_id = ? ORDER BY created_at DESC";
$comment_stmt = $conn->prepare($comment_sql);
$comment_stmt->bind_param("i", $post_id);
$comment_stmt->execute();
$comments = $comment_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> | Tosdev Blog</title>
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
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container main-content">
        <main>
            <article class="full-post">
                <?php if ($post['image_path']): ?>
                    <div class="post-image">
                        <img src="<?php echo $post['image_path']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    </div>
                <?php endif; ?>
                
                <div class="post-content">
                    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
                    <div class="post-meta">
                        <span class="category"><?php echo htmlspecialchars($post['category']); ?></span>
                        <span class="date"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                    </div>
                    <div class="post-text">
    <?php 
    // First sanitize the content, then parse markdown
    $clean_content = htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8');
    echo parseMarkdownLinks($clean_content);
    ?>
</div>
                </div>
            </article>

            <section class="comments-section">
                <h2>Comments</h2>
                
                <?php if ($comments->num_rows > 0): ?>
                    <div class="comments-list">
                        <?php while ($comment = $comments->fetch_assoc()): ?>
                            <div class="comment">
                                <div class="comment-author">
                                    <strong><?php echo htmlspecialchars($comment['author_name']); ?></strong>
                                    <span><?php echo date('M j, Y g:i a', strtotime($comment['created_at'])); ?></span>
                                </div>
                                <div class="comment-content">
                                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No comments yet. Be the first to comment!</p>
                <?php endif; ?>

                <div class="add-comment">
                    <h3>Add a Comment</h3>
                    <form action="add_comment.php" method="POST">
                        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                        <div class="form-group">
                            <input type="text" name="author_name" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" name="author_email" placeholder="Your Email" required>
                        </div>
                        <div class="form-group">
                            <textarea name="content" placeholder="Your Comment" rows="5" required></textarea>
                        </div>
                        <button type="submit">Submit Comment</button>
                    </form>
                </div>
            </section>
        </main>

        <aside class="sidebar">
            <div class="sidebar-widget">
                <h3>About the Author</h3>
                <p>Software engineer with experience in web development, PHP, and various tech projects. Sharing knowledge and insights from my journey.</p>
            </div>
            
            <div class="sidebar-widget">
                <h3>Categories</h3>
                <ul class="categories">
                    <li><a href="index.php?category=Web Development">Web Development</a></li>
                    <li><a href="index.php?category=PHP">PHP</a></li>
                    <li><a href="index.php?category=Projects">Projects</a></li>
                </ul>
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
