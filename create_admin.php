<?php
include 'config.php';

// Example: Create an admin account
// You should run this once to create your first admin account

$email = "admin@gmail.com"; // Change this
$password = "12345"; // Change this


try {
    $stmt = $pdo->prepare("INSERT INTO admins (email, password) VALUES (?, ?)");
    $stmt->execute([$email, $password]);
    echo "Admin account created successfully!";
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo "Admin with this email already exists!";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>