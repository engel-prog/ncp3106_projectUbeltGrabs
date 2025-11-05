<?php
include 'database/db.php'; // connect to database

// Fetch all products
$result = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu | Ubelt Grabs</title>
    <link rel="stylesheet" href="assets/css/menu.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar__container">
            <a href="index.php" id="navbar__logo">
                <img src="uploads/logoreal.png" alt="Ubelt Grabs" class="nav__logo-img">
                <span class="nav__logo-text">Ubelt Grabs</span>
            </a>
            <ul class="navbar__menu">
                <li class="navbar__item"><a href="index.php" class="navbar__links">Home</a></li>
                <li class="navbar__item"><a href="menu.php" class="navbar__links">Menu</a></li>
                <li class="navbar__item"><a href="about.php" class="navbar__links">About</a></li>
                <li class="navbar__btn"><a href="login.php" class="button">Log In</a></li>
            </ul>
        </div>
    </nav>

    <!-- Menu Section -->
    <section class="menu">
        <h1 class="section__title">Our Menu</h1>
        <div class="grid">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="card">
                        <img src="uploads/<?= $row['image'] ?>" alt="<?= $row['name'] ?>" class="card__img">
                        <div class="card__body">
                            <h3 class="card__title"><?= $row['name'] ?></h3>
                            <p class="card__desc"><?= $row['description'] ?></p>
                            <p class="card__price">₱<?= $row['price'] ?></p>
                            <button class="button add-to-cart">Add to Cart</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No products available yet.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>© <span id="year"></span> Ubelt Grabs — Local picks, fast.</p>
    </footer>

    <script>
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>
</html>
