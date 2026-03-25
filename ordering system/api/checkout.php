<?php
header('Content-Type: application/json');

include '../db_config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    // Get POST data
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate cart data
    if (!isset($data['cart']) || empty($data['cart'])) {
        throw new Exception('Cart is empty');
    }

    $cart = $data['cart'];

    // Validate cart items
    foreach ($cart as $item) {
        if (!isset($item['id']) || !isset($item['quantity'])) {
            throw new Exception('Invalid cart item data');
        }
    }

    // Start transaction
    $conn->begin_transaction();

    // Create new order
    $sql = "INSERT INTO orders (created_at) VALUES (NOW())";
    if (!$conn->query($sql)) {
        throw new Exception('Error creating order: ' . $conn->error);
    }

    $order_id = $conn->insert_id;

    // Insert order items
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . $conn->error);
    }

    foreach ($cart as $item) {
        $product_id = intval($item['id']);
        $quantity = intval($item['quantity']);

        $stmt->bind_param("iii", $order_id, $product_id, $quantity);

        if (!$stmt->execute()) {
            throw new Exception('Error inserting order item: ' . $stmt->error);
        }
    }

    $stmt->close();

    // Commit transaction
    $conn->commit();

    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn) {
        $conn->rollback();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if (isset($conn)) {
    $conn->close();
}
