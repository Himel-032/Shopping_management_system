<?php
include '../config.php';
session_start();

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);

    // Check if email already exists
    $check = $conn->query("SELECT * FROM Customers WHERE email='$email'");
    if ($check->num_rows > 0) {
        $error = "Email already registered!";
    } else {
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
?>

<!DOCTYPE html>
<html>

<head>
    <title>Customer Registration</title>
    <link rel="stylesheet" href="css/registration.css">
</head>

<body>
    <h2>Register</h2>
    <?php if (isset($error))
        echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post">
        <input type="text" name="name" placeholder="Full Name" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="text" name="phone" placeholder="Phone"><br><br>
        <input type="text" name="address" placeholder="Address"><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <input type="submit" name="register" value="Register">
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
</body>

</html>