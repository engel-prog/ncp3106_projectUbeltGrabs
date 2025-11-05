<?php
include 'database/db.php';

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
$result = mysqli_query($conn, $sql);

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$row = $result->fetch_assoc();
if (password_verify($password, $row['password'])) {
    echo "Login successful!";
} else {
    echo "Invalid password.";
}

if (mysqli_num_rows($result) > 0) {
    // Successful login
    echo "Login successful! Welcome, " . $name;
} else {
    // Invalid credentials
    echo "Invalid email or password.";
}

$conn->close();
?>