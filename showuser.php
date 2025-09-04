<?php
include 'config.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all customers
$result = $conn->query("SELECT customer_id, name, email, phone, address FROM Customers ORDER BY customer_id");

?>

<!DOCTYPE html>
<html>

<head>
    <title>All Registered Customers</title>
   <link rel="stylesheet" href="css/showuser.css">
</head>

<body>
    <div class="dashboard">
        <a href="dashboard.php">Back to Admin Dashboard</a>
    </div>

    <h2>All Registered Customers</h2>

    <table>
        <tr>
            <th>Customer ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['customer_id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['phone']; ?></td>
                <td><?php echo $row['address']; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>

</html>