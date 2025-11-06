<?php
// database/add_product.php
include 'db.php';

// Validate user is logged in and is a seller
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../logins.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $name = trim($_POST['foodName'] ?? '');
    $price = floatval($_POST['foodPrice'] ?? 0);
    $description = trim($_POST['foodDesc'] ?? '');
    $seller_id = $_SESSION['user_id'];
    
    // Validation
    $errors = [];
    if (empty($name) || strlen($name) < 3) $errors[] = "Food name must be at least 3 characters.";
    if ($price <= 0) $errors[] = "Price must be greater than 0.";
    if (empty($description) || strlen($description) < 10) $errors[] = "Description must be at least 10 characters.";
    
    if (!empty($errors)) {
        $_SESSION['error'] = implode(" | ", $errors);
        header("Location: ../seller.php");
        exit;
    }
    
    // Handle image upload
    $image = '';
    if(isset($_FILES['foodImage']) && $_FILES['foodImage']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foodImage']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $_SESSION['error'] = "Only image files are allowed.";
            header("Location: ../seller.php");
            exit;
        }
        
        $image = time() . '_' . basename($filename);
        $target = '../uploads/' . $image;
        
        if (!move_uploaded_file($_FILES['foodImage']['tmp_name'], $target)) {
            $_SESSION['error'] = "Failed to upload image.";
            header("Location: ../seller.php");
            exit;
        }
    }
    
    // Insert product with prepared statement
    $stmt = $conn->prepare("INSERT INTO products (seller_id, name, description, price, image, status) 
                           VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("issds", $seller_id, $name, $description, $price, $image);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Product submitted for approval! Admins will review it soon.";
        header("Location: ../seller.php");
    } else {
        $_SESSION['error'] = "Error adding product: " . $conn->error;
        header("Location: ../seller.php");
    }
    
    $stmt->close();
    $conn->close();
}
?>
