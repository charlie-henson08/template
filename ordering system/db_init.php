<?php
// Database Initialization - Create tables and insert sample products

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ordering_system";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . $dbname;
if ($conn->query($sql) === FALSE) {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select database
$conn->select_db($dbname);

// Create products table
$sql = "CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255)
)";

if ($conn->query($sql) === FALSE) {
    echo "Error creating products table: " . $conn->error . "<br>";
} else {
    echo "✓ Products table ready<br>";
}

// Create orders table
$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === FALSE) {
    echo "Error creating orders table: " . $conn->error . "<br>";
} else {
    echo "✓ Orders table ready<br>";
}

// Create order_items table
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
)";

if ($conn->query($sql) === FALSE) {
    echo "Error creating order_items table: " . $conn->error . "<br>";
} else {
    echo "✓ Order_items table ready<br>";
}

// Create users table (customers)
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    loyalty_points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === FALSE) {
    echo "Error creating users table: " . $conn->error . "<br>";
} else {
    echo "✓ Users table ready<br>";
}

// Create producers table
$sql = "CREATE TABLE IF NOT EXISTS producers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    company_name VARCHAR(100),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'inactive',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === FALSE) {
    echo "Error creating producers table: " . $conn->error . "<br>";
} else {
    echo "✓ Producers table ready<br>";
}

// Create admins table
$sql = "CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === FALSE) {
    echo "Error creating admins table: " . $conn->error . "<br>";
} else {
    echo "✓ Admins table ready<br>";
}

// Create loyalty_transactions table
$sql = "CREATE TABLE IF NOT EXISTS loyalty_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    points INT NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($sql) === FALSE) {
    echo "Error creating loyalty_transactions table: " . $conn->error . "<br>";
} else {
    echo "✓ Loyalty_transactions table ready<br>";
}

// Add stock column to products if it doesn't exist
$result = $conn->query("SHOW COLUMNS FROM products LIKE 'stock'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE products ADD COLUMN stock INT DEFAULT 100";
    if ($conn->query($sql) === FALSE) {
        echo "Error adding stock column: " . $conn->error . "<br>";
    } else {
        echo "✓ Stock column added to products<br>";
    }
}

// Add producer_id column to products if it doesn't exist
$result = $conn->query("SHOW COLUMNS FROM products LIKE 'producer_id'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE products ADD COLUMN producer_id INT, ADD FOREIGN KEY (producer_id) REFERENCES producers(id)";
    if ($conn->query($sql) === FALSE) {
        echo "Error adding producer_id column: " . $conn->error . "<br>";
    } else {
        echo "✓ Producer_id column added to products<br>";
    }
}

// Check if products table is empty and insert sample data
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Insert sample products
    $sql = "INSERT INTO products (name, price, image, stock) VALUES
        ('Laptop', 999.99, 'laptop.jpg', 15),
        ('Mouse', 29.99, 'mouse.jpg', 50),
        ('Keyboard', 79.99, 'keyboard.jpg', 30),
        ('Monitor', 299.99, 'monitor.jpg', 20),
        ('Headphones', 129.99, 'headphones.jpg', 25),
        ('Webcam', 89.99, 'webcam.jpg', 40)";

    if ($conn->query($sql) === TRUE) {
        echo "✓ Sample products inserted<br>";
    } else {
        echo "Error inserting products: " . $conn->error . "<br>";
    }
} else {
    echo "✓ Products already exist in database<br>";
}

// Insert demo admin if none exist
$result = $conn->query("SELECT COUNT(*) as count FROM admins");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $admin_pass = password_hash('admin123', PASSWORD_BCRYPT);
    $sql = "INSERT INTO admins (full_name, email, password) VALUES ('Admin User', 'admin@store.com', '$admin_pass')";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Demo admin created (email: admin@store.com, password: admin123)<br>";
    }
}

// Insert demo customer if none exist
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $customer_pass = password_hash('password', PASSWORD_BCRYPT);
    $sql = "INSERT INTO users (full_name, email, password, loyalty_points) VALUES ('John Doe', 'customer@store.com', '$customer_pass', 500)";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Demo customer created (email: customer@store.com, password: password)<br>";
    }
}

// Insert demo producer if none exist
$result = $conn->query("SELECT COUNT(*) as count FROM producers");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $producer_pass = password_hash('password', PASSWORD_BCRYPT);
    $sql = "INSERT INTO producers (full_name, email, password, company_name, status) VALUES ('Jane Smith', 'producer@store.com', '$producer_pass', 'TechCorp Supplies', 'active')";
    if ($conn->query($sql) === TRUE) {
        $producerId = $conn->insert_id;
        echo "✓ Demo producer created (email: producer@store.com, password: password)<br>";
        
        // Assign products to this producer
        $sql = "UPDATE products SET producer_id = $producerId LIMIT 3";
        $conn->query($sql);
    }
}

// Insert sample loyalty transaction for demo customer
$result = $conn->query("SELECT COUNT(*) as count FROM loyalty_transactions");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $sql = "INSERT INTO loyalty_transactions (user_id, points, description) VALUES (1, 500, 'Welcome bonus')";
    $conn->query($sql);
}

echo "<br><p style='color: green; font-weight: bold; font-size: 18px;'>✓ Database initialized successfully!</p>";
echo "<p><a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;'>Go to Product Listing</a></p>";

$conn->close();
