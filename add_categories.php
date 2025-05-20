<?php
require_once "config/database.php";

$database = new Database();
$conn = $database->getConnection();

$categories = [
    [
        'name' => 'Technology',
        'slug' => 'technology',
        'description' => 'Posts about technology and innovations'
    ],
    [
        'name' => 'Lifestyle',
        'slug' => 'lifestyle',
        'description' => 'Posts about lifestyle and daily living'
    ],
    [
        'name' => 'Travel',
        'slug' => 'travel',
        'description' => 'Travel experiences and guides'
    ],
    [
        'name' => 'Food',
        'slug' => 'food',
        'description' => 'Food recipes and reviews'
    ],
    [
        'name' => 'Health',
        'slug' => 'health',
        'description' => 'Health and wellness tips'
    ]
];

try {
    $query = "INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);

    foreach ($categories as $category) {
        $stmt->execute([$category['name'], $category['slug'], $category['description']]);
    }
    echo "Categories added successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
} 