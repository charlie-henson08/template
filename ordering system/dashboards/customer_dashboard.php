<?php
include '../db_config.php';
include '../auth/session.php';

requireCustomer();

$userId = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT full_name, email, loyalty_points FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch loyalty transactions
$stmt = $conn->prepare("SELECT points, description, created_at FROM loyalty_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();

// Calculate loyalty progress (assuming 1000 points = 1 free item)
$pointsForReward = 1000;
$loyaltyPercentage = ($user['loyalty_points'] % $pointsForReward) / $pointsForReward * 100;
$rewardsEarned = intval($user['loyalty_points'] / $pointsForReward);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Tech Store</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .dashboard-header {
            background-color: #007bff;
            color: white;
            padding: 30px 20px;
            margin-bottom: 40px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-header h1 {
            margin: 0;
            font-size: 28px;
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

        .dashboard-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .dashboard-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }

        .dashboard-card h2 {
            margin-top: 0;
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .user-info p {
            margin: 10px 0;
            font-size: 16px;
        }

        .user-info strong {
            display: inline-block;
            width: 120px;
            color: #666;
        }

        .loyalty-section {
            grid-column: 1 / -1;
        }

        .loyalty-points-display {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .points-card {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .points-card .big-number {
            font-size: 40px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .points-card .label {
            font-size: 14px;
            opacity: 0.9;
        }

        .loyalty-bar-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .loyalty-bar-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: 500;
        }

        .loyalty-bar {
            background-color: #e9ecef;
            border-radius: 10px;
            height: 30px;
            overflow: hidden;
        }

        .loyalty-bar-fill {
            background: linear-gradient(90deg, #28a745, #20c997);
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
        }

        .loyalty-info {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            font-size: 14px;
            color: #004085;
            margin-bottom: 20px;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
        }

        .transactions-table th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        .transactions-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }

        .transactions-table tr:hover {
            background-color: #f8f9fa;
        }

        .points-positive {
            color: #28a745;
            font-weight: bold;
        }

        .points-negative {
            color: #dc3545;
            font-weight: bold;
        }

        .empty-transactions {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .nav-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .nav-btn {
            flex: 1;
            padding: 12px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .nav-btn:hover {
            background-color: #0056b3;
        }

        @media (max-width: 768px) {
            .dashboard-content {
                grid-template-columns: 1fr;
            }

            .loyalty-points-display {
                grid-template-columns: 1fr;
            }

            .dashboard-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">Customer Dashboard</p>
            </div>
            <a href="../auth/logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="dashboard-content">
            <!-- User Profile Card -->
            <div class="dashboard-card user-info">
                <h2>Profile Information</h2>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Total Points:</strong> <strong style="color: #007bff;"><?php echo number_format($user['loyalty_points']); ?></strong></p>
            </div>

            <!-- Quick Links Card -->
            <div class="dashboard-card">
                <h2>Quick Links</h2>
                <div class="nav-buttons">
                    <a href="../index.php" class="nav-btn">Browse Products</a>
                    <a href="../cart.php" class="nav-btn">View Cart</a>
                </div>
            </div>

            <!-- Loyalty Rewards Section -->
            <div class="dashboard-card loyalty-section">
                <h2>Loyalty Rewards</h2>

                <div class="loyalty-points-display">
                    <div class="points-card">
                        <div class="big-number"><?php echo number_format($user['loyalty_points']); ?></div>
                        <div class="label">Total Points</div>
                    </div>
                    <div class="points-card">
                        <div class="big-number"><?php echo $rewardsEarned; ?></div>
                        <div class="label">Rewards Earned</div>
                    </div>
                    <div class="points-card">
                        <div class="big-number"><?php echo number_format($pointsForReward - ($user['loyalty_points'] % $pointsForReward)); ?></div>
                        <div class="label">Points to Next Reward</div>
                    </div>
                </div>

                <div class="loyalty-bar-container">
                    <div class="loyalty-bar-label">
                        <span>Progress to Next Reward</span>
                        <span><?php echo number_format($user['loyalty_points'] % $pointsForReward); ?> / <?php echo $pointsForReward; ?> points</span>
                    </div>
                    <div class="loyalty-bar">
                        <div class="loyalty-bar-fill" style="width: <?php echo $loyaltyPercentage; ?>%">
                            <?php if ($loyaltyPercentage > 20): ?>
                                <?php echo round($loyaltyPercentage); ?>%
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="loyalty-info">
                    <strong>How it works:</strong> Earn 1 point per dollar spent. Collect 1000 points to earn a reward!
                </div>

                <h3 style="margin-top: 30px; color: #333;">Recent Transactions</h3>

                <?php if (empty($transactions)): ?>
                    <div class="empty-transactions">
                        <p>No transactions yet. Start shopping to earn loyalty points!</p>
                    </div>
                <?php else: ?>
                    <table class="transactions-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Points</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                    <td class="<?php echo $transaction['points'] > 0 ? 'points-positive' : 'points-negative'; ?>">
                                        <?php echo ($transaction['points'] > 0 ? '+' : '') . $transaction['points']; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($transaction['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
