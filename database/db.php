<?php
// db.php
$host = "127.0.0.1:3307";     // XAMPP default
$user = "root";               // default MySQL user
$pass = "";                   // default password is empty in XAMPP
$dbname = "ubelt_grabs";      // database name

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
