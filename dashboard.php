<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
if (isset($_SESSION['message'])) {
    echo "<p style='color:green;'>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Shopping Management System</title>
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
            padding: 25px 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header h1 {
            color: #667eea;
            font-size: 32px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .header-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .header-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px 20px;
            background: #f7fafc;
            border-radius: 8px;
        }

        .user-info span {
            color: #2d3748;
            font-weight: 600;
        }

        .logout-btn {
            padding: 8px 18px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 30px;
        }

        .welcome-card h2 {
            color: #2d3748;
            font-size: 36px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .welcome-card p {
            color: #718096;
            font-size: 18px;
            line-height: 1.6;
        }

        .navbar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .nav-item {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .nav-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .nav-item a {
            text-decoration: none;
            display: block;
            padding: 30px 25px;
            position: relative;
        }

        .nav-item button {
            width: 100%;
            padding: 0;
            border: none;
            background: none;
            cursor: pointer;
            text-align: left;
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s ease;
        }

        .nav-item .icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .nav-item:nth-child(1) .icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .nav-item:nth-child(2) .icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .nav-item:nth-child(3) .icon {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .nav-item:nth-child(4) .icon {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .nav-item:nth-child(5) .icon {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .nav-item:nth-child(6) .icon {
            background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
        }

        .nav-item:nth-child(7) .icon {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        }

        .nav-item:nth-child(8) .icon {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
        }

        .nav-item:nth-child(9) .icon {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        }

        .nav-description {
            font-size: 13px;
            color: #718096;
            margin-top: 8px;
            line-height: 1.4;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                padding: 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .header-actions {
                width: 100%;
                justify-content: center;
            }

            .welcome-card {
                padding: 30px 20px;
            }

            .welcome-card h2 {
                font-size: 28px;
            }

            .navbar {
                grid-template-columns: 1fr;
            }

            .nav-item button {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>🛒 Shopping Management System</h1>
        <div class="header-actions">
            <a href="showuser.php" class="header-btn">👥 All Customers</a>
            <a href="RawQuery.php" class="header-btn">🔍 Query Console</a>
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($_SESSION['admin_email']); ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="welcome-card">
            <h2>Welcome to Admin Dashboard 🎯</h2>
            <p>You are successfully logged in as an administrator. Manage your store efficiently with comprehensive tools below.</p>
        </div>

        <div class="navbar">
            <div class="nav-item">
                <a href="products.php">
                    <button type="button">
                        <span class="icon">📦</span>
                        <div>
                            <div>Manage Products</div>
                            <div class="nav-description">Add, update, and manage product inventory</div>
                        </div>
                    </button>
                </a>
            </div>
            <div class="nav-item">
                <a href="product_insights.php">
                    <button type="button">
                        <span class="icon">📊</span>
                        <div>
                            <div>Product Insights</div>
                            <div class="nav-description">View detailed product performance metrics</div>
                        </div>
                    </button>
                </a>
            </div>
            <div class="nav-item">
                <a href="suppliers.php">
                    <button type="button">
                        <span class="icon">🏭</span>
                        <div>
                            <div>Manage Suppliers</div>
                            <div class="nav-description">Track and manage supplier information</div>
                        </div>
                    </button>
                </a>
            </div>
            <div class="nav-item">
                <a href="customer_supplier_insights.php">
                    <button type="button">
                        <span class="icon">🏭</span>
                        <div>
                            <div>Customer-Supplier Insights</div>
                            <div class="nav-description">Analyze customers who are also suppliers</div>
                        </div>
                    </button>
                </a>
            </div>
            <div class="nav-item">
                <a href="categories.php">
                    <button type="button">
                        <span class="icon">📂</span>
                        <div>
                            <div>Manage Categories</div>
                            <div class="nav-description">Organize products into categories</div>
                        </div>
                    </button>
                </a>
            </div>
            <div class="nav-item">
                <a href="all_orders.php">
                    <button type="button">
                        <span class="icon">🛍️</span>
                        <div>
                            <div>All Orders</div>
                            <div class="nav-description">View and manage customer orders</div>
                        </div>
                    </button>
                </a>
            </div>
            <div class="nav-item">
                <a href="all_reviews.php">
                    <button type="button">
                        <span class="icon">⭐</span>
                        <div>
                            <div>Customer Reviews</div>
                            <div class="nav-description">Monitor product reviews and ratings</div>
                        </div>
                    </button>
                </a>
            </div>
            <div class="nav-item">
                <a href="top_customer.php">
                    <button type="button">
                        <span class="icon">🏆</span>
                        <div>
                            <div>Top Customers</div>
                            <div class="nav-description">View your most valuable customers</div>
                        </div>
                    </button>
                </a>
            </div>
            <div class="nav-item">
                <a href="top_order.php">
                    <button type="button">
                        <span class="icon">💎</span>
                        <div>
                            <div>Top Orders</div>
                            <div class="nav-description">Analyze highest value orders</div>
                        </div>
                    </button>
                </a>
            </div>
            <div class="nav-item">
                <a href="customer_loyalty.php">
                    <button type="button">
                        <span class="icon">🎖️</span>
                        <div>
                            <div>Customer Loyalty</div>
                            <div class="nav-description">Track customer loyalty tiers</div>
                        </div>
                    </button>
                </a>
            </div>
            <div class="nav-item">
                <a href="admin_aggregates.php">
                    <button type="button">
                        <span class="icon">📊</span>
                        <div>
                            <div>Aggregates & Statistics</div>
                            <div class="nav-description">View comprehensive analytics</div>
                        </div>
                    </button>
                </a>
            </div>
        </div>
    </div>
</body>

</html>