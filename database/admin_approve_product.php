<?php
// database/admin_approve_product.php
include 'db.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = intval($_POST['product_id'] ?? 0);
    $action = $_POST['action'] ?? ''; // approve or reject
    $notes = trim($_POST['notes'] ?? '');
    
    if (!$product_id || !in_array($action, ['approve', 'reject'])) {
        http_response_code(400);
        exit(json_encode(['error' => 'Invalid request']));
    }
    
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    
    $stmt = $conn->prepare("UPDATE products SET status = ?, approval_notes = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $notes, $product_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => 'Product ' . $action . 'ed successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => $conn->error]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}
?>
