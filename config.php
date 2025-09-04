<?php
$servername = "localhost";
$username = "root";   // Default phpMyAdmin username
$password = "";       // Default phpMyAdmin password (usually empty)
$dbname = "shopping_management_system";
$conn = new mysqli("localhost", "root", "", "shopping_management_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
try {
    // Added charset=utf8mb4 for better Unicode support (emojis, special chars, etc.)
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Enable exceptions for errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional: Fetch data as associative array by default
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>