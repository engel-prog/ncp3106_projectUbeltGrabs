<?php
include "db.php";

// Only run when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['pName'];
    $price = $_POST['pPrice'];
    $category = $_POST['pCategory'];
    $university = $_POST['pUniversity'];
    $fb = $_POST['pFB'] ?? null;
    $image = $_POST['pImage'] ?? null;
    $desc = $_POST['pDesc'] ?? null;

    $sql = "INSERT INTO products (name, price, category, university_id, fb_page, image_url, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdsisss", $name, $price, $category, $university, $fb, $image, $desc);

    if ($stmt->execute()) {
        header("Location: index.php?seller=1&success=1"); // redirect back with success
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
