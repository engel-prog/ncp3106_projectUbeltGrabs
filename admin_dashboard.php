<?php
include 'database/db.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: logins.php");
    exit;
}

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Get tab from URL
$tab = $_GET['tab'] ?? 'products';

// Fetch pending products
$pending_products = $conn->prepare("
    SELECT p.id, p.name, p.price, p.image, p.description, p.created_at, u.username 
    FROM products p 
    JOIN users u ON p.seller_id = u.id 
    WHERE p.status = 'pending' 
    ORDER BY p.created_at ASC
");
$pending_products->execute();
$products_result = $pending_products->get_result();

// Fetch pending users
$pending_users = $conn->prepare("
    SELECT id, username, email, role, created_at 
    FROM users 
    WHERE status = 'pending' 
    ORDER BY created_at ASC
");
$pending_users->execute();
$users_result = $pending_users->get_result();

// Fetch stats
$stats = [
    'pending_products' => $conn->query("SELECT COUNT(*) FROM products WHERE status = 'pending'")->fetch_row()[0],
    'approved_products' => $conn->query("SELECT COUNT(*) FROM products WHERE status = 'approved'")->fetch_row()[0],
    'rejected_products' => $conn->query("SELECT COUNT(*) FROM products WHERE status = 'rejected'")->fetch_row()[0],
    'pending_users' => $conn->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetch_row()[0],
    'approved_users' => $conn->query("SELECT COUNT(*) FROM users WHERE status = 'approved'")->fetch_row()[0],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Ubelt Grabs</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
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
        
        .navbar h1 {
            font-size: 24px;
        }
        
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 4px;
            transition: background 0.3s;
        }
        
        .navbar a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            color: #667eea;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #333;
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
        }
        
        .tab-btn {
            padding: 12px 20px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 16px;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab-btn.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .message {
            padding: 15px;
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
        
        .approval-card {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: 150px 1fr auto;
            gap: 20px;
            align-items: start;
        }
        
        .product-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .approval-info h3 {
            margin-bottom: 8px;
            color: #333;
        }
        
        .approval-info p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        
        .seller-name {
            color: #667eea;
            font-weight: bold;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-direction: column;
        }
        
        button {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-approve {
            background-color: #28a745;
            color: white;
        }
        
        .btn-approve:hover {
            background-color: #218838;
        }
        
        .btn-reject {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-reject:hover {
            background-color: #c82333;
        }
        
        textarea {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            resize: vertical;
        }
        
        .form-group {
            margin-top: 10px;
        }
        
        @media (max-width: 768px) {
            .approval-card {
                grid-template-columns: 1fr;
            }
            
            .product-image {
                width: 100%;
                height: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <h1>Ubelt Grabs Admin</h1>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <!-- Messages -->
        <?php if ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <h3>Pending Products</h3>
                <div class="number"><?= $stats['pending_products'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Approved Products</h3>
                <div class="number"><?= $stats['approved_products'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Rejected Products</h3>
                <div class="number"><?= $stats['rejected_products'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Users</h3>
                <div class="number"><?= $stats['pending_users'] ?></div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn <?= $tab === 'products' ? 'active' : '' ?>" onclick="switchTab('products')">
                Approve Products (<?= $stats['pending_products'] ?>)
            </button>
            <button class="tab-btn <?= $tab === 'users' ? 'active' : '' ?>" onclick="switchTab('users')">
                Approve Users (<?= $stats['pending_users'] ?>)
            </button>
        </div>

        <!-- Products Tab -->
        <div id="products" class="tab-content <?= $tab === 'products' ? 'active' : '' ?>">
            <h2 style="margin-bottom: 20px;">Pending Product Approvals</h2>
            
            <?php if ($products_result->num_rows > 0): ?>
                <?php while ($product = $products_result->fetch_assoc()): ?>
                    <div class="approval-card">
                        <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                        
                        <div class="approval-info">
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p><strong>Price:</strong> ₱<?= number_format($product['price'], 2) ?></p>
                            <p><strong>Description:</strong> <?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                            <p><strong>Seller:</strong> <span class="seller-name"><?= htmlspecialchars($product['username']) ?></span></p>
                            <p style="font-size: 12px; color: #999;"><?= date('M d, Y H:i', strtotime($product['created_at'])) ?></p>
                        </div>
                        
                        <div class="action-buttons">
                            <form method="POST" action="database/admin_approve_product.php" style="display: contents;">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                
                                <div class="form-group">
                                    <textarea name="notes" placeholder="Approval notes..." rows="2"></textarea>
                                </div>
                                
                                <button type="submit" name="action" value="approve" class="btn-approve">✓ Approve</button>
                                <button type="submit" name="action" value="reject" class="btn-reject">✗ Reject</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #999; padding: 40px;">No pending products to review.</p>
            <?php endif; ?>
        </div>

        <!-- Users Tab -->
        <div id="users" class="tab-content <?= $tab === 'users' ? 'active' : '' ?>">
            <h2 style="margin-bottom: 20px;">Pending User Approvals</h2>
            
            <?php if ($users_result->num_rows > 0): ?>
                <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <thead>
                        <tr style="background-color: #f8f9fa; border-bottom: 2px solid #ddd;">
                            <th style="padding: 15px; text-align: left;">Username</th>
                            <th style="padding: 15px; text-align: left;">Email</th>
                            <th style="padding: 15px; text-align: left;">Role</th>
                            <th style="padding: 15px; text-align: left;">Joined</th>
                            <th style="padding: 15px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users_result->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px;"><?= htmlspecialchars($user['username']) ?></td>
                                <td style="padding: 15px;"><?= htmlspecialchars($user['email']) ?></td>
                                <td style="padding: 15px; text-transform: capitalize;"><?= $user['role'] ?></td>
                                <td style="padding: 15px; font-size: 14px; color: #666;"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                <td style="padding: 15px; text-align: center;">
                                    <form method="POST" action="database/admin_approve_user.php" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" name="action" value="approve" class="btn-approve" style="padding: 6px 12px; font-size: 14px;">Approve</button>
                                        <button type="submit" name="action" value="reject" class="btn-reject" style="padding: 6px 12px; font-size: 14px;">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #999; padding: 40px;">No pending users to review.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
