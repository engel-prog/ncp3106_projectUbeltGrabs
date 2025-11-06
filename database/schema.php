<?php
// database/schema.php
// Run this file ONCE to create all tables
include 'db.php';

// Create users table
$users_sql = "CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('seller', 'customer', 'admin') DEFAULT 'customer',
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Create products table with approval status
$products_sql = "CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seller_id INT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  image VARCHAR(255),
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  approval_notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (status),
  INDEX (seller_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Create recommendations table
$recommendations_sql = "CREATE TABLE IF NOT EXISTS recommendations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  reason VARCHAR(255),
  priority INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Create cart table
$cart_sql = "CREATE TABLE IF NOT EXISTS cart (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Execute all queries
if ($conn->query($users_sql) === TRUE) {
    echo "Users table created successfully.<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

if ($conn->query($products_sql) === TRUE) {
    echo "Products table created successfully.<br>";
} else {
    echo "Error creating products table: " . $conn->error . "<br>";
}

if ($conn->query($recommendations_sql) === TRUE) {
    echo "Recommendations table created successfully.<br>";
} else {
    echo "Error creating recommendations table: " . $conn->error . "<br>";
}

if ($conn->query($cart_sql) === TRUE) {
    echo "Cart table created successfully.<br>";
} else {
    echo "Error creating cart table: " . $conn->error . "<br>";
}

$conn->close();
echo "Database setup complete!";
?>
