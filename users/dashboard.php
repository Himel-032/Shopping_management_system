<?php
session_start();
include '../config.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];

// Fetch customer details
$sql = "SELECT name, email, phone, address FROM Customers WHERE customer_id = $customer_id";
$res = mysqli_query($conn, $sql);
$customer = mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($customer_name); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h1 {
            color: #667eea;
            font-size: 28px;
            font-weight: 700;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header .user-badge {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            font-weight: bold;
        }

        .header .user-info {
            display: flex;
            flex-direction: column;
        }

        .header .user-info .name {
            font-weight: 600;
            color: #2d3748;
            font-size: 16px;
        }

        .header .user-info .role {
            font-size: 13px;
            color: #718096;
        }

        .logout-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #ff6b6b 0%, #c92a2a 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .welcome-section {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            text-align: center;
        }

        .welcome-section h2 {
            color: #2d3748;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .welcome-section p {
            color: #718096;
            font-size: 16px;
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .profile-card h3 {
            color: #2d3748;
            font-size: 22px;
            margin-bottom: 25px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .field {
            background: #f7fafc;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .field label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .field span {
            color: #2d3748;
            font-size: 16px;
            display: block;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 10px;
        }

        .action-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .action-card .icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .action-card.edit .icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .action-card.order .icon {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .action-card.view .icon {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .action-card.review .icon {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .action-card h4 {
            color: #2d3748;
            font-size: 18px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .action-card p {
            color: #718096;
            font-size: 14px;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }

            .header h1 {
                font-size: 24px;
            }

            .welcome-section h2 {
                font-size: 24px;
            }

            .profile-grid,
            .actions-grid {
                grid-template-columns: 1fr;
            }

            .profile-card,
            .welcome-section {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h1>🛒 Shopping Management System</h1>
        <div class="header-right">
            <div class="user-badge">
                <div class="avatar">
                    <?php echo strtoupper(substr($customer_name, 0, 1)); ?>
                </div>
                <div class="user-info">
                    <span class="name"><?php echo htmlspecialchars($customer_name); ?></span>
                    <span class="role">Customer Account</span>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h2>Welcome Back, <?php echo htmlspecialchars($customer_name); ?>! 👋</h2>
            <p>Manage your profile, place orders, and track your shopping experience</p>
        </div>

        <!-- Profile Information Card -->
        <div class="profile-card">
            <h3>📋 Your Profile Information</h3>
            <div class="profile-grid">
                <div class="field">
                    <label>Full Name</label>
                    <span><?php echo htmlspecialchars($customer['name']); ?></span>
                </div>
                <div class="field">
                    <label>Email Address</label>
                    <span><?php echo htmlspecialchars($customer['email']); ?></span>
                </div>
                <div class="field">
                    <label>Phone Number</label>
                    <span><?php echo htmlspecialchars($customer['phone'] ?: 'Not provided'); ?></span>
                </div>
                <div class="field">
                    <label>Delivery Address</label>
                    <span><?php echo htmlspecialchars($customer['address'] ?: 'Not provided'); ?></span>
                </div>
            </div>
        </div>

        <!-- Quick Actions Grid -->
        <div class="actions-grid">
            <a href="editProfile.php" class="action-card edit">
                <div class="icon">✏️</div>
                <h4>Edit Profile</h4>
                <p>Update your personal information and contact details</p>
            </a>

            <a href="create_order.php" class="action-card order">
                <div class="icon">🛍️</div>
                <h4>Place New Order</h4>
                <p>Browse products and create a new order</p>
            </a>

            <a href="my_orders.php" class="action-card view">
                <div class="icon">📦</div>
                <h4>View Your Orders</h4>
                <p>Track your order history and status</p>
            </a>

            <a href="make_review.php" class="action-card review">
                <div class="icon">⭐</div>
                <h4>Write Reviews</h4>
                <p>Share your experience with purchased products</p>
            </a>
        </div>
    </div>

</body>

</html>
