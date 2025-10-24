<?php
include '../config.php';
session_start();
$error = "";

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);

    // --- 1️⃣ Check email format using SQL REGEXP_SUBSTR ---
    $email_check = $conn->query("
    SELECT REGEXP_SUBSTR(
        '$email', 
        '[#$%&*]'
    ) AS email_invalid
");


    $email_invalid = $email_check->fetch_assoc()['email_invalid'];

    if ($email_invalid !== null) {
        $error = "Invalid email format! Only letters, numbers, underscores, and dots are allowed.";
    } else {
        // --- 2️⃣ Check if email already exists ---
        $check = $conn->query("SELECT * FROM Customers WHERE email='$email'");
        if ($check->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            // --- 3️⃣ Insert new customer ---
            $sql = "INSERT INTO Customers (name, email, phone, address, password)
                    VALUES ('$name', '$email', '$phone', '$address', '$password')";
            if ($conn->query($sql)) {
                $_SESSION['customer_id'] = $conn->insert_id;
                $_SESSION['customer_name'] = $name;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Registration failed: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Customer Registration</title>
    <link rel="stylesheet" href="css/registration.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }

        h2 {
            text-align: center;
        }

        form {
            max-width: 400px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }

        input[type=text],
        input[type=email],
        input[type=password] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        input[type=submit] {
            background-color: #007BFF;
            color: white;
            padding: 10px;
            width: 100%;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type=submit]:hover {
            background-color: #0056b3;
        }

        p.error {
            color: red;
            text-align: center;
        }

        p.login-link {
            text-align: center;
        }
    </style>
</head>

<body>

    <h2>Register</h2>

    <?php if (!empty($error))
        echo "<p class='error'>$error</p>"; ?>

    <form method="post">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone">
        <input type="text" name="address" placeholder="Address">
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" name="register" value="Register">
    </form>

    <p class="login-link">Already have an account? <a href="login.php">Login</a></p>

</body>

</html>