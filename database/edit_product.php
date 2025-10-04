<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['id']);
    $name = $_POST['pName'];
    $price = $_POST['pPrice'];
    $category = $_POST['pCategory'];
    $university = $_POST['pUniversity'];
    $fb = $_POST['pFB'] ?? null;
    $image = $_POST['pImage'] ?? null;
    $desc = $_POST['pDesc'] ?? null;

    $stmt = $conn->prepare("UPDATE products 
        SET name=?, price=?, category=?, university_id=?, fb_page=?, image_url=?, description=? 
        WHERE id=?");
    $stmt->bind_param("sdissssi", $name, $price, $category, $university, $fb, $image, $desc, $id);

    if ($stmt->execute()) {
        header("Location: ../index.php?seller=1&success=product_updated");
    } else {
        header("Location: ../index.php?seller=1&error=update_failed");
    }
    exit;
}
