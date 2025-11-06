<?php
include 'database/db.php';

session_start();

// Fetch only approved products from sellers
$approved_products = $conn->prepare("
    SELECT p.id, p.name, p.description, p.price, p.image, u.username, u.id as seller_id
    FROM products p
    JOIN users u ON p.seller_id = u.id
    WHERE p.status = 'approved'
    ORDER BY p.created_at DESC
");
$approved_products->execute();
$products_result = $approved_products->get_result();

// Fetch recommended products (mark top products as recommended)
$recommended = $conn->prepare("
    SELECT p.id, p.name, p.description, p.price, p.image, u.username, r.reason
    FROM products p
    JOIN users u ON p.seller_id = u.id
    LEFT JOIN recommendations r ON p.id = r.product_id
    WHERE p.status = 'approved' AND r.id IS NOT NULL
    ORDER BY r.priority DESC
    LIMIT 5
");
$recommended->execute();
$recommended_result = $recommended->get_result();

// Get cart count if user is logged in
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $cart_check = $conn->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
    $cart_check->bind_param("i", $_SESSION['user_id']);
    $cart_check->execute();
    $cart_count = $cart_check->get_result()->fetch_row()[0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu | Ubelt Grabs</title>
    <link rel="stylesheet" href="assets/css/menu.css">
    <style>
        .recommended-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .card {
            position: relative;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }
        
        .card__img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .card__body {
            padding: 15px;
        }
        
        .card__title {
            margin: 0 0 8px 0;
            font-size: 18px;
            color: #333;
        }
        
        .seller-info {
            font-size: 12px;
            color: #667eea;
            margin-bottom: 8px;
        }
        
        .card__desc {
            margin: 8px 0;
            color: #666;
            font-size: 14px;
        }
        
        .card__price {
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }
        
        .card__btn {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: opacity 0.3s;
        }
        
        .card__btn:hover {
            opacity: 0.9;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .section__title {
            font-size: 28px;
            margin-bottom: 20px;
            color: #333;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            transition: opacity 0.3s;
        }
        
        .navbar a:hover {
            opacity: 0.8;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .no-items {
            text-align: center;
            color: #999;
            padding: 40px 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        
        .footer {
            background: #333;
            color: white;
            padding: 30px;
            text-align: center;
            margin-top: 60px;
        }
        
        .footer a {
            color: #667eea;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer a:hover {
            color: #764ba2;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <h1>Ubelt Grabs</h1>
        <div>
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="seller.php">My Products</a>
                <a href="cart.php">Cart (<?= $cart_count ?>)</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="logins.php">Login</a>
                <a href="signups.php">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <!-- Recommended Products Section -->
        <?php if ($recommended_result->num_rows > 0): ?>
            <section>
                <h2 class="section__title">Recommended for You</h2>
                <div class="grid">
                    <?php while ($product = $recommended_result->fetch_assoc()): ?>
                        <div class="card">
                            <div class="recommended-badge">RECOMMENDED</div>
                            <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="card__img">
                            <div class="card__body">
                                <h3 class="card__title"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="seller-info">By <?= htmlspecialchars($product['username']) ?></p>
                                <p class="card__desc"><?= htmlspecialchars(substr($product['description'], 0, 80)) ?>...</p>
                                <p class="card__price">₱<?= number_format($product['price'], 2) ?></p>
                                <p style="font-size: 12px; color: #999; margin-bottom: 10px;">
                                    Why Recommended: <?= htmlspecialchars($product['reason'] ?? 'Popular choice') ?>
                                </p>
                                <button class="card__btn" onclick="addToCart(<?= $product['id'] ?>)">Add to Cart</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- All Products Section -->
        <section>
            <h2 class="section__title">All Available Food</h2>
            
            <?php if ($products_result->num_rows > 0): ?>
                <div class="grid">
                    <?php while ($product = $products_result->fetch_assoc()): ?>
                        <div class="card">
                            <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="card__img">
                            <div class="card__body">
                                <h3 class="card__title"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="seller-info">By <?= htmlspecialchars($product['username']) ?></p>
                                <p class="card__desc"><?= htmlspecialchars(substr($product['description'], 0, 80)) ?>...</p>
                                <p class="card__price">₱<?= number_format($product['price'], 2) ?></p>
                                <button class="card__btn" onclick="addToCart(<?= $product['id'] ?>)">Add to Cart</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-items">
                    <p>No products available yet. Check back soon!</p>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>© 2025 Ubelt Grabs. All rights reserved.</p>
        <a href="https://facebook.com">Facebook</a> | 
        <a href="https://twitter.com">Twitter</a>
    </footer>

    <script>
        function addToCart(productId) {
            <?php if (!isset($_SESSION['user_id'])): ?>
                alert('Please login to add items to cart');
                window.location.href = 'logins.php';
            <?php else: ?>
                fetch('database/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'product_id=' + productId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Added to cart!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
            <?php endif; ?>
        }
    </script>
</body>
</html>
