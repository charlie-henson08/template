<?php
include '../db_config.php';
include '../auth/session.php';

requireAdmin();

$adminId = $_SESSION['admin_id'];

// Handle producer status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $producerId = intval($_POST['producer_id']);
        $newStatus = $_POST['status']; // active, inactive, suspended

        if (in_array($newStatus, ['active', 'inactive', 'suspended'])) {
            $stmt = $conn->prepare("UPDATE producers SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $newStatus, $producerId);

            if ($stmt->execute()) {
                $success = 'Producer status updated successfully!';
            } else {
                $error = 'Failed to update producer status';
            }
            $stmt->close();
        }
    }
}

// Fetch all producers
$result = $conn->query("SELECT id, full_name, email, company_name, status, created_at FROM producers ORDER BY created_at DESC");
$producers = [];
while ($row = $result->fetch_assoc()) {
    $producers[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Tech Store</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .dashboard-header {
            background-color: #dc3545;
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

        .dashboard-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .dashboard-card h2 {
            color: #dc3545;
            margin-top: 0;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .producers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .producer-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        .producer-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .producer-card h3 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .producer-card p {
            margin: 8px 0;
            font-size: 14px;
            color: #666;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-suspended {
            background-color: #f8d7da;
            color: #721c24;
        }

        .producer-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .action-btn {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .activate-btn {
            background-color: #28a745;
            color: white;
        }

        .activate-btn:hover {
            background-color: #218838;
        }

        .deactivate-btn {
            background-color: #ffc107;
            color: #333;
        }

        .deactivate-btn:hover {
            background-color: #e0a800;
        }

        .suspend-btn {
            background-color: #dc3545;
            color: white;
        }

        .suspend-btn:hover {
            background-color: #c82333;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-card .label {
            font-size: 14px;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .producers-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1>Admin Dashboard</h1>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">Manage producers and system access</p>
            </div>
            <a href="../auth/logout.php" class="logout-btn">Logout</a>
        </div>

        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="dashboard-card">
            <h2>Producer Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="number"><?php echo count($producers); ?></div>
                    <div class="label">Total Producers</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo count(array_filter($producers, fn($p) => $p['status'] === 'active')); ?></div>
                    <div class="label">Active Producers</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo count(array_filter($producers, fn($p) => $p['status'] === 'inactive')); ?></div>
                    <div class="label">Pending Approval</div>
                </div>
            </div>
        </div>

        <div class="dashboard-card">
            <h2>Producer Management</h2>

            <?php if (empty($producers)): ?>
                <div class="empty-state">
                    <p>No producers registered yet.</p>
                </div>
            <?php else: ?>
                <div class="producers-grid">
                    <?php foreach ($producers as $producer): ?>
                        <div class="producer-card">
                            <h3><?php echo htmlspecialchars($producer['company_name']); ?></h3>
                            <div class="status-badge status-<?php echo $producer['status']; ?>">
                                <?php echo ucfirst($producer['status']); ?>
                            </div>
                            <p><strong>Manager:</strong> <?php echo htmlspecialchars($producer['full_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($producer['email']); ?></p>
                            <p><strong>Registered:</strong> <?php echo date('M d, Y', strtotime($producer['created_at'])); ?></p>

                            <div class="producer-actions">
                                <form method="POST" style="flex: 1;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="producer_id" value="<?php echo $producer['id']; ?>">
                                    <input type="hidden" name="status" value="active">
                                    <?php if ($producer['status'] !== 'active'): ?>
                                        <button type="submit" class="action-btn activate-btn">Approve</button>
                                    <?php endif; ?>
                                </form>

                                <form method="POST" style="flex: 1;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="producer_id" value="<?php echo $producer['id']; ?>">
                                    <input type="hidden" name="status" value="inactive">
                                    <?php if ($producer['status'] !== 'inactive'): ?>
                                        <button type="submit" class="action-btn deactivate-btn">Deactivate</button>
                                    <?php endif; ?>
                                </form>

                                <form method="POST" style="flex: 1;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="producer_id" value="<?php echo $producer['id']; ?>">
                                    <input type="hidden" name="status" value="suspended">
                                    <?php if ($producer['status'] !== 'suspended'): ?>
                                        <button type="submit" class="action-btn suspend-btn">Suspend</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
