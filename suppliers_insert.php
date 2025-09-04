<?php
include 'config.php';
session_start();

// Check admin login

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// INSERT
if (isset($_POST['add_supplier'])) {
    $id = (int) trim($_POST['supplier_id']); // manual ID
    $name = $conn->real_escape_string($_POST['name']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $email = $conn->real_escape_string($_POST['email']);

    // Check if ID already exists
    $check = $conn->query("SELECT * FROM Suppliers WHERE supplier_id=$id");
    if (!$check) {
        die("Check Query Error: " . $conn->error);
    }

    if ($check->num_rows > 0) {
        echo "<p style='color:red;'>Supplier ID $id already exists! Choose another ID.</p>";
    } else {
        $sql = "INSERT INTO Suppliers (supplier_id, name, contact, email) VALUES ($id, '$name', '$contact', '$email')";
        if ($conn->query($sql)) {
            echo "<p style='color:green;'>Supplier added successfully!</p>";
        } else {
            echo "<p style='color:red;'>Insert Error: " . $conn->error . "</p>";
        }
    }
}

// UPDATE
if (isset($_POST['update_supplier'])) {
    $id = $_POST['supplier_id'];
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];

    $sql = "UPDATE Suppliers SET name='$name', contact='$contact', email='$email' WHERE supplier_id=$id";
    $conn->query($sql);
}

// DELETE via POST
if (isset($_POST['delete_supplier'])) {
    $id = (int) $_POST['supplier_id'];
    $conn->query("DELETE FROM Suppliers WHERE supplier_id=$id");
}


// Fetch suppliers
$result = $conn->query("SELECT * FROM Suppliers ORDER BY supplier_id");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Suppliers Management</title>
    <link rel="stylesheet" href="css/suppliers.css">
</head>

<body>
    <div class="dashboard">
        <button><a href="dashboard.php">Dahsboard</a></button>
    </div>
    <h2>Suppliers Management</h2>

    <!-- Add Supplier -->
    <h3>Add Supplier</h3>
    <form method="post">
        <input type="number" name="supplier_id" placeholder="Supplier ID" required><br><br>
        <input type="text" name="name" placeholder="Name" required><br><br>
        <input type="text" name="contact" placeholder="Contact"><br><br>
        <input type="email" name="email" placeholder="Email"><br><br>
        <input type="submit" name="add_supplier" value="Add Supplier">
    </form>

    <hr>

    <!-- Supplier List -->
    <h3>All Suppliers</h3>
    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Contact</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <form method="post">
                    <td>
                        <input type="number" name="supplier_id" value="<?php echo $row['supplier_id']; ?>" readonly>
                    </td>
                    <td><input type="text" name="name" value="<?php echo $row['name']; ?>"></td>
                    <td><input type="text" name="contact" value="<?php echo $row['contact']; ?>"></td>
                    <td><input type="email" name="email" value="<?php echo $row['email']; ?>"></td>
                    <td>
                        <input type="submit" name="update_supplier" value="Update">
                         <input type="hidden" name="supplier_id" value="<?php echo $row['supplier_id']; ?>">
                        <input type="submit" name="delete_supplier" value="Delete" onclick="return confirm('Are you sure?')">
                    </td>
                </form>
            </tr>
        <?php endwhile; ?>
    </table>
</body>

</html>