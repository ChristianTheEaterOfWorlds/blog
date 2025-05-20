<?php
require_once "config/database.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Fetch user information
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user's posts
$query = "SELECT p.*, c.name as category_name 
          FROM posts p 
          JOIN categories c ON p.category_id = c.category_id 
          WHERE p.user_id = ? 
          ORDER BY p.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total posts and comments
$query = "SELECT COUNT(*) as comment_count FROM comments WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$comment_count = $stmt->fetch(PDO::FETCH_ASSOC)['comment_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Blog System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: #3498db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            margin: 0 auto 1rem;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 2rem;
            color: #3498db;
            font-weight: bold;
        }

        .stat-label {
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }

        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .post-card {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .post-card h3 {
            margin-bottom: 1rem;
        }

        .post-card .category {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .post-meta {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .edit-profile {
            margin-top: 1rem;
        }

        .edit-profile .btn {
            background: #3498db;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
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
                <li><a href="create-post.php">Create Post</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
                <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                <p class="email"><?php echo htmlspecialchars($user['email']); ?></p>
                <p class="joined">Joined <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
            </div>

            <div class="profile-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($posts); ?></div>
                    <div class="stat-label">Posts</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $comment_count; ?></div>
                    <div class="stat-label">Comments</div>
                </div>
            </div>

            <section class="user-posts">
                <h2>Your Posts</h2>
                <div class="posts-grid">
                    <?php if (empty($posts)): ?>
                        <p>You haven't created any posts yet. <a href="create-post.php">Create your first post!</a></p>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="post-card">
                                <span class="category"><?php echo htmlspecialchars($post['category_name']); ?></span>
                                <h3><a href="post.php?slug=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                                <div class="post-meta">
                                    <span class="date"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                                </div>
                                <p><?php echo substr(strip_tags($post['content']), 0, 150) . '...'; ?></p>
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