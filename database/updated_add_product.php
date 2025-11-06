<?php
include 'db.php';
include 'config_validation.php';

require_seller();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    exit;
}

// Get and validate inputs
$name = sanitize_input($_POST['foodName'] ?? '');
$price = floatval($_POST['foodPrice'] ?? 0);
$description = sanitize_input($_POST['foodDesc'] ?? '');
$seller_id = $_SESSION['user_id'];

// Validate product data
$validation_errors = validate_product_data($name, $price, $description);
if (!empty($validation_errors)) {
    $_SESSION['error'] = implode(" | ", $validation_errors);
    header("Location: ../seller.php");
    exit;
}

// Validate image
if (!isset($_FILES['foodImage'])) {
    $_SESSION['error'] = 'Image is required';
    header("Location: ../seller.php");
    exit;
}

$image_validation = validate_image_upload($_FILES['foodImage']);
if (!$image_validation['valid']) {
    $_SESSION['error'] = $image_validation['error'];
    header("Location: ../seller.php");
    exit;
}

// Save image with unique name
$ext = strtolower(pathinfo($_FILES['foodImage']['name'], PATHINFO_EXTENSION));
$image_name = uniqid('product_', true) . '.' . $ext;
$target_path = '../uploads/' . $image_name;

if (!move_uploaded_file($_FILES['foodImage']['tmp_name'], $target_path)) {
    $_SESSION['error'] = 'Failed to upload image';
    header("Location: ../seller.php");
    exit;
}

// Insert into database
$stmt = $conn->prepare("
    INSERT INTO products (seller_id, name, description, price, image, status, created_at)
    VALUES (?, ?, ?, ?, ?, 'pending', NOW())
");

if (!$stmt) {
    $_SESSION['error'] = 'Database error';
    header("Location: ../seller.php");
    exit;
}

$stmt->bind_param("issds", $seller_id, $name, $description, $price, $image_name);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Product submitted successfully! Awaiting admin approval.';
} else {
    $_SESSION['error'] = 'Error adding product';
    unlink($target_path); // Remove uploaded image on error
}

$stmt->close();
$conn->close();

header("Location: ../seller.php");
?>
