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

// Handle review submission
if (isset($_POST['submit_review'])) {
    $product_id = (int) $_POST['product_id'];
    $rating = (int) $_POST['rating'];
    $comment = trim($_POST['comment']);

    $sql = "INSERT INTO Reviews (customer_id, product_id, rating, comment) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiis", $customer_id, $product_id, $rating, $comment);

    if (mysqli_stmt_execute($stmt)) {
        $success = "Your review has been submitted!";
    } else {
        $error = "Failed to submit review: " . mysqli_stmt_error($stmt);
    }
}

// ------------------------
// Top table: all purchased products (payment done)
$sql_purchased = "
(
    SELECT p.product_id, p.name AS product_name, p.price, p.stock,
           o.order_id, o.order_date
    FROM Orders o
    INNER JOIN Order_Items oi ON o.order_id = oi.order_id
    INNER JOIN Products p ON oi.product_id = p.product_id
    WHERE o.customer_id = ? AND o.payment_status = 'Done'
)
ORDER BY order_date DESC, product_name
";
$stmt_purchased = mysqli_prepare($conn, $sql_purchased);
mysqli_stmt_bind_param($stmt_purchased, "i", $customer_id);
mysqli_stmt_execute($stmt_purchased);
$result_purchased = mysqli_stmt_get_result($stmt_purchased);

// ------------------------
// Bottom table: all submitted reviews
$sql_reviews = "
SELECT r.review_id, r.product_id, p.name AS product_name, r.rating, r.comment
FROM Reviews r
INNER JOIN Products p ON r.product_id = p.product_id
WHERE r.customer_id = ?
ORDER BY r.review_id DESC
";
$stmt_reviews = mysqli_prepare($conn, $sql_reviews);
mysqli_stmt_bind_param($stmt_reviews, "i", $customer_id);
mysqli_stmt_execute($stmt_reviews);
$result_reviews = mysqli_stmt_get_result($stmt_reviews);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Purchased Products & Reviews - <?php echo htmlspecialchars($customer_name); ?></title>
    <style>
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
            background: #0097e6;
            color: #fff;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        button {
            padding: 6px 12px;
            cursor: pointer;
            border-radius: 5px;
            border: none;
            background: #2ecc71;
            color: white;
        }

        /* Modal */
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
            width: 450px;
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
    <h2>Hello <?php echo htmlspecialchars($customer_name); ?>, Your Purchased Products</h2>
    <p><a href="dashboard.php">Back to Dashboard</a></p>

    <?php if ($error)
        echo "<p style='color:red;'>$error</p>"; ?>
    <?php if ($success)
        echo "<p style='color:green;'>$success</p>"; ?>

    <!-- Top Table: Purchased Products -->
    <table>
        <tr>
            <th>Order ID</th>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Action</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result_purchased)): ?>
            <tr>
                <td><?php echo $row['order_id']; ?></td>
                <td><?php echo $row['product_id']; ?></td>
                <td><?php echo $row['product_name']; ?></td>
                <td><?php echo $row['price']; ?></td>
                <td><?php echo $row['stock']; ?></td>
                <td><button onclick="openModal(<?php echo $row['product_id']; ?>)">Review</button></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- Bottom Table: Submitted Reviews -->
    <h2>Your Submitted Reviews</h2>
    <table>
        <tr>
            <th>Review ID</th>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Rating</th>
            <th>Comment</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result_reviews)): ?>
            <tr>
                <td><?php echo $row['review_id']; ?></td>
                <td><?php echo $row['product_id']; ?></td>
                <td><?php echo $row['product_name']; ?></td>
                <td><?php echo $row['rating']; ?></td>
                <td><?php echo $row['comment']; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- Review Modal -->
    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Write Review</h3>
            <form method="post">
                <input type="hidden" name="product_id" id="modal_product_id">
                <label>Rating (1-5):</label>
                <input type="number" name="rating" min="1" max="5" required>
                <br><br>
                <label>Comment:</label>
                <textarea name="comment" rows="4" cols="40" placeholder="Write your review..." required></textarea>
                <br><br>
                <button type="submit" name="submit_review">Submit Review</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(productId) {
            document.getElementById('modal_product_id').value = productId;
            document.getElementById('reviewModal').style.display = 'block';
        }
        function closeModal() {
            document.getElementById('reviewModal').style.display = 'none';
        }
        window.onclick = function (event) {
            if (event.target == document.getElementById('reviewModal')) {
                closeModal();
            }
        }
    </script>
</body>

</html>