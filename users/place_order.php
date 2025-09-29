<?php
session_start();
include '../config.php'; // Make sure this file sets $conn = new mysqli(...);

// Redirect if not logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];

$error = "";
$success = "";

// ----------------------------
// 1) Create or replace view
$create_view_sql = "
CREATE OR REPLACE VIEW AvailableProducts AS
SELECT product_id, name, price, stock
FROM Products
WHERE stock > 0
";
mysqli_query($conn, $create_view_sql);

// ----------------------------
// Handle form submission
if (isset($_POST['place_order'])) {
    $products_post = isset($_POST['products']) ? $_POST['products'] : array();
    $total_amount = 0;
    $order_items = array();

    // Calculate total and check stock
    foreach ($products_post as $pid => $qty) {
        $pid = (int) $pid;
        $qty = (int) $qty;
        if ($qty <= 0)
            continue;

        $sql = "SELECT price, stock, name FROM Products WHERE product_id = $pid";
        $res = mysqli_query($conn, $sql);
        if (!$res || mysqli_num_rows($res) == 0) {
            $error = "Product ID $pid not found.";
            break;
        }

        $row = mysqli_fetch_assoc($res);
        $price = $row['price'];
        $stock = $row['stock'];
        $pname = $row['name'];

        if ($qty > $stock) {
            $error = "Not enough stock for product \"$pname\" (ID $pid). Requested $qty, available $stock.";
            break;
        }

        $total_amount += $price * $qty;
        $order_items[] = array('product_id' => $pid, 'quantity' => $qty, 'price' => $price);
    }

    // Insert into Orders
    if ($error == "" && count($order_items) > 0) {
        $sql_order = "INSERT INTO Orders (customer_id, total_amount) VALUES ($customer_id, $total_amount)";
        if (mysqli_query($conn, $sql_order)) {
            $order_id = mysqli_insert_id($conn);

            // Insert into Order_Items and update stock
            foreach ($order_items as $item) {
                $pid = $item['product_id'];
                $qty = $item['quantity'];
                $price = $item['price'];

                $sql_item = "INSERT INTO Order_Items (order_id, product_id, quantity, price)
                             VALUES ($order_id, $pid, $qty, $price)";
                mysqli_query($conn, $sql_item);

                // Update stock
                $sql_update = "UPDATE Products SET stock = stock - $qty WHERE product_id = $pid";
                mysqli_query($conn, $sql_update);
            }

            $success = "Order placed successfully! Your Order ID is $order_id.";
        } else {
            $error = "Error creating order: " . mysqli_error($conn);
        }
    }
}

// Fetch products for display using the view
$sql_products = "SELECT * FROM AvailableProducts";
$result = mysqli_query($conn, $sql_products);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Place Order</title>
</head>

<body>
    <h2>Welcome, <?php echo $customer_name; ?>! Place your order below.</h2>

    <?php if ($error != "")
        echo "<p style='color:red;'>$error</p>"; ?>
    <?php if ($success != "")
        echo "<p style='color:green;'>$success</p>"; ?>

    <form method="post">
        <table border="1" cellpadding="5">
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Quantity</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['price']; ?></td>
                    <td><?php echo $row['stock']; ?></td>
                    <td><input type="number" name="products[<?php echo $row['product_id']; ?>]" min="0"
                            max="<?php echo $row['stock']; ?>" value="0"></td>
                </tr>
            <?php endwhile; ?>
        </table>
        <br>
        <button type="submit" name="place_order">Place Order</button>
    </form>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>

</html>