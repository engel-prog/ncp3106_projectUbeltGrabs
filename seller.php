<?php
include 'database/db.php'; // database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['foodName']);
    $price = floatval($_POST['foodPrice']);
    $desc = mysqli_real_escape_string($conn, $_POST['foodDesc']);

    // Handle image upload
    $image = '';
    if(isset($_FILES['foodImage']) && $_FILES['foodImage']['name'] != '') {
        $image = time() . '_' . basename($_FILES['foodImage']['name']);
        move_uploaded_file($_FILES['foodImage']['tmp_name'], 'uploads/' . $image);
    }

    // Insert into database
    $sql = "INSERT INTO products (name, description, price, image) 
            VALUES ('$name', '$desc', '$price', '$image')";

    if(mysqli_query($conn, $sql)) {
        // Redirect back to seller dashboard
        header("Location: seller.php?success=1");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
