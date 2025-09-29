<?php
session_start();
include '../config.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];

// Fetch customer details
$sql = "SELECT name, email, phone, address FROM Customers WHERE customer_id = $customer_id";
$res = mysqli_query($conn, $sql);
$customer = mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - <?php echo htmlspecialchars($customer_name); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .field {
            margin-bottom: 15px;
        }

        .field label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .actions {
            margin-top: 25px;
            text-align: center;
        }

        .actions a button {
            padding: 12px 25px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            background: #0097e6;
            color: #fff;
            cursor: pointer;
        }

        .actions a button:hover {
            background: #40739e;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($customer_name); ?>!</h2>

        <div class="field">
            <label>Name:</label>
            <span><?php echo htmlspecialchars($customer['name']); ?></span>
        </div>
        <div class="field">
            <label>Email:</label>
            <span><?php echo htmlspecialchars($customer['email']); ?></span>
        </div>
        <div class="field">
            <label>Phone:</label>
            <span><?php echo htmlspecialchars($customer['phone']); ?></span>
        </div>
        <div class="field">
            <label>Address:</label>
            <span><?php echo htmlspecialchars($customer['address']); ?></span>
        </div>

        <div class="actions">
            <a href="editProfile.php"><button>Edit Profile</button></a>
            <a href="create_order.php"><button>Place New Order</button></a>
            <a href="my_orders.php"><button>View Your Orders</button></a>
            <a href="make_review.php"><button>Write Product Reviews</button></a>
            <a href="logout.php"><button>Logout</button></a>
        </div>
    </div>

</body>

</html>
<!-- 
    <a href="create_order.php">
        <button>Place New Order</button>
    </a> <br><br>
    <a href="my_orders.php">
        <button>View Your Orders</button>
    </a>

</body>
</html> -->
