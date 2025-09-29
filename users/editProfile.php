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

// Fetch current info
$sql = "SELECT name, email, phone, address FROM Customers WHERE customer_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (!$res) {
    die("Database error: " . mysqli_error($conn));
}

$customer = mysqli_fetch_assoc($res);

// Handle form submission
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Email uniqueness check
    $check_sql = "SELECT customer_id FROM Customers WHERE email=? AND customer_id != ?";
    $stmt_check = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt_check, "si", $email, $customer_id);
    mysqli_stmt_execute($stmt_check);
    $check_res = mysqli_stmt_get_result($stmt_check);

    if (!$check_res) {
        $error = "Database error: " . mysqli_error($conn);
    } elseif (mysqli_num_rows($check_res) > 0) {
        $error = "Email is already used by another account.";
    } elseif ($password !== "" && $password !== $password_confirm) {
        $error = "Passwords do not match.";
    } else {
        // Prepare update statement
        if ($password !== "") {
           
            $update_sql = "UPDATE Customers SET name=?, email=?, phone=?, address=?, password=? WHERE customer_id=?";
            $stmt_update = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($stmt_update, "sssssi", $name, $email, $phone, $address, $password, $customer_id);
        } else {
            $update_sql = "UPDATE Customers SET name=?, email=?, phone=?, address=? WHERE customer_id=?";
            $stmt_update = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($stmt_update, "ssssi", $name, $email, $phone, $address, $customer_id);
        }

        if (mysqli_stmt_execute($stmt_update)) {
            $_SESSION['customer_name'] = $name;
            header("Location: dashboard.php?success=Profile updated successfully!");
            exit();
        } else {
            $error = "Database error: " . mysqli_stmt_error($stmt_update);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Profile - <?php echo htmlspecialchars($customer_name); ?></title>
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

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: bold;
            margin-top: 10px;
        }

        input {
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            margin-top: 20px;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background: #0097e6;
            color: #fff;
            cursor: pointer;
        }

        button:hover {
            background: #40739e;
        }

        .message {
            margin-top: 15px;
            text-align: center;
        }

        .message.error {
            color: red;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Edit Profile</h2>

        <?php if ($error != "")
            echo "<div class='message error'>$error</div>"; ?>

        <form method="post">
            <label>Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($customer['name']); ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required>

            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>">

            <label>Address</label>
            <input type="text" name="address" value="<?php echo htmlspecialchars($customer['address']); ?>">

<label>New Password (leave blank to keep current)</label>
<input type="password" name="password" placeholder="Enter new password" autocomplete="new-password">

<label>Confirm Password</label>
<input type="password" name="password_confirm" placeholder="Confirm new password" autocomplete="new-password">



            <button type="submit" name="update_profile">Update Profile</button>
        </form>

        <div style="text-align:center; margin-top:15px;">
            <a href="dashboard.php"><button>Back to Dashboard</button></a>
        </div>
    </div>

</body>

</html>