<?php
require_once "config/database.php";
session_start();

$database = new Database();
$conn = $database->getConnection();

$error = '';
$success = '';

// Get post data
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    header("Location: index.php");
    exit();
}

// Fetch post with author, category, and tags
$query = "SELECT p.*, u.username, c.name as category_name, c.slug as category_slug 
          FROM posts p 
          JOIN users u ON p.user_id = u.user_id 
          JOIN categories c ON p.category_id = c.category_id 
          WHERE p.slug = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$slug]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header("Location: index.php");
    exit();
}

// Fetch tags for this post
$query = "SELECT t.name, t.slug 
          FROM tags t 
          JOIN post_tags pt ON t.tag_id = pt.tag_id 
          WHERE pt.post_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$post['post_id']]);
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $comment_content = trim($_POST['comment']);
    
    if (strlen($comment_content) < 5) {
        $error = "Comment must be at least 5 characters long";
    } else {
        $query = "INSERT INTO comments (content, user_id, post_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        if ($stmt->execute([$comment_content, $_SESSION['user_id'], $post['post_id']])) {
            $success = "Comment added successfully!";
            // Refresh the page to show the new comment
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        } else {
            $error = "An error occurred while adding the comment";
        }
    }
}

// Fetch comments with user information
$query = "SELECT c.*, u.username 
          FROM comments c 
          JOIN users u ON c.user_id = u.user_id 
          WHERE c.post_id = ? 
          ORDER BY c.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$post['post_id']]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Blog System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .post-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .post-header {
            text-align: center;
            margin-bottom: 3rem;
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        .post-title {
            font-size: 2.8rem;
            color: var(--heading-color);
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .post-meta {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .post-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .post-category {
            background: var(--tag-bg);
            color: var(--tag-color);
            padding: 0.5rem 1.2rem;
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .post-category:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
            text-decoration: none;
        }

        .post-content {
            font-size: 1.2rem;
            line-height: 1.8;
            color: var(--text-primary);
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: 12px;
            margin: 2rem 0;
            border: 1px solid var(--border-color);
        }

        .post-content p {
            margin-bottom: 1.5rem;
        }

        .tags {
            margin: 2rem 0;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .tag {
            background: var(--bg-secondary);
            color: var(--text-secondary);
            padding: 0.4rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }

        .tag:hover {
            background: var(--accent-color);
            color: var(--tag-color);
            transform: translateY(-2px);
            text-decoration: none;
            border-color: var(--accent-color);
        }

        .comments-section {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: 12px;
            margin-top: 3rem;
            border: 1px solid var(--border-color);
        }

        .comments-section h2 {
            color: var(--heading-color);
            margin-bottom: 2rem;
            font-size: 1.8rem;
        }

        .comment {
            background-color: var(--bg-secondary);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
        }

        .comment-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            color: var(--text-secondary);
        }

        .comment-content {
            font-size: 1.1rem;
            line-height: 1.6;
            color: var(--text-primary);
        }

        .comment-form {
            background: var(--bg-secondary);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px var(--shadow-color);
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }

        .comment-form textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-size: 1.1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .comment-form textarea:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.2);
        }

        .login-prompt {
            text-align: center;
            padding: 2rem;
            background: var(--bg-secondary);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .login-prompt a {
            color: var(--accent-color);
            font-weight: 500;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">BlogSystem</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="categories.php">Categories</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="create-post.php">Create Post</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <div class="post-container">
            <article>
                <header class="post-header">
                    <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                    <div class="post-meta">
                        <span class="author">By <?php echo htmlspecialchars($post['username']); ?></span>
                        <span class="date"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                        <span>
                            <a href="index.php?category=<?php echo urlencode($post['category_slug']); ?>" class="post-category">
                                <?php echo htmlspecialchars($post['category_name']); ?>
                            </a>
                        </span>
                    </div>
                    <?php if (!empty($tags)): ?>
                        <div class="tags">
                            <?php foreach ($tags as $tag): ?>
                                <a href="tag.php?slug=<?php echo $tag['slug']; ?>" class="tag">
                                    #<?php echo htmlspecialchars($tag['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </header>

                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>
            </article>

            <section class="comments-section">
                <h2>Comments</h2>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($error): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    <form method="POST" class="comment-form">
                        <div class="form-group">
                            <label for="comment">Add a Comment</label>
                            <textarea id="comment" name="comment" required placeholder="Share your thoughts..." rows="4"></textarea>
                        </div>
                        <button type="submit" class="btn">Submit Comment</button>
                    </form>
                <?php else: ?>
                    <p class="login-prompt">Please <a href="login.php">login</a> to leave a comment.</p>
                <?php endif; ?>

                <div class="comments-list">
                    <?php if (empty($comments)): ?>
                        <p>No comments yet. Be the first to comment!</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <div class="comment-meta">
                                    <span class="comment-author"><?php echo htmlspecialchars($comment['username']); ?></span>
                                    <span class="comment-date"><?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?></span>
                                </div>
                                <div class="comment-content">
                                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> BlogSystem. All rights reserved.</p>
    </footer>

    <script src="js/main.js"></script>
</body>
</html> 