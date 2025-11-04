<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['foodName'];
    $price = $_POST['foodPrice'];
    $description = $_POST['foodDesc'];

    // Handle image upload
    $image = "";
    if (isset($_FILES['foodImage']) && $_FILES['foodImage']['error'] == 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true); // create uploads folder if it doesn't exist
        }

        $image = basename($_FILES["foodImage"]["name"]);
        $targetFile = $targetDir . $image;

        if (!move_uploaded_file($_FILES["foodImage"]["tmp_name"], $targetFile)) {
            echo "Error uploading image.";
            exit;
        }
    }

    $sql = "INSERT INTO products (name, description, price, image)
            VALUES ('$name', '$description', '$price', '$image')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Product added successfully!'); window.location.href='seller.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>
