<?php
session_start();
include './config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$threshold = 400;

// Subquery + HAVING: calculate total spent per customer, filter with HAVING
$sql = "
SELECT 
    c.customer_id,
    c.name AS customer_name,
    c.email,
    totals.total_spent
FROM Customers c
JOIN (
    SELECT 
        o.customer_id,
        SUM(oi.quantity * oi.price) AS total_spent
    FROM Orders o
    JOIN Order_Items oi ON o.order_id = oi.order_id
    WHERE o.payment_status = 'Done'
    GROUP BY o.customer_id
    HAVING total_spent > $threshold
) AS totals ON c.customer_id = totals.customer_id
ORDER BY totals.total_spent DESC
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
    <title>Top Customers</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
table {
    width: 100%;
    border-collapse: collapse; /* Merge borders */
    font-family: Arial, sans-serif;
}

table th, table td {
    border: 1px solid #333; /* Add borders */
    padding: 8px 12px;
    text-align: left;
}

table th {
    background-color: #f2f2f2; /* Header background */
    font-weight: bold;
}

table tr:nth-child(even) {
    background-color: #fafafa; /* Alternating row color */
}

table tr:hover {
    background-color: #f1f1f1; /* Row hover effect */
}
</style>

</head>

<body>
    <div class="header">
        <h1>Top Customers (Spent more than 400)</h1>
        <div class="user-info">
            <a href="dashboard.php" class="logout-btn">Back to Dashboard</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($error != ""): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <h2>Customers with Total Spending above <?php echo $threshold; ?></h2>
        <table>
            <tr>
                <th>Customer ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Total Spent</th>
            </tr>
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['customer_id']; ?></td>
                        <td><?php echo $row['customer_name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo number_format($row['total_spent'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No customers found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>

</html>