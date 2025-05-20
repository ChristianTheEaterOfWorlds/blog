<?php
require_once "config/database.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Fetch categories for the dropdown
$query = "SELECT category_id, name FROM categories ORDER BY name";
$stmt = $conn->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category_id = $_POST['category_id'];
    $tags = isset($_POST['tags']) ? trim($_POST['tags']) : '';

    // Create URL-friendly slug from title
    $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $title));
    
    // Validate input
    if (strlen($title) < 5) {
        $error = "Title must be at least 5 characters long";
    } elseif (strlen($content) < 50) {
        $error = "Content must be at least 50 characters long";
    } else {
        try {
            $conn->beginTransaction();

            // Insert post
            $query = "INSERT INTO posts (title, slug, content, user_id, category_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$title, $slug, $content, $_SESSION['user_id'], $category_id]);
            $post_id = $conn->lastInsertId();

            // Handle tags
            if (!empty($tags)) {
                $tag_array = array_map('trim', explode(',', $tags));
                foreach ($tag_array as $tag_name) {
                    // Create or get tag
                    $tag_slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $tag_name));
                    
                    $query = "INSERT INTO tags (name, slug) VALUES (?, ?) ON DUPLICATE KEY UPDATE tag_id=LAST_INSERT_ID(tag_id)";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([$tag_name, $tag_slug]);
                    $tag_id = $conn->lastInsertId();

                    // Link tag to post
                    $query = "INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([$post_id, $tag_id]);
                }
            }

            $conn->commit();
            $success = "Post created successfully!";
            header("Location: post.php?slug=" . $slug);
            exit();
        } catch (Exception $e) {
            $conn->rollBack();
            $error = "An error occurred while creating the post. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post - Blog System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">BlogSystem</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="form-container">
            <h2>Create New Post</h2>
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form method="POST" action="create-post.php">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required placeholder="Enter post title" maxlength="200">
                </div>
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" required placeholder="Write your post content here..." rows="10" maxlength="50000"></textarea>
                </div>
                <div class="form-group">
                    <label for="tags">Tags (comma-separated)</label>
                    <input type="text" id="tags" name="tags" placeholder="Enter tags, separated by commas">
                </div>
                <button type="submit" class="btn">Create Post</button>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> BlogSystem. All rights reserved.</p>
    </footer>

    <script src="js/main.js"></script>
</body>
</html> 