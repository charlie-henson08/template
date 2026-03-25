<?php
include '../db_config.php';
include '../auth/session.php';

requireProducer();

$producerId = $_SESSION['producer_id'];
$tab = $_GET['tab'] ?? 'products';

// Fetch producer data
$stmt = $conn->prepare("SELECT full_name, email, company_name FROM producers WHERE id = ?");
$stmt->bind_param("i", $producerId);
$stmt->execute();
$result = $stmt->get_result();
$producer = $result->fetch_assoc();
$stmt->close();

// Handle product update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_product') {
        $productId = intval($_POST['product_id']);
        $name = trim($_POST['name']);
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);

        $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, stock = ? WHERE id = ? AND producer_id = ?");
        $stmt->bind_param("sdiii", $name, $price, $stock, $productId, $producerId);

        if ($stmt->execute()) {
            $success = 'Product updated successfully!';
        } else {
            $error = 'Failed to update product';
        }
        $stmt->close();
    }
}

// Fetch products for this producer
$stmt = $conn->prepare("SELECT id, name, price, stock, image FROM products WHERE producer_id = ? ORDER BY name");
$stmt->bind_param("i", $producerId);
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();

// Fetch orders for this producer's products
$stmt = $conn->prepare("
    SELECT o.id, o.created_at, oi.product_id, oi.quantity, p.name, p.price
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN products p ON p.id = oi.product_id
    WHERE p.producer_id = ?
    ORDER BY o.created_at DESC
    LIMIT 20
");
$stmt->bind_param("i", $producerId);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Producer Dashboard - Tech Store</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .dashboard-header {
            background-color: #28a745;
            color: white;
            padding: 30px 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-header h1 {
            margin: 0;
        }

        .dashboard-header .logout-btn {
            background-color: rgba(255,255,255,0.2);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .dashboard-header .logout-btn:hover {
            background-color: rgba(255,255,255,0.3);
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #dee2e6;
        }

        .tab-link {
            padding: 12px 20px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .tab-link:hover {
            color: #28a745;
        }

        .tab-link.active {
            color: #28a745;
            border-bottom-color: #28a745;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .dashboard-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .dashboard-card h2 {
            color: #28a745;
            margin-top: 0;
            border-bottom: 2px solid #28a745;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .products-table,
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table th,
        .orders-table th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        .products-table td,
        .orders-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }

        .products-table tbody tr:hover,
        .orders-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .edit-btn {
            background-color: #007bff;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }

        .edit-btn:hover {
            background-color: #0056b3;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
        }

        .modal-content h2 {
            color: #28a745;
            margin-top: 0;
        }

        .modal-content .form-group {
            margin-bottom: 20px;
        }

        .modal-content label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .modal-content input,
        .modal-content textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .modal-content input:focus,
        .modal-content textarea:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40,167,69,0.1);
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .modal-buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .save-btn {
            background-color: #28a745;
            color: white;
        }

        .save-btn:hover {
            background-color: #218838;
        }

        .cancel-btn {
            background-color: #6c757d;
            color: white;
        }

        .cancel-btn:hover {
            background-color: #5a6268;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .profile-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .profile-info p {
            margin: 0;
        }

        .profile-info strong {
            color: #666;
        }

        @media (max-width: 768px) {
            .tabs {
                flex-wrap: wrap;
            }

            .modal-content {
                width: 95%;
                margin: 20% auto;
            }

            .profile-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><?php echo htmlspecialchars($producer['company_name']); ?></h1>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">Managed by <?php echo htmlspecialchars($producer['full_name']); ?></p>
            </div>
            <a href="../auth/logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="dashboard-card">
            <h2>Company Information</h2>
            <div class="profile-info">
                <div><strong>Manager:</strong> <?php echo htmlspecialchars($producer['full_name']); ?></div>
                <div><strong>Email:</strong> <?php echo htmlspecialchars($producer['email']); ?></div>
                <div><strong>Company:</strong> <?php echo htmlspecialchars($producer['company_name']); ?></div>
            </div>
        </div>

        <div class="tabs">
            <a href="?tab=products" class="tab-link <?php echo $tab === 'products' ? 'active' : ''; ?>">Products</a>
            <a href="?tab=stock" class="tab-link <?php echo $tab === 'stock' ? 'active' : ''; ?>">Stock Management</a>
            <a href="?tab=orders" class="tab-link <?php echo $tab === 'orders' ? 'active' : ''; ?>">Orders</a>
        </div>

        <!-- Products Tab -->
        <div class="tab-content <?php echo $tab === 'products' ? 'active' : ''; ?>">
            <div class="dashboard-card">
                <h2>Product Descriptions</h2>

                <?php if (!empty($success)): ?>
                    <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <p>No products yet. You'll see products here once they're added to the store.</p>
                    </div>
                <?php else: ?>
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo $product['stock']; ?> units</td>
                                    <td>
                                        <button class="edit-btn" onclick="openEditModal(
                                            <?php echo $product['id']; ?>,
                                            '<?php echo htmlspecialchars($product['name']); ?>',
                                            <?php echo $product['price']; ?>,
                                            <?php echo $product['stock']; ?>
                                        )">Edit</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stock Tab -->
        <div class="tab-content <?php echo $tab === 'stock' ? 'active' : ''; ?>">
            <div class="dashboard-card">
                <h2>Stock Levels</h2>

                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <p>No products to manage.</p>
                    </div>
                <?php else: ?>
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Current Stock</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo $product['stock']; ?> units</td>
                                    <td>
                                        <?php if ($product['stock'] <= 10): ?>
                                            <span style="color: #dc3545; font-weight: bold;">Low Stock</span>
                                        <?php elseif ($product['stock'] <= 25): ?>
                                            <span style="color: #ffc107; font-weight: bold;">Medium</span>
                                        <?php else: ?>
                                            <span style="color: #28a745; font-weight: bold;">Healthy</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="edit-btn" onclick="openEditModal(
                                            <?php echo $product['id']; ?>,
                                            '<?php echo htmlspecialchars($product['name']); ?>',
                                            <?php echo $product['price']; ?>,
                                            <?php echo $product['stock']; ?>
                                        )">Adjust</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Orders Tab -->
        <div class="tab-content <?php echo $tab === 'orders' ? 'active' : ''; ?>">
            <div class="dashboard-card">
                <h2>Recent Orders</h2>

                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <p>No orders yet for your products.</p>
                    </div>
                <?php else: ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['name']); ?></td>
                                    <td><?php echo $order['quantity']; ?></td>
                                    <td>$<?php echo number_format($order['price'], 2); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Product</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_product">
                <input type="hidden" id="product_id" name="product_id">

                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="price">Price ($)</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="stock">Stock Quantity</label>
                    <input type="number" id="stock" name="stock" required>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="cancel-btn" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="save-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(productId, name, price, stock) {
            document.getElementById('product_id').value = productId;
            document.getElementById('name').value = name;
            document.getElementById('price').value = price;
            document.getElementById('stock').value = stock;
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('name').focus();
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
