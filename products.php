<?php
include 'config.php';
session_start();

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// --- INSERT ---
if (isset($_POST['add_product'])) {
    $id = (int) trim($_POST['product_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);
    $stock = (int) $_POST['stock'];
    $category_id = (int) $_POST['category_id'];
    $supplier_id = (int) $_POST['supplier_id'];

    // Check if product_id exists
    $check = $conn->query("SELECT * FROM Products WHERE product_id=$id");
    if ($check->num_rows > 0) {
        echo "<p style='color:red;'>Product ID $id already exists!</p>";
    } else {
        $sql = "INSERT INTO Products (product_id, name, price, stock, category_id, supplier_id)
                VALUES ($id, '$name', $price, $stock, $category_id, $supplier_id)";
        if ($conn->query($sql)) {
            echo "<p style='color:green;'>Product added successfully!</p>";
        } else {
            echo "<p style='color:red;'>Insert Error: " . $conn->error . "</p>";
        }
    }
}

// --- UPDATE ---
if (isset($_POST['update_product'])) {
    $id = (int) $_POST['product_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);
    $stock = (int) $_POST['stock'];
    $category_id = (int) $_POST['category_id'];
    $supplier_id = (int) $_POST['supplier_id'];
    $query_view = "CREATE OR REPLACE VIEW view_products AS
SELECT product_id, name, price, stock, category_id, supplier_id
FROM Products";
    $conn->query($query_view);

    // $sql = "UPDATE Products 
    //         SET name='$name', price=$price, stock=$stock, category_id=$category_id, supplier_id=$supplier_id
    //         WHERE product_id=$id";
    $sql = "UPDATE view_products
        SET name='$name', price=$price, stock=$stock, category_id=$category_id, supplier_id=$supplier_id
        WHERE product_id=$id";

    $conn->query($sql);
}

// --- DELETE ---
if (isset($_POST['delete_product'])) {
    $id = (int) $_POST['product_id'];
    $conn->query("DELETE FROM Products WHERE product_id=$id");
}

// Fetch products
$result = $conn->query("
    SELECT p.*, c.name AS category_name, s.name AS supplier_name 
    FROM Products p
    JOIN Categories c ON p.category_id=c.category_id
    JOIN Suppliers s ON p.supplier_id=s.supplier_id
    ORDER BY product_id
");

// Fetch categories and suppliers for dropdowns
$categories = $conn->query("SELECT * FROM Categories ORDER BY name");
$suppliers = $conn->query("SELECT * FROM Suppliers ORDER BY name");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Products Management</title>
    <link rel="stylesheet" href="css/products.css">
</head>
<body>
    <div class="dashboard">
        <a href="dashboard.php"><button>Dashboard</button></a>
    </div>

    <h2>Products Management</h2>

    <!-- Add Product -->
    <h3>Add Product</h3>
    <form method="post">
        <input type="number" name="product_id" placeholder="Product ID" required><br><br>
        <input type="text" name="name" placeholder="Product Name" required><br><br>
        <input type="number" step="0.01" name="price" placeholder="Price" required><br><br>
        <input type="number" name="stock" placeholder="Stock" required><br><br>
        <select name="category_id" required>
            <option value="">Select Category</option>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $cat['category_id']; ?>"><?php echo $cat['name']; ?></option>
            <?php endwhile; ?>
        </select><br><br>
        <select name="supplier_id" required>
            <option value="">Select Supplier</option>
            <?php while ($sup = $suppliers->fetch_assoc()): ?>
                    <option value="<?php echo $sup['supplier_id']; ?>"><?php echo $sup['name']; ?></option>
            <?php endwhile; ?>
        </select><br><br>
        <input type="submit" name="add_product" value="Add Product">
    </form>

    <hr>

    <!-- Product List -->
    <h3>All Products</h3>
    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Category</th>
            <th>Supplier</th>
            <th>Actions</th>
        </tr>
        <?php
        // Reset result sets for dropdowns
        $categories = $conn->query("SELECT * FROM Categories ORDER BY name");
        $suppliers = $conn->query("SELECT * FROM Suppliers ORDER BY name");

        while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <form method="post">
                        <td><?php echo $row['product_id']; ?>
                            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                        </td>
                        <td><input type="text" name="name" value="<?php echo $row['name']; ?>"></td>
                        <td><input type="number" step="0.01" name="price" value="<?php echo $row['price']; ?>"></td>
                        <td><input type="number" name="stock" value="<?php echo $row['stock']; ?>"></td>
                        <td>
                            <select name="category_id" required>
                                <?php
                                $categories->data_seek(0); // reset pointer
                                while ($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $cat['category_id']; ?>" <?php if ($cat['category_id'] == $row['category_id'])
                                               echo 'selected'; ?>>
                                            <?php echo $cat['name']; ?>
                                        </option>
                                <?php endwhile; ?>
                            </select>
                        </td>
                        <td>
                            <select name="supplier_id" required>
                                <?php
                                $suppliers->data_seek(0); // reset pointer
                                while ($sup = $suppliers->fetch_assoc()): ?>
                                        <option value="<?php echo $sup['supplier_id']; ?>" <?php if ($sup['supplier_id'] == $row['supplier_id'])
                                               echo 'selected'; ?>>
                                            <?php echo $sup['name']; ?>
                                        </option>
                                <?php endwhile; ?>
                            </select>
                        </td>
                        <td>
                            <input type="submit" name="update_product" value="Update">
                            <input type="submit" name="delete_product" value="Delete" onclick="return confirm('Are you sure?')">
                        </td>
                    </form>
                </tr>
        <?php endwhile; ?>
    </table>
    <script>
if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
}
</script>
</body>
</html>
