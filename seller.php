<?php
<<<<<<< HEAD
include 'database/db.php';

session_start();

// Show success/error messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Fetch seller's products
if (isset($_SESSION['user_id'])) {
    $seller_id = $_SESSION['user_id'];
    $result = $conn->prepare("SELECT id, name, price, image, status, created_at FROM products WHERE seller_id = ? ORDER BY created_at DESC");
    $result->bind_param("i", $seller_id);
    $result->execute();
    $products = $result->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard | Ubelt Grabs</title>
    <link rel="stylesheet" href="assets/css/seller.css">
    <style>
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 8px;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: 500;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar__container">
            <a href="index.html" id="navbar__logo">
                <img src="uploads/uglogo.png" alt="Ubelt Grabs" class="nav__logo-img"/>
                <span class="nav__logo-text">Ubelt Grabs</span>
            </a>
            <ul class="navbar__menu">
                <li class="navbar__item"><a href="index.html" class="navbar__links">Home</a></li>
                <li class="navbar__item"><a href="menu.php" class="navbar__links">Menu</a></li>
                <li class="navbar__item"><a href="about.html" class="navbar__links">About</a></li>
                <li class="navbar__btn"><a href="logout.php" class="button">Log Out</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero seller-hero">
        <div class="hero__container">
            <h1 class="hero__title">Welcome, Seller!</h1>
            <p class="hero__subtitle">Upload your best local picks and start earning today.</p>
        </div>
    </header>

    <main class="container" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
        <!-- Messages -->
        <?php if ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Upload Form -->
        <section class="upload" style="background: #f9f9f9; padding: 30px; border-radius: 8px; margin-bottom: 40px;">
            <h2 class="section__title">Add New Food Item</h2>
            <form class="upload__form" method="POST" action="database/add_product.php" enctype="multipart/form-data">
                <div class="form__group" style="margin-bottom: 20px;">
                    <label for="foodImage" style="display: block; margin-bottom: 8px; font-weight: 500;">Food Image:</label>
                    <input type="file" id="foodImage" name="foodImage" accept="image/*" required>
                    <img id="imagePreview" class="image-preview" alt="Image preview" style="display:none; max-width: 300px; margin-top: 10px; border-radius: 4px;">
                </div>

                <div class="form__group" style="margin-bottom: 20px;">
                    <label for="foodName" style="display: block; margin-bottom: 8px; font-weight: 500;">Food Name:</label>
                    <input type="text" id="foodName" name="foodName" placeholder="e.g. Chicken Adobo" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div class="form__group" style="margin-bottom: 20px;">
                    <label for="foodPrice" style="display: block; margin-bottom: 8px; font-weight: 500;">Price (₱):</label>
                    <input type="number" id="foodPrice" name="foodPrice" placeholder="e.g. 120" step="0.01" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div class="form__group" style="margin-bottom: 20px;">
                    <label for="foodDesc" style="display: block; margin-bottom: 8px; font-weight: 500;">Description (min 10 characters):</label>
                    <textarea id="foodDesc" name="foodDesc" rows="4" placeholder="Describe your food item..." required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit;"></textarea>
                </div>

                <button type="submit" class="button upload__btn" style="padding: 12px 30px; font-size: 16px; cursor: pointer;">Upload Item</button>
            </form>
        </section>

        <!-- Your Products -->
        <section class="features">
            <h2 class="section__title">Your Uploaded Items</h2>
            <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
                <?php if ($products && $products->num_rows > 0): ?>
                    <?php while ($row = $products->fetch_assoc()): ?>
                        <div class="card" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="card__img" style="width: 100%; height: 200px; object-fit: cover;">
                            <div class="card__body" style="padding: 15px;">
                                <h3 class="card__title" style="margin: 0 0 8px 0; font-size: 18px;"><?= htmlspecialchars($row['name']) ?></h3>
                                <p class="card__desc" style="margin: 0 0 8px 0; color: #666;">₱<?= number_format($row['price'], 2) ?></p>
                                <p style="font-size: 12px; color: #999; margin-bottom: 10px;"><?= date('M d, Y', strtotime($row['created_at'])) ?></p>
                                
                                <span class="status-badge status-<?= $row['status'] ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="grid-column: 1/-1; text-align: center; color: #999;">No items uploaded yet.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer__inner">
            <p>© <span id="year"></span> Ubelt Grabs — Empowering Local Sellers.</p>
        </div>
    </footer>

    <script>
        document.getElementById('year').textContent = new Date().getFullYear();

        // Image Preview
        const foodImageInput = document.getElementById('foodImage');
        const imagePreview = document.getElementById('imagePreview');
        foodImageInput.addEventListener('change', () => {
            const file = foodImageInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
=======
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
>>>>>>> 0211375e267cbd17ef7ba261d82ac4e91b7f28f7
