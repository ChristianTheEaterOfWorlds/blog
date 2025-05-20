# Blog System

A modern blog system built with PHP, MySQL, HTML, CSS, and JavaScript. Features a clean, responsive design and robust functionality for creating and managing blog posts.

## Features

- User authentication (login/register)
- Create, read, and comment on blog posts
- Categories and tags support
- Responsive design
- Form validation
- Clean URLs with slugs
- Security features (password hashing, SQL injection prevention)
- Mobile-friendly navigation

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

## Installation

1. Clone the repository to your web server directory:
```bash
git clone https://github.com/yourusername/blog-system.git
```

2. Create a MySQL database and import the schema:
```bash
mysql -u your_username -p your_database_name < database.sql
```

3. Configure the database connection:
   - Open `config/database.php`
   - Update the database credentials:
     ```php
     private $host = "localhost";
     private $db_name = "blog_system";
     private $username = "your_username";
     private $password = "your_password";
     ```

4. Set up the web server:
   - For Apache, ensure mod_rewrite is enabled
   - Point the document root to the project directory
   - Ensure the web server has write permissions for uploads (if implemented)

5. Create the first admin user:
   - Register a new user through the registration form
   - Manually update the `is_admin` field in the database:
     ```sql
     UPDATE users SET is_admin = 1 WHERE user_id = 1;
     ```

## Directory Structure

```
blog-system/
├── config/
│   └── database.php
├── css/
│   └── style.css
├── js/
│   └── main.js
├── index.php
├── login.php
├── register.php
├── logout.php
├── create-post.php
├── post.php
├── database.sql
└── README.md
```

## Security Features

- Password hashing using PHP's password_hash()
- Prepared statements for all database queries
- Input sanitization
- CSRF protection (to be implemented)
- XSS prevention through htmlspecialchars()

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Built with modern PHP practices
- Uses PDO for database connections
- Implements responsive design principles
- Follows security best practices 
