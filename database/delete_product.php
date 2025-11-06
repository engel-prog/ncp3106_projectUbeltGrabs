<?php
include "database/db.php";

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../index.php?error=no_product_id");
    exit;
}

$product_id = $_GET['id'];

// Fetch the product details for confirmation
$stmt = $conn->prepare("SELECT p.*, u.name AS university_name FROM products p 
                       LEFT JOIN universities u ON p.university_id = u.id 
                       WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../index.php?error=product_not_found");
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

// Handle form submission for deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_delete'])) {
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        header("Location: ../index.php?success=product_deleted");
        exit;
    } else {
        $error = "Error deleting product: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Delete Product - Ubelt Grabs</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

<div class="container py-4">
  <!-- Header -->
  <header class="text-center mb-4">
    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" 
         style="width:80px;height:80px;font-size:1.5rem;">UG</div>
    <h1 class="mt-3 fw-bold">UBELT GRABS</h1>
    <p class="text-muted">Delete Product</p>
  </header>

  <!-- Delete Confirmation -->
  <main class="card shadow-sm p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h5 fw-bold text-danger">Delete Product</h2>
      <a href="../index.php" class="btn btn-outline-secondary">← Back to Dashboard</a>
    </div>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="alert alert-warning">
      <h5 class="alert-heading">⚠️ Are you sure you want to delete this product?</h5>
      <p class="mb-0">This action cannot be undone. The product will be permanently removed from the system.</p>
    </div>

    <!-- Product Details -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row">
          <div class="col-md-3">
            <?php if (!empty($product['image_url'])): ?>
              <img src="<?= htmlspecialchars($product['image_url']) ?>" class="img-fluid rounded" alt="Product Image">
            <?php else: ?>
              <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                <span class="text-muted">No Image</span>
              </div>
            <?php endif; ?>
          </div>
          <div class="col-md-9">
            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
            <p class="card-text">
              <strong>Price:</strong> ₱<?= number_format($product['price'], 2) ?><br>
              <strong>Category:</strong> <?= ucfirst(htmlspecialchars($product['category'])) ?><br>
              <strong>University:</strong> <?= htmlspecialchars($product['university_name']) ?><br>
              <?php if (!empty($product['description'])): ?>
                <strong>Description:</strong> <?= htmlspecialchars($product['description']) ?><br>
              <?php endif; ?>
              <?php if (!empty($product['fb_page'])): ?>
                <strong>Facebook Page:</strong> <a href="<?= htmlspecialchars($product['fb_page']) ?>" target="_blank" class="text-decoration-none"><?= htmlspecialchars($product['fb_page']) ?></a>
              <?php endif; ?>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Form -->
    <form method="post" class="d-flex gap-2">
      <button type="submit" name="confirm_delete" class="btn btn-danger flex-fill">
        🗑️ Yes, Delete Product
      </button>
      <a href="../index.php" class="btn btn-outline-secondary flex-fill">
        Cancel
      </a>
    </form>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
