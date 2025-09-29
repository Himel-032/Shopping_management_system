<?php
include 'config.php';
session_start();

// Check admin login

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// INSERT
if (isset($_POST['add_category'])) {
    $id = (int) trim($_POST['category_id']); // manual ID
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);

    // Check if ID already exists
    $check = $conn->query("SELECT * FROM Categories WHERE category_id=$id");
    if (!$check) {
        die("Check Query Error: " . $conn->error);
    }

    if ($check->num_rows > 0) {
        echo "<p style='color:red;'>Category ID $id already exists! Choose another ID.</p>";
    } else {
        $sql = "INSERT INTO Categories (category_id, name, description) VALUES ($id, '$name', '$description')";
        if ($conn->query($sql)) {
            echo "<p style='color:green;'>Category added successfully!</p>";
        } else {
            echo "<p style='color:red;'>Insert Error: " . $conn->error . "</p>";
        }
    }
}

// UPDATE
if (isset($_POST['update_category'])) {
    $id = $_POST['category_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    

    $sql = "UPDATE Categories SET name='$name', description='$description' WHERE category_id=$id";
    $conn->query($sql);
}

// DELETE via POST
if (isset($_POST['delete_category'])) {
    $id = (int) $_POST['category_id'];
    $conn->query("DELETE FROM Categories WHERE category_id=$id");
}


// Fetch suppliers
$result = $conn->query("SELECT * FROM Categories ORDER BY category_id");
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
    <h2>Categories Management</h2>

    <!-- Add Category -->
    <h3>Add Category</h3>
    <form method="post">
        <input type="number" name="category_id" placeholder="Category ID" required><br><br>
        <input type="text" name="name" placeholder="Name" required><br><br>
        <input type="text" name="description" placeholder="Description"><br><br>
        
        <input type="submit" name="add_category" value="Add Category">
    </form>

    <hr>

    <!-- Supplier List -->
    <h3>All Categories</h3>
    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <form method="post">
                    <td>
                        <input type="number" name="category_id" value="<?php echo $row['category_id']; ?>" readonly>
                    </td>
                    <td><input type="text" name="name" value="<?php echo $row['name']; ?>"></td>
                    <td><input type="text" name="description" value="<?php echo $row['description']; ?>"></td>
                   
                    <td>
                        <input type="submit" name="update_category" value="Update">
                        <input type="hidden" name="categoryr_id" value="<?php echo $row['category_id']; ?>">
                        <input type="submit" name="delete_category" value="Delete"
                            onclick="return confirm('Are you sure?')">
                    </td>
                </form>
            </tr>
        <?php endwhile; ?>
    </table>
</body>

</html>