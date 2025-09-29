<?php
session_start();
include './config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// Fetch all reviews with product and customer info
$sql = "
SELECT 
    r.review_id,
    r.rating,
    r.comment,
    r.customer_id,
    c.name AS customer_name,
    r.product_id,
    p.name AS product_name
FROM Reviews r
INNER JOIN Customers c ON r.customer_id = c.customer_id
INNER JOIN Products p ON r.product_id = p.product_id
ORDER BY r.review_id DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Customer Reviews - Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h2 {
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #0097e6;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>

<body>

    <h2>Hello <?php echo htmlspecialchars($admin_name); ?>, Customer Reviews</h2>
    <p><a href="dashboard.php">Back to Dashboard</a></p>

    <table>
        <tr>
            <th>Review ID</th>
            <th>Customer ID</th>
            <th>Customer Name</th>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Rating</th>
            <th>Comment</th>
        </tr>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['review_id']; ?></td>
                    <td><?php echo $row['customer_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo $row['product_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo $row['rating']; ?></td>
                    <td><?php echo htmlspecialchars($row['comment']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align:center;">No reviews found.</td>
            </tr>
        <?php endif; ?>
    </table>

</body>

</html>