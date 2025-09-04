<?php
include '../config.php';
session_start();

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']); // plain text

    $result = $conn->query("SELECT * FROM Customers WHERE email='$email' AND password='$password'");
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['customer_id'] = $user['customer_id'];
        $_SESSION['customer_name'] = $user['name'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Customer Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <h2>Login</h2>
    <?php if (isset($error))
        echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <input type="submit" name="login" value="Login">
    </form>
    <p>Don't have an account? <a href="registration.php">Register</a></p>
</body>

</html>