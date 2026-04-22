<?php
// Cart page - displays items from localStorage via JavaScript
include 'auth/session.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Ordering System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Tech Store</h1>
            <nav>
                <a href="index.php" class="nav-link">Products</a>
                <a href="cart.php" class="nav-link">Cart (<span class="cart-count">0</span>)</a>
                <?php if (isCustomer()): ?>
                    <a href="dashboards/customer_dashboard.php" class="nav-link">Profile</a>
                    <a href="auth/logout.php" class="nav-link logout-link">Logout</a>
                <?php elseif (isProducer()): ?>
                    <a href="dashboards/producer_dashboard.php" class="nav-link">Producer Dashboard</a>
                    <a href="auth/logout.php" class="nav-link logout-link">Logout</a>
                <?php elseif (isAdmin()): ?>
                    <a href="dashboards/admin_dashboard.php" class="nav-link">Admin Dashboard</a>
                    <a href="auth/logout.php" class="nav-link logout-link">Logout</a>
                <?php else: ?>
                    <a href="auth/login.php" class="nav-link">Login</a>
                    <a href="auth/register.php" class="nav-link">Register</a>
                <?php endif; ?>
            </nav>
        </header>

        <main>
            <h2>Shopping Cart</h2>
            
            <?php if (!isCustomer()): ?>
                <div style="background-color: #fff3cd; border: 1px solid #ffc107; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                    <h3 style="color: #856404; margin-top: 0;">Login Required</h3>
                    <p style="color: #856404; margin-bottom: 15px;">You must be logged in to checkout. Please login or create an account to proceed with your order.</p>
                    <a href="auth/login.php" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;">Login</a>
                    <a href="auth/register.php" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px;">Register</a>
                </div>
            <?php endif; ?>
            
            <div id="cart-container">
                <p class="empty-cart">Your cart is empty. <a href="index.php">Continue shopping</a></p>
            </div>

            <div id="checkout-container" style="display: none;">
                <div class="cart-summary">
                    <p><strong>Subtotal:</strong> $<span id="subtotal">0.00</span></p>
                    <p><strong>Tax (10%):</strong> $<span id="tax">0.00</span></p>
                    <p class="total"><strong>Total:</strong> $<span id="total">0.00</span></p>
                </div>

                <div class="address-form">
                    <h3>Delivery Address</h3>
                    <div class="form-group">
                        <label for="address">Street Address *</label>
                        <input type="text" id="address" name="address" placeholder="e.g., 123 Main Street" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="postcode">Postcode *</label>
                            <input type="text" id="postcode" name="postcode" placeholder="e.g., 12345" required>
                        </div>
                        <div class="form-group">
                            <label for="city">City *</label>
                            <input type="text" id="city" name="city" placeholder="e.g., New York" required>
                        </div>
                    </div>
                </div>
                
                <button id="checkout-btn" class="btn btn-success">Place Order</button>
                <button id="clear-cart-btn" class="btn btn-secondary">Clear Cart</button>
            </div>

            <div id="success-message" class="success-message" style="display: none;">
                <h3>Order Placed Successfully!</h3>
                <p>Thank you for your purchase. Your order has been confirmed.</p>
                <a href="index.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        </main>

        <footer>
            <p>&copy; 2026 Tech Store. All rights reserved.</p>
        </footer>
    </div>

    <script src="js/cart.js"></script>
    <script>
        // Display cart on page load
        document.addEventListener('DOMContentLoaded', function() {
            displayCart();
            updateCartCount();
        });
    </script>
</body>
</html>
