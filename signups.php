<?php
include 'database/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tel = trim($_POST['tel'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic validation
    if ($name === '' || $email === '' || $password === '') {
        echo "Name, email and password are required.";
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$stmt) {
        echo "Database error: " . $conn->error;
        $conn->close();
        exit();
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        echo "Email already taken. Please choose another one.";
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();

    // Hash the password before storing it
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user using prepared statement
    $stmt = $conn->prepare("INSERT INTO users (name, email, tel, password) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        echo "Database error: " . $conn->error;
        $conn->close();
        exit();
    }
    $stmt->bind_param("ssss", $name, $email, $tel, $hashedPassword);

    if ($stmt->execute()) {
        echo "Signup successful! Welcome, " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>