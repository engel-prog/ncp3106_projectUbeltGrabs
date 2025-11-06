<?php
include 'db.php';

$admin_logs_sql = "CREATE TABLE IF NOT EXISTS admin_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id INT UNSIGNED NOT NULL,
  action VARCHAR(50) NOT NULL,
  target_type VARCHAR(50) NOT NULL,
  target_id INT UNSIGNED NOT NULL,
  details TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (created_at),
  INDEX (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($admin_logs_sql) === TRUE) {
    echo "Admin logs table created successfully.";
} else {
    echo "Error creating admin logs table: " . $conn->error;
}

$conn->close();
?>
