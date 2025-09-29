<?php
session_start();
include '../config.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];

$error = "";
$success = "";

// Handle payment submission
if (isset($_POST['pay_order'])) {
    $order_id = (int) $_POST['order_id'];
    $payment_method = $_POST['payment_method'];

    // Get total amount for the order
    $sql_total = "SELECT SUM(quantity * price) AS total_amount FROM Order_Items WHERE order_id=?";
    $stmt_total = mysqli_prepare($conn, $sql_total);
    mysqli_stmt_bind_param($stmt_total, "i", $order_id);
    mysqli_stmt_execute($stmt_total);
    $res_total = mysqli_stmt_get_result($stmt_total);
    $row_total = mysqli_fetch_assoc($res_total);
    $amount = $row_total['total_amount'];

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // 1. Update Orders table
        $sql_update = "UPDATE Orders SET payment_status='Done' WHERE order_id=? AND customer_id=?";
        $stmt_update = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "ii", $order_id, $customer_id);
        if (!mysqli_stmt_execute($stmt_update)) {
            throw new Exception(mysqli_stmt_error($stmt_update));
        }

        // 2. Insert into Payments table
        $sql_insert = "INSERT INTO Payments (order_id, payment_method, amount) VALUES (?, ?, ?)";
        $stmt_insert = mysqli_prepare($conn, $sql_insert);
        mysqli_stmt_bind_param($stmt_insert, "isd", $order_id, $payment_method, $amount);
        if (!mysqli_stmt_execute($stmt_insert)) {
            throw new Exception(mysqli_stmt_error($stmt_insert));
        }

        mysqli_commit($conn);
        $success = "Payment for Order #$order_id completed!";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Payment failed: " . $e->getMessage();
    }
}

// Fetch orders with total per product
$sql = "
WITH OrderDetails AS (
    SELECT 
        o.order_id,
        o.customer_id,
        o.order_date,
        o.payment_status,
        oi.product_id,
        oi.quantity,
        oi.price,
        (oi.quantity * oi.price) AS total_price
    FROM Orders o
    JOIN Order_Items oi ON o.order_id = oi.order_id
)
SELECT 
    od.order_id,
    od.customer_id,
    od.order_date,
    od.payment_status,
    od.product_id,
    p.name AS product_name,
    od.quantity,
    od.price,
    od.total_price
FROM OrderDetails od
JOIN Products p ON od.product_id = p.product_id
WHERE od.customer_id = $customer_id
ORDER BY od.order_date DESC, od.order_id DESC
";

$result = mysqli_query($conn, $sql);
if (!$result)
    die("Query failed: " . mysqli_error($conn));

$last_order_id = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <link rel="stylesheet" href="./css/my_orders.css">
    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            padding-top: 100px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 400px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <h2>Hi <?php echo $customer_name; ?>, Your Orders</h2>
    <p><a href="dashboard.php">Back to Dashboard</a></p>

    <?php if ($error)
        echo "<p style='color:red;'>$error</p>"; ?>
    <?php if ($success)
        echo "<p style='color:green;'>$success</p>"; ?>

    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Order ID</th>
            <th>Order Date</th>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Price per Product</th>
            <th>Total Price</th>
            <th>Payment Status</th>
            <th>Action</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <?php if ($row['order_id'] != $last_order_id): ?>
                    <td><?php echo $row['order_id']; ?></td>
                    <td><?php echo $row['order_date']; ?></td>
                    <?php $last_order_id = $row['order_id']; ?>
                <?php else: ?>
                    <td></td>
                    <td></td>
                <?php endif; ?>

                <td><?php echo $row['product_id']; ?></td>
                <td><?php echo $row['product_name']; ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td><?php echo $row['price']; ?></td>
                <td><?php echo $row['total_price']; ?></td>
                <td><?php echo $row['payment_status']; ?></td>

                <td>
                    <?php if ($row['order_id'] == $last_order_id && $row['payment_status'] != 'Done'): ?>
                        <button onclick="openModal(<?php echo $row['order_id']; ?>)">Pay</button>
                    <?php else: ?>
                        <span>Paid</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Make Payment</h3>
            <form method="post">
                <input type="hidden" name="order_id" id="modal_order_id">
                <label>Payment Method:</label>
                <select name="payment_method" required>
                    <option value="Cash">Cash</option>
                    <option value="Card">Card</option>
                    <option value="Bkash">Bkash</option>
                    <option value="Rocket">Rocket</option>
                </select>
                <br><br>
                <button type="submit" name="pay_order">Pay</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(orderId) {
            document.getElementById('modal_order_id').value = orderId;
            document.getElementById('paymentModal').style.display = 'block';
        }
        function closeModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }
        window.onclick = function (event) {
            if (event.target == document.getElementById('paymentModal')) {
                closeModal();
            }
        }
    </script>

</body>

</html>