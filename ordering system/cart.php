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
            
            <div id="cart-container">
                <p class="empty-cart">Your cart is empty. <a href="index.php">Continue shopping</a></p>
            </div>

            <div id="checkout-container" style="display: none;">
                <div class="cart-summary">
                    <p><strong>Subtotal:</strong> $<span id="subtotal">0.00</span></p>
                    <p><strong>Tax (10%):</strong> $<span id="tax">0.00</span></p>
                    <p class="total"><strong>Total:</strong> $<span id="total">0.00</span></p>
                </div>
                <button id="checkout-btn" class="btn btn-success">Proceed to Checkout</button>
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
