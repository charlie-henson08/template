<?php
include '../db_config.php';
include 'session.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: ../dashboards/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userType = trim($_POST['user_type'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($userType) || empty($email) || empty($password)) {
        $error = 'Please fill all fields';
    } else {
        if ($userType === 'customer') {
            // Login as customer
            $stmt = $conn->prepare("SELECT id, full_name, email, password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    loginCustomer($user['id'], $user['email']);
                    header('Location: ../dashboards/dashboard.php');
                    exit;
                } else {
                    $error = 'Invalid email or password';
                }
            } else {
                $error = 'Invalid email or password';
            }
            $stmt->close();
        } elseif ($userType === 'producer') {
            // Login as producer
            $stmt = $conn->prepare("SELECT id, full_name, email, password, company_name, status FROM producers WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $producer = $result->fetch_assoc();
                if (password_verify($password, $producer['password'])) {
                    if ($producer['status'] !== 'active') {
                        $error = 'Your account has been ' . $producer['status'] . ' by an administrator';
                    } else {
                        loginProducer($producer['id'], $producer['email'], $producer['company_name']);
                        header('Location: ../dashboards/dashboard.php');
                        exit;
                    }
                } else {
                    $error = 'Invalid email or password';
                }
            } else {
                $error = 'Invalid email or password';
            }
            $stmt->close();
        } elseif ($userType === 'admin') {
            // Login as admin
            $stmt = $conn->prepare("SELECT id, full_name, email, password FROM admins WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                if (password_verify($password, $admin['password'])) {
                    loginAdmin($admin['id'], $admin['email']);
                    header('Location: ../dashboards/dashboard.php');
                    exit;
                } else {
                    $error = 'Invalid email or password';
                }
            } else {
                $error = 'Invalid email or password';
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tech Store</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .login-container {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }

        .login-container h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #007bff;
        }

        .login-container .form-group {
            margin-bottom: 20px;
        }

        .login-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .login-container select,
        .login-container input[type="email"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .login-container input:focus,
        .login-container select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }

        .login-container button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .login-container button:hover {
            background-color: #0056b3;
        }

        .login-container .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .login-container .register-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        .login-container .register-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }

        .login-container .register-link a:hover {
            text-decoration: underline;
        }

        .demo-credentials {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #004085;
        }

        .demo-credentials strong {
            display: block;
            margin-bottom: 8px;
        }

        .demo-credentials p {
            margin: 4px 0;
        }

        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .tab-btn {
            flex: 1;
            padding: 10px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            border-color: #007bff;
            color: #007bff;
            background-color: #f0f8ff;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Tech Store Login</h1>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="demo-credentials">
            <strong>Demo Credentials:</strong>
            <p><strong>Customer:</strong> customer@store.com / password</p>
            <p><strong>Producer:</strong> producer@store.com / password</p>
            <p><strong>Admin:</strong> admin@store.com / admin123</p>
        </div>

        <div class="tab-buttons">
            <button class="tab-btn active" onclick="switchTab('customer', this)">Customer</button>
            <button class="tab-btn" onclick="switchTab('producer', this)">Producer</button>
            <button class="tab-btn" onclick="switchTab('admin', this)">Admin</button>
        </div>

        <form method="POST">
            <input type="hidden" name="user_type" id="user_type" value="customer">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Login</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>

    <script>
        function switchTab(userType, btn) {
            document.getElementById('user_type').value = userType;
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('email').focus();
        }
    </script>
</body>
</html>
