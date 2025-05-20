<?php
require_once "config/database.php";
session_start();

$database = new Database();
$conn = $database->getConnection();

// Fetch categories with post counts
$query = "SELECT c.*, COUNT(p.post_id) as post_count 
          FROM categories c 
          LEFT JOIN posts p ON c.category_id = p.category_id 
          GROUP BY c.category_id 
          ORDER BY c.name";
$stmt = $conn->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Blog System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
        }
        
        .category-card {
            background: #fff;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
        }
        
        .category-name {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        
        .category-description {
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        
        .post-count {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .category-link {
            text-decoration: none;
            color: inherit;
        }
        
        .category-link:hover {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">BLOGSYSTEM</div>
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
        <section class="hero">
            <h1>Blog Categories</h1>
            <p>Explore posts by category</p>
        </section>

        <div class="container">
            <div class="categories-grid">
                <?php foreach($categories as $category): ?>
                    <div class="category-link">
                        <a href="index.php?category=<?php echo urlencode($category['slug']); ?>">
                            <div class="category-card">
                                <div class="category-content">
                                    <h2 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h2>
                                    <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                                </div>
                                <div class="post-count"><?php echo $category['post_count']; ?> Posts</div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> BlogSystem. All rights reserved.</p>
    </footer>

    <script src="js/main.js"></script>
</body>
</html> 