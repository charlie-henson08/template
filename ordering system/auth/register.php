<?php
include '../db_config.php';
include 'session.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: ../dashboards/dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userType = trim($_POST['user_type'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $companyName = trim($_POST['company_name'] ?? '');

    // Validation
    if (empty($userType) || empty($fullName) || empty($email) || empty($password)) {
        $error = 'Please fill all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        if ($userType === 'customer') {
            // Register as customer
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $fullName, $email, $hashedPassword);

            if ($stmt->execute()) {
                $success = 'Account created successfully! <a href="login.php">Login here</a>';
                $fullName = '';
                $email = '';
                $password = '';
                $confirmPassword = '';
            } else {
                if (strpos($stmt->error, 'Duplicate entry') !== false) {
                    $error = 'Email already registered';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
            $stmt->close();
        } elseif ($userType === 'producer') {
            // Register as producer
            if (empty($companyName)) {
                $error = 'Company name is required for producers';
            } else {
                $status = 'inactive'; // Producers start as inactive, admin must approve
                $stmt = $conn->prepare("INSERT INTO producers (full_name, email, password, company_name, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $fullName, $email, $hashedPassword, $companyName, $status);

                if ($stmt->execute()) {
                    $success = 'Producer account created! Awaiting admin approval. <a href="login.php">Login here</a>';
                    $fullName = '';
                    $email = '';
                    $password = '';
                    $confirmPassword = '';
                    $companyName = '';
                } else {
                    if (strpos($stmt->error, 'Duplicate entry') !== false) {
                        $error = 'Email already registered';
                    } else {
                        $error = 'Registration failed. Please try again.';
                    }
                }
                $stmt->close();
            }
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
    <title>Register - Tech Store</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .register-container {
            max-width: 500px;
            margin: 30px auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }

        .register-container h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #007bff;
        }

        .register-container .form-group {
            margin-bottom: 20px;
        }

        .register-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .register-container select,
        .register-container input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .register-container input:focus,
        .register-container select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }

        .register-container button {
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

        .register-container button:hover {
            background-color: #0056b3;
        }

        .register-container .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .register-container .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .register-container .success-message a {
            color: #155724;
            font-weight: bold;
        }

        .register-container .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        .register-container .login-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }

        .register-container .login-link a:hover {
            text-decoration: underline;
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

        .form-group.hidden {
            display: none;
        }

        .info-text {
            background-color: #e7f3ff;
            padding: 10px;
            border-radius: 4px;
            font-size: 13px;
            color: #004085;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h1>Tech Store Register</h1>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="tab-buttons">
            <button class="tab-btn active" onclick="switchTab('customer', this)">Customer</button>
            <button class="tab-btn" onclick="switchTab('producer', this)">Producer</button>
        </div>

        <form method="POST">
            <input type="hidden" name="user_type" id="user_type" value="customer">

            <div class="info-text" id="info-text">
                Create a customer account to start shopping and earn loyalty points!
            </div>

            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($fullName); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
            </div>

            <div class="form-group hidden" id="company_group">
                <label for="company_name">Company Name *</label>
                <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($companyName); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required>
                <small style="color: #666;">Minimum 6 characters</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

    <script>
        function switchTab(userType, btn) {
            document.getElementById('user_type').value = userType;
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const companyGroup = document.getElementById('company_group');
            const infoText = document.getElementById('info-text');

            if (userType === 'producer') {
                companyGroup.classList.remove('hidden');
                infoText.textContent = 'Create a producer account to manage your products and orders. Admin approval required.';
            } else {
                companyGroup.classList.add('hidden');
                infoText.textContent = 'Create a customer account to start shopping and earn loyalty points!';
            }

            document.getElementById('full_name').focus();
        }
    </script>
</body>
</html>
