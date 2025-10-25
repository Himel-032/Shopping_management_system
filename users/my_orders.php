<?php
session_start();
include '../config.php'; // Make sure $conn is defined here

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = (int) $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];

$error = "";
$success = "";

// --------------------------
// Handle Payment
if (isset($_POST['pay_order'])) {
    $order_id = (int) $_POST['order_id'];
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

    // Get total amount for the order
    $sql_total = "SELECT SUM(quantity * price) AS total_amount 
                  FROM Order_Items 
                  WHERE order_id=$order_id";
    $res_total = mysqli_query($conn, $sql_total);
    $row_total = mysqli_fetch_assoc($res_total);
    $amount = $row_total['total_amount'];

    // Update Orders table
    $sql_update = "UPDATE Orders 
                   SET payment_status='Done' 
                   WHERE order_id=$order_id AND customer_id=$customer_id";
    if (mysqli_query($conn, $sql_update)) {
        // Insert into Payments table
        $sql_insert = "INSERT INTO Payments (order_id, payment_method, amount) 
                       VALUES ($order_id, '$payment_method', $amount)";
        if (mysqli_query($conn, $sql_insert)) {
            $success = "Payment for Order #$order_id completed!";
        } else {
            $error = "Payment insert failed: " . mysqli_error($conn);
        }
    } else {
        $error = "Payment update failed: " . mysqli_error($conn);
    }
}

// --------------------------
// Handle Delete Unpaid Order
if (isset($_POST['delete_order'])) {
    $order_id = (int) $_POST['order_id'];

    // Restore stock for each item
    $sql_items = "SELECT product_id, quantity FROM Order_Items WHERE order_id=$order_id";
    $res_items = mysqli_query($conn, $sql_items);
    while ($item = mysqli_fetch_assoc($res_items)) {
        $sql_stock = "UPDATE Products 
                      SET stock = stock + {$item['quantity']} 
                      WHERE product_id={$item['product_id']}";
        mysqli_query($conn, $sql_stock);
    }

    // Delete order items
    $sql_delete_items = "DELETE FROM Order_Items WHERE order_id=$order_id";
    mysqli_query($conn, $sql_delete_items);

    // Delete order if unpaid
    $sql_delete_order = "DELETE FROM Orders 
                         WHERE order_id=$order_id AND payment_status != 'Done'";
    if (mysqli_query($conn, $sql_delete_order)) {
        $success = "Order #$order_id deleted and stock restored!";
    } else {
        $error = "Failed to delete order: " . mysqli_error($conn);
    }
}

// --------------------------
// Fetch Orders
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 20px;
            color: #333;
        }

        h2 {
            color: #2c3e50;
        }

        a {
            text-decoration: none;
            color: #3498db;
            margin-bottom: 15px;
            display: inline-block;
        }

        a:hover {
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            margin-top: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        button {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        button.pay {
            background-color: #2ecc71;
            color: white;
        }

        button.pay:hover {
            background-color: #27ae60;
        }

        button.delete {
            background-color: #e74c3c;
            color: white;
        }

        button.delete:hover {
            background-color: #c0392b;
        }

        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 350px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .modal-content h3 {
            margin-top: 0;
            color: #2c3e50;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        .modal select,
        .modal input[type="text"],
        .modal input[type="number"] {
            padding: 6px;
            width: 100%;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .modal button {
            width: 100%;
            margin-top: 10px;
        }

        p.error {
            color: red;
        }

        p.success {
            color: green;
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
                        <button class="pay" onclick="openModal(<?php echo $row['order_id']; ?>)">Pay</button>
                        <form method="post" style="display:inline-block;">
                            <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                            <button type="submit" name="delete_order" class="delete">Delete</button>
                        </form>
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
                    <option value="Bank">Bank</option>
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