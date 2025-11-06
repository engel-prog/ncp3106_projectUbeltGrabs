<?php
// database/admin_approve_user.php
include 'db.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = intval($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? ''; // approve or reject
    
    if (!$user_id || !in_array($action, ['approve', 'reject'])) {
        http_response_code(400);
        exit(json_encode(['error' => 'Invalid request']));
    }
    
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND role IN ('seller', 'customer')");
    $stmt->bind_param("si", $status, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => 'User ' . $action . 'ed successfully']);
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
