# Ubelt Grabs - Database Integration Setup Guide

This guide will walk you through setting up the database and getting your food delivery platform running with approval systems.

## Prerequisites
- PHP 7.4 or higher
- MySQL/phpMyAdmin (XAMPP recommended)
- Local server running (Apache)

## Step 1: Database Setup

### 1.1 Create the Database
Open phpMyAdmin and:
1. Click "New" to create a new database
2. Name it: `ubelt` (or update `database/db.php` with your database name)
3. Set collation to: `utf8mb4_unicode_ci`
4. Click Create

### 1.2 Run Schema Setup
1. Copy `database/schema.php` to your web root
2. Visit: `http://localhost/database/schema.php` in your browser
3. You should see success messages for all tables created
4. Run `database/audit_logs.php` the same way to create logging table

### 1.3 Verify Tables in phpMyAdmin
You should now have these tables:
- `users` - Stores user accounts (sellers, customers, admins)
- `products` - Stores products with approval status
- `recommendations` - Stores recommended products
- `cart` - Stores shopping cart items
- `admin_logs` - Logs all admin actions

## Step 2: File Structure

Place these files in your web root:

\`\`\`
/ubelt_grabs/
├── index.html
├── seller.html (convert to seller.php)
├── menu.html (convert to menu.php)
├── login.html (use logins.php)
├── signup.html (use signups.php)
├── admin_dashboard.php (NEW)
├── database/
│   ├── db.php
│   ├── schema.php
│   ├── config_validation.php (NEW)
│   ├── audit_logs.php (NEW)
│   ├── add_product.php (UPDATED)
│   ├── updated_add_product.php (NEW - use this version)
│   ├── admin_approve_product.php (NEW)
│   ├── admin_approve_user.php (NEW)
│   ├── add_to_cart.php (NEW)
│   ├── seller_products.php (NEW)
│   └── phpMyAdmin.txt
├── uploads/
│   └── (images will be stored here)
├── config/
│   └── session_manager.php (NEW)
├── assets/
│   ├── css/
│   └── js/
└── includes/
    ├── header.php
    ├── footer.php
    └── navbar.php
\`\`\`

## Step 3: User Roles & Permissions

### Three User Types:
1. **Admin** - Can approve/reject products and users
2. **Seller** - Can upload products (must be approved)
3. **Customer** - Can browse and order approved products

### Registration Flow:
1. User signs up
2. Status set to `pending` (awaits admin approval)
3. Admin reviews in dashboard
4. Once approved, user can use full features

## Step 4: Product Approval Flow

### Seller:
1. Login to `seller.php`
2. Upload product with image, name, price, description
3. Submit for approval (status = pending)
4. View status of their products

### Admin:
1. Login with admin role
2. Visit `admin_dashboard.php`
3. See all pending products
4. Review and click "Approve" or "Reject"
5. Can add notes for rejection reasons

### Customer:
1. View only APPROVED products in `menu.php`
2. See recommended products
3. Add to cart and checkout

## Step 5: Configuration

### Update database/db.php:
\`\`\`php
$host = "127.0.0.1:3307";     // Change port if needed
$user = "root";               // Your MySQL user
$pass = "";                   // Your MySQL password
$dbname = "ubelt";            // Your database name
\`\`\`

### Session Security (config/session_manager.php):
For HTTPS/production, enable:
\`\`\`php
ini_set('session.cookie_secure', 1);  // Only send over HTTPS
\`\`\`

## Step 6: Testing the System

### Create Test Admin:
1. Register a user account
2. In phpMyAdmin, update user record:
   \`\`\`sql
   UPDATE users SET role = 'admin', status = 'approved' 
   WHERE username = 'your_username';
   \`\`\`

### Create Test Seller:
1. Register another account
2. Update in phpMyAdmin:
   \`\`\`sql
   UPDATE users SET role = 'seller', status = 'approved' 
   WHERE username = 'seller_username';
   \`\`\`

### Test Flow:
1. Login as Seller → Upload product (pending status)
2. Login as Admin → Approve product
3. Login as Customer → See approved product in menu

## Step 7: Security Features Implemented

✓ **SQL Injection Protection** - Prepared statements everywhere
✓ **Input Validation** - All user inputs validated
✓ **File Upload Security** - MIME type & extension validation
✓ **Session Security** - HTTPOnly cookies, session timeout
✓ **XSS Protection** - htmlspecialchars on all outputs
✓ **CSRF Protection** - Session verification
✓ **Admin Logging** - All admin actions logged
✓ **Image Size Limits** - 5MB max per upload

## File Upload Storage

Images are stored in `/uploads/` with unique names to prevent conflicts:
- Original: `recipe.jpg`
- Stored as: `product_1234567890.1234.jpg`

## Troubleshooting

### Products not showing in menu?
- Check if status = 'approved' in phpMyAdmin products table
- Verify image path exists in uploads folder

### Upload fails?
- Ensure `uploads/` folder exists and has write permissions
- Check file size (max 5MB)
- Verify image format (jpg, png, gif, webp)

### Admin page not accessible?
- Verify user role = 'admin' in database
- Check session is active (might have timed out after 30 min)

### Database connection errors?
- Verify db.php credentials match phpMyAdmin
- Ensure MySQL is running
- Check port (default 3307 for XAMPP)

## API Endpoints

### Cart Management
- **POST** `/database/add_to_cart.php` - Add item to cart
  - Params: `product_id`

### Admin Approval
- **POST** `/database/admin_approve_product.php` - Approve/reject product
  - Params: `product_id`, `action` (approve/reject), `notes`
- **POST** `/database/admin_approve_user.php` - Approve/reject user
  - Params: `user_id`, `action` (approve/reject)

### Seller Dashboard
- **GET** `/database/seller_products.php` - Get seller's products
  - Params: `status` (all/pending/approved/rejected)

## Next Steps

1. Add payment gateway (Stripe/PayPal)
2. Implement order management
3. Add review/rating system
4. Create email notifications
5. Add analytics dashboard
6. Implement search and filters

---

For questions or issues, contact the development team.
