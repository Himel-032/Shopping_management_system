<?php
session_start();
include '../config.php'; // Make sure $conn = new mysqli(...)

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
// Create or replace view for available products
$create_view_sql = "
CREATE OR REPLACE VIEW AvailableProducts AS
SELECT product_id, name, price, stock
FROM Products
WHERE stock > 0
";
mysqli_query($conn, $create_view_sql);

// ----------------------------
// Handle form submission (one product at a time)
if (isset($_POST['place_order'])) {
    $product_id = (int) $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];

    if ($quantity <= 0) {
        $error = "Quantity must be at least 1.";
    } else {
        // Fetch product details
        $sql = "SELECT name, price, stock FROM Products WHERE product_id = $product_id";
        $res = mysqli_query($conn, $sql);
        if (!$res || mysqli_num_rows($res) == 0) {
            $error = "Product not found.";
        } else {
            $row = mysqli_fetch_assoc($res);
            $price = $row['price'];
            $stock = $row['stock'];
            $pname = $row['name'];

            if ($quantity > $stock) {
                $error = "Not enough stock for \"$pname\". Available: $stock.";
            } else {
                $total_amount = $price * $quantity;

                // Insert order
                $sql_order = "INSERT INTO Orders (customer_id, total_amount) VALUES ($customer_id, $total_amount)";
                if (mysqli_query($conn, $sql_order)) {
                    $order_id = mysqli_insert_id($conn);

                    // Insert order item
                    $sql_item = "INSERT INTO Order_Items (order_id, product_id, quantity, price) 
                                 VALUES ($order_id, $product_id, $quantity, $price)";
                    mysqli_query($conn, $sql_item);

                    // Update stock
                    $sql_update = "UPDATE Products SET stock = stock - $quantity WHERE product_id = $product_id";
                    mysqli_query($conn, $sql_update);

                    $success = "Order placed successfully! Order ID: $order_id for product \"$pname\".";
                } else {
                    $error = "Error creating order: " . mysqli_error($conn);
                }
            }
        }
    }
}

// Fetch products from view
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

    <table border="1" cellpadding="5">
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Quantity</th>
            <th>Action</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <form method="post">
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['price']; ?></td>
                    <td><?php echo $row['stock']; ?></td>
                    <td>
                        <input type="number" name="quantity" min="1" max="<?php echo $row['stock']; ?>" value="1">
                    </td>
                    <td>
                        <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                        <button type="submit" name="place_order">Order</button>
                    </td>
                </form>
            </tr>
        <?php endwhile; ?>
    </table>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>

</html>