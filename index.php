<?php
include "database/db.php";

// Fetch universities once
$universities = [];
$result = $conn->query("SELECT * FROM universities");
while ($row = $result->fetch_assoc()) {
    $universities[] = $row;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Ubelt Grabs</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<div class="container py-4">

  <!-- Header -->
  <header class="text-center mb-4">
    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" 
         style="width:80px;height:80px;font-size:1.5rem;">UG</div>
    <h1 class="mt-3 fw-bold">UBELT GRABS</h1>
    <p class="text-muted">University food delivery — select Buyer or Seller</p>
  </header>

  <!-- Home screen -->
  <main id="screen-home" 
        class="card shadow-sm p-4 <?= (isset($_GET['seller']) || isset($_GET['success']) || isset($_GET['error'])) ? 'd-none' : '' ?>">
    <h2 class="h5 fw-bold">Welcome</h2>
    <p class="small text-muted">Choose a role to continue.</p>
    <div class="row g-3 mt-2">
      <div class="col-6">
        <button id="btnBuyer" class="btn btn-danger w-100 py-3">🛒 Buyer</button>
      </div>
      <div class="col-6">
        <button id="btnSeller" class="btn btn-outline-secondary w-100 py-3">🏪 Seller</button>
      </div>
    </div>
  </main>

  <!-- Buyer screen -->
  <section id="screen-buyer" class="d-none">
    <div class="d-flex justify-content-between align-items-center my-3">
      <div>
        <h2 class="h5 fw-bold">Buyer — Browse Products</h2>
        <p class="text-muted small mb-0">Participating schools only</p>
      </div>
      <button id="buyer-back" class="btn btn-link">← Back</button>
    </div>

    <div class="row g-2 mb-3">
      <div class="col-6 col-md-4">
        <select id="buyerUniversity" class="form-select">
          <option value="">All Schools</option>
          <?php foreach ($universities as $uni): ?>
            <option value="<?= $uni['id'] ?>"><?= htmlspecialchars($uni['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-8">
        <input id="buyerSearch" type="search" class="form-control" placeholder="Search products...">
      </div>
    </div>

    <div id="buyerGrid" class="row g-3">
      <?php
      $result = $conn->query("SELECT p.*, u.name AS university 
                              FROM products p 
                              JOIN universities u ON p.university_id = u.id 
                              ORDER BY p.created_at DESC");

      if ($result && $result->num_rows > 0):
        while ($row = $result->fetch_assoc()): ?>
          <div class="col-md-4">
            <div class="card h-100">
              <img src="<?= !empty($row['image_url']) ? htmlspecialchars($row['image_url']) : 'https://via.placeholder.com/150' ?>" 
                   class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>">
              <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                <p class="card-text text-muted">₱<?= number_format($row['price'], 2) ?> — <?= htmlspecialchars($row['university']) ?></p>
                <?php if (!empty($row['description'])): ?>
                  <p class="small"><?= htmlspecialchars($row['description']) ?></p>
                <?php endif; ?>
                <?php if (!empty($row['fb_page'])): ?>
                  <a href="<?= htmlspecialchars($row['fb_page']) ?>" target="_blank" class="btn btn-sm btn-danger">Facebook Page</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="alert alert-secondary">No products available yet.</div>
      <?php endif; ?>
    </div>
    <div id="buyerEmpty" class="alert alert-secondary d-none mt-3">No products found.</div>
  </section>

  <!-- Seller screen -->
  <section id="screen-seller" 
           class="<?= (isset($_GET['seller']) || isset($_GET['success']) || isset($_GET['error'])) ? '' : 'd-none' ?>">
    <div class="d-flex justify-content-between align-items-center my-3">
      <div>
        <h2 class="h5 fw-bold">Seller Dashboard</h2>
        <p class="small text-muted">Add products and link your FB page</p>
      </div>
      <button id="seller-back" class="btn btn-link">← Back</button>
    </div>

    <!-- Alerts -->
    <?php if (isset($_GET['success'])): ?>
      <?php if ($_GET['success'] === '1'): ?>
        <div class="alert alert-success alert-dismissible fade show">Product added successfully.
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php elseif ($_GET['success'] === 'product_updated'): ?>
        <div class="alert alert-success alert-dismissible fade show">Product updated successfully.
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php elseif ($_GET['success'] === 'product_deleted'): ?>
        <div class="alert alert-success alert-dismissible fade show">Product deleted successfully.
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
      <?php if ($_GET['error'] === 'no_product_id'): ?>
        <div class="alert alert-danger alert-dismissible fade show">No product ID provided.
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php elseif ($_GET['error'] === 'product_not_found'): ?>
        <div class="alert alert-danger alert-dismissible fade show">Product not found.
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <!-- Add Product Form -->
    <form id="sellerForm" method="post" action="database/add_product.php" class="card card-body shadow-sm mb-4">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Product name</label>
          <input name="pName" required class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Price (₱)</label>
          <input name="pPrice" type="number" step="0.01" required class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Category</label>
          <select name="pCategory" class="form-select">
            <option value="rice">Rice</option>
            <option value="noodles">Noodles</option>
            <option value="snacks">Snacks</option>
            <option value="drinks">Drinks</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">University</label>
          <select name="pUniversity" required class="form-select">
            <?php foreach ($universities as $uni): ?>
              <option value="<?= $uni['id'] ?>"><?= htmlspecialchars($uni['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Facebook page (optional)</label>
          <input name="pFB" type="url" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Image URL (optional)</label>
          <input name="pImage" type="url" class="form-control">
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea name="pDesc" rows="2" class="form-control"></textarea>
        </div>
      </div>
      <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-danger flex-fill">Add Product</button>
        <button type="reset" class="btn btn-outline-secondary flex-fill">Clear</button>
      </div>
    </form>

    <!-- Seller Products -->
    <h3 class="h6 fw-bold mb-2">My Products</h3>
    <div id="sellerList" class="row g-3">
      <?php
      $seller_products = $conn->query("SELECT p.*, u.name AS university_name 
                                      FROM products p 
                                      LEFT JOIN universities u ON p.university_id = u.id 
                                      ORDER BY p.created_at DESC");

      if ($seller_products && $seller_products->num_rows > 0):
        while ($product = $seller_products->fetch_assoc()): ?>
          <div class="col-md-4">
            <div class="card shadow-sm h-100 border-0">
              <?php if (!empty($product['image_url'])): ?>
                <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                     class="card-img-top" 
                     alt="<?= htmlspecialchars($product['name']) ?>" 
                     style="height:200px;object-fit:cover;">
              <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center card-img-top" style="height:200px;">
                  <span class="text-muted small">No Image</span>
                </div>
              <?php endif; ?>

              <div class="card-body d-flex flex-column">
                <h5 class="card-title mb-1"><?= htmlspecialchars($product['name']) ?></h5>
                <p class="card-text text-muted mb-2">
                  ₱<?= number_format($product['price'], 2) ?> — 
                  <?= htmlspecialchars($product['university_name']) ?> • 
                  <?= ucfirst(htmlspecialchars($product['category'])) ?>
                </p>
                <?php if (!empty($product['description'])): ?>
                  <p class="card-text small text-secondary mb-2"><?= htmlspecialchars($product['description']) ?></p>
                <?php endif; ?>

                <div class="mt-auto d-flex gap-2">
                  <?php if (!empty($product['fb_page'])): ?>
                    <a href="<?= htmlspecialchars($product['fb_page']) ?>" target="_blank" 
                       class="btn btn-sm btn-outline-primary flex-fill">Facebook</a>
                  <?php endif; ?>

                  <button class="btn btn-sm btn-outline-warning btn-edit flex-fill"
                          data-id="<?= $product['id'] ?>"
                          data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                          data-price="<?= $product['price'] ?>"
                          data-category="<?= $product['category'] ?>"
                          data-university="<?= $product['university_id'] ?>"
                          data-fb="<?= htmlspecialchars($product['fb_page'], ENT_QUOTES) ?>"
                          data-image="<?= htmlspecialchars($product['image_url'], ENT_QUOTES) ?>"
                          data-desc="<?= htmlspecialchars($product['description'], ENT_QUOTES) ?>">
                          ✏️ Edit
                  </button>

                  <a href="database/delete_product.php?id=<?= $product['id'] ?>" 
                     onclick="return confirm('Are you sure you want to delete this product?')" 
                     class="btn btn-sm btn-outline-danger flex-fill">❌ Delete</a>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="alert alert-info">You have not added any products yet.</div>
        </div>
      <?php endif; ?>
    </div>
  </section>
</div>

<!-- Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:11">
  <div id="toast" class="toast text-bg-dark border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body"></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/srcipt.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);

  // Auto-show seller dashboard
  if (urlParams.has('seller') || urlParams.has('success') || urlParams.has('error')) {
    document.getElementById('screen-home').classList.add('d-none');
    document.getElementById('screen-seller').classList.remove('d-none');
  }

  // Handle Edit buttons
  const editButtons = document.querySelectorAll('.btn-edit');
  const editModal = new bootstrap.Modal(document.getElementById('editModal'));

  editButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('editId').value = btn.dataset.id;
      document.getElementById('editName').value = btn.dataset.name;
      document.getElementById('editPrice').value = btn.dataset.price;
      document.getElementById('editCategory').value = btn.dataset.category;
      document.getElementById('editUniversity').value = btn.dataset.university;
      document.getElementById('editFB').value = btn.dataset.fb;
      document.getElementById('editImage').value = btn.dataset.image;
      document.getElementById('editDesc').value = btn.dataset.desc;
      editModal.show();
    });
  });
});
</script>

<!-- Edit Product Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="editForm" method="post" action="database/edit_product.php">
        <div class="modal-header">
          <h5 class="modal-title">✏️ Edit Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="editId">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Product name</label>
              <input name="pName" id="editName" required class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Price (₱)</label>
              <input name="pPrice" id="editPrice" type="number" step="0.01" required class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Category</label>
              <select name="pCategory" id="editCategory" class="form-select">
                <option value="rice">Rice</option>
                <option value="noodles">Noodles</option>
                <option value="snacks">Snacks</option>
                <option value="drinks">Drinks</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">University</label>
              <select name="pUniversity" id="editUniversity" required class="form-select">
                <?php foreach ($universities as $uni): ?>
                  <option value="<?= $uni['id'] ?>"><?= htmlspecialchars($uni['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Facebook page (optional)</label>
              <input name="pFB" id="editFB" type="url" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Image URL (optional)</label>
              <input name="pImage" id="editImage" type="url" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea name="pDesc" id="editDesc" rows="2" class="form-control"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Save Changes</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

</body>
</html>
