<?php

// Prevent direct access to PHP files
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    http_response_code(403);
    exit('Access denied');
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Input sanitization helper
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Validate email
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate price
function is_valid_price($price) {
    return is_numeric($price) && $price > 0 && $price <= 99999.99;
}

// Validate image upload
function validate_image_upload($file) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== 0) {
        return ['valid' => false, 'error' => 'Upload failed'];
    }
    
    if ($file['size'] > $max_size) {
        return ['valid' => false, 'error' => 'File too large (max 5MB)'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return ['valid' => false, 'error' => 'Invalid file type'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime, $allowed_mimes)) {
        return ['valid' => false, 'error' => 'Invalid image file'];
    }
    
    return ['valid' => true];
}

// Validate product data
function validate_product_data($name, $price, $description) {
    $errors = [];
    
    if (empty($name) || strlen($name) < 3 || strlen($name) > 255) {
        $errors[] = 'Product name must be 3-255 characters';
    }
    
    if (!is_valid_price($price)) {
        $errors[] = 'Invalid price';
    }
    
    if (empty($description) || strlen($description) < 10 || strlen($description) > 1000) {
        $errors[] = 'Description must be 10-1000 characters';
    }
    
    return $errors;
}

// Log admin actions
function log_admin_action($conn, $admin_id, $action, $target_type, $target_id, $details = '') {
    $stmt = $conn->prepare("
        INSERT INTO admin_logs (admin_id, action, target_type, target_id, details, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("issis", $admin_id, $action, $target_type, $target_id, $details);
    return $stmt->execute();
}

// Check admin access
function require_admin() {
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit(json_encode(['error' => 'Admin access required']));
    }
}

// Check seller access
function require_seller() {
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
        http_response_code(403);
        exit(json_encode(['error' => 'Seller access required']));
    }
}

// Check login
function require_login() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        exit(json_encode(['error' => 'Login required']));
    }
}
?>
