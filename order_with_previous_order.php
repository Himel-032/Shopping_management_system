<?php
session_start();
include './config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";

// ------------------ Orders with Previous Order (SELF JOIN) ------------------
$sql = "
SELECT 
    o1.order_id AS current_order_id,
    o1.order_date AS current_order_date,
    o1.payment_status AS status,
    c.name AS customer_name,
    c.email AS customer_email,
    c.phone AS customer_phone,
    o2.order_id AS previous_order_id,
    o2.order_date AS previous_order_date
FROM Orders o1
LEFT JOIN Orders o2
    ON o1.customer_id = o2.customer_id
   AND o2.order_date < o1.order_date
JOIN Customers c ON o1.customer_id = c.customer_id
ORDER BY o1.customer_id, o1.order_date;
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
    <title>Orders with Previous Orders - Admin</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Orders with Previous Orders</h1>
        <div class="user-info">
            <a href="dashboard.php" class="logout-btn">Back to Dashboard</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($error != ""): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <table>
            <tr>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Current Order ID</th>
                <th>Current Order Date</th>
                <th>Payment Status</th>
                <th>Previous Order ID</th>
                <th>Previous Order Date</th>
            </tr>

            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['customer_name']; ?></td>
                        <td><?php echo $row['customer_email']; ?></td>
                        <td><?php echo $row['current_order_id']; ?></td>
                        <td><?php echo $row['current_order_date']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td><?php echo $row['previous_order_id'] ?? 'N/A'; ?></td>
                        <td><?php echo $row['previous_order_date'] ?? 'N/A'; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No orders found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>

</html>