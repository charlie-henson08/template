<?php
include 'db_config.php';
include 'auth/session.php';

// Fetch all products from database
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
$products = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordering System - Products</title>
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
            <h2>Available Products</h2>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="https://via.placeholder.com/200?text=<?php echo urlencode($product['name']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>
                            <button class="btn btn-primary add-to-cart" data-id="<?php echo $product['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($product['name']); ?>" 
                                    data-price="<?php echo $product['price']; ?>">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>

        <footer>
            <p>&copy; 2026 Tech Store. All rights reserved.</p>
        </footer>
    </div>

    <script src="js/cart.js"></script>
</body>
</html>
