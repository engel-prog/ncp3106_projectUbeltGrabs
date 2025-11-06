<?php
// database/seller_products.php
include 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$seller_id = $_SESSION['user_id'];
$status = $_GET['status'] ?? 'all'; // all, pending, approved, rejected

$query = "SELECT id, name, price, image, status, created_at FROM products WHERE seller_id = ?";
$params = [$seller_id];
$types = "i";

if ($status !== 'all') {
    $query .= " AND status = ?";
    array_push($params, $status);
    array_push($types, "s");
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($products);
?>
