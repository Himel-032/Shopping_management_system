<?php
session_start();
include './config.php';

// Only admin can access
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";

// Query: total spent + MOD-based loyalty tiers
$sql = "
SELECT 
    c.customer_id,
    c.name AS customer_name,
    c.email,
    COALESCE(SUM(oi.quantity * oi.price),0) AS total_spent,
    MOD(c.customer_id, 3) AS loyalty_tier_num,
    CASE MOD(c.customer_id, 3)
        WHEN 0 THEN 'Gold'
        WHEN 1 THEN 'Silver'
        ELSE 'Bronze'
    END AS loyalty_tier
FROM Customers c
LEFT JOIN Orders o ON c.customer_id = o.customer_id AND o.payment_status='Done'
LEFT JOIN Order_Items oi ON o.order_id = oi.order_id
GROUP BY c.customer_id, c.name, c.email
ORDER BY total_spent DESC
";

$result = mysqli_query($conn, $sql);
if (!$result) {
    $error = "Database error: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Customer Loyalty Tiers</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        table {
            border-collapse: collapse;
            width: 80%;
            margin: 20px auto;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #e8f0fe;
        }

        .explanation {
            width: 80%;
            margin: 20px auto;
            padding: 10px;
            font-style: italic;
            color: #555;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Customer Loyalty Tiers</h1>
        <div class="user-info">
            <a href="dashboard.php" class="logout-btn">Back to Dashboard</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($error != ""): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <div class="explanation">
            Loyalty tiers are assigned using the MOD function on customer IDs.
            <br>
            <strong>Tier Mapping:</strong> 0 → Gold, 1 → Silver, 2 → Bronze.
            <br>
            Total spent is calculated from completed (paid) orders.
        </div>

        <table>
            <tr>
                <th>Customer ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Total Spent</th>
                <th>Tier Number</th>
                <th>Tier Name</th>
            </tr>

            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['customer_id']; ?></td>
                        <td><?php echo $row['customer_name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo number_format($row['total_spent'], 2); ?></td>
                        <td><?php echo $row['loyalty_tier_num']; ?></td>
                        <td><?php echo $row['loyalty_tier']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No customers found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>

</html>