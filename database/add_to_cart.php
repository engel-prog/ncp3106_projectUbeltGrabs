<?php
include 'db.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id'] ?? 0);

if (!$product_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid product']);
    exit;
}

// Verify product exists and is approved
$verify = $conn->prepare("SELECT id FROM products WHERE id = ? AND status = 'approved'");
$verify->bind_param("i", $product_id);
$verify->execute();
if (!$verify->get_result()->fetch_assoc()) {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found']);
    exit;
}

// Check if already in cart
$check = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
$check->bind_param("ii", $user_id, $product_id);
$check->execute();
$existing = $check->get_result()->fetch_assoc();

if ($existing) {
    // Update quantity
    $new_qty = $existing['quantity'] + 1;
    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $update->bind_param("ii", $new_qty, $existing['id']);
    $update->execute();
} else {
    // Add to cart
    $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
    $insert->bind_param("ii", $user_id, $product_id);
    $insert->execute();
}

echo json_encode(['success' => true]);
$conn->close();
?>
