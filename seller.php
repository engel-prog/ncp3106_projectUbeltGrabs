<?php
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Seller Dashboard | Ubelt Grabs</title>
  <link rel="stylesheet" href="seller.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar">
    <div class="navbar__container">
      <a href="index.html" id="navbar__logo">
        <img src="uploads/logoreal.png" alt="Ubelt Grabs" class="nav__logo-img"/>
        <span class="nav__logo-text">Ubelt Grabs</span>
      </a>
      <ul class="navbar__menu">
        <li class="navbar__item"><a href="index.html" class="navbar__links">Home</a></li>
        <li class="navbar__item"><a href="menu.html" class="navbar__links">Menu</a></li>
        <li class="navbar__item"><a href="about.html" class="navbar__links">About</a></li>
        <li class="navbar__btn"><a href="login.html" class="button">Log Out</a></li>
      </ul>
    </div>
  </nav>

  <!-- Seller Hero Section -->
  <header class="hero seller-hero">
    <div class="hero__container">
      <h1 class="hero__title">Welcome, Seller!</h1>
      <p class="hero__subtitle">Upload your best local picks and start earning today.</p>
    </div>
  </header>

  <!-- Upload Section -->
  <section class="upload">
  <h2 class="section__title">Add New Food Item</h2>
  <form id="uploadForm" class="upload__form" method="POST" action="add_product.php" enctype="multipart/form-data">
    <div class="form__group">
      <label for="foodImage">Food Image:</label>
      <input type="file" id="foodImage" name="foodImage" accept="image/*" required>
      <img id="imagePreview" class="image-preview" alt="Image preview" style="display:none;">
    </div>

    <div class="form__group">
      <label for="foodName">Food Name:</label>
      <input type="text" id="foodName" name="foodName" placeholder="e.g. Chicken Adobo" required>
    </div>

    <div class="form__group">
      <label for="foodPrice">Price (₱):</label>
      <input type="number" id="foodPrice" name="foodPrice" placeholder="e.g. 120" required>
    </div>

    <div class="form__group">
      <label for="foodDesc">Description:</label>
      <textarea id="foodDesc" name="foodDesc" rows="3" placeholder="Short description..." required></textarea>
    </div>

    <button type="submit" class="button upload__btn">Upload Item</button>
  </form>
</section>


    <!-- Uploaded Items Preview -->
    <section class="features">
      <h2 class="section__title">Your Uploaded Items</h2>
      <div class="grid" id="sellerItems">
        <p class="no-items">No items uploaded yet.</p>
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
    // Year in footer
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

    // Handle Upload (UI only)
    const uploadForm = document.getElementById('uploadForm');
    const sellerItems = document.getElementById('sellerItems');

    uploadForm.addEventListener('submit', e => {
      e.preventDefault();

      const name = document.getElementById('foodName').value;
      const price = document.getElementById('foodPrice').value;
      const desc = document.getElementById('foodDesc').value;
      const imgSrc = imagePreview.src || 'https://via.placeholder.com/400x250';

      // Remove "no items" text
      const noItemsText = sellerItems.querySelector('.no-items');
      if (noItemsText) noItemsText.remove();

      // Create new card
      const card = document.createElement('article');
      card.classList.add('card');
      card.innerHTML = `
        <img src="${imgSrc}" alt="${name}" class="card__img">
        <div class="card__body">
          <h3 class="card__title">${name}</h3>
          <p class="card__desc">₱${price} — ${desc}</p>
          <button class="button card__btn delete-btn">Remove</button>
        </div>
      `;
      sellerItems.appendChild(card);

      // Reset form
      uploadForm.reset();
      imagePreview.style.display = 'none';

      // Add delete functionality
      card.querySelector('.delete-btn').addEventListener('click', () => {
        card.remove();
        if (!sellerItems.querySelector('.card')) {
          sellerItems.innerHTML = '<p class="no-items">No items uploaded yet.</p>';
        }
      });
    });
  </script>
</body>
</html>
?>