<?php
include 'config.php';
session_start();

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Array to track executed queries
$executed_queries = [];

// --- INSERT ---
if (isset($_POST['add_product'])) {
    $id = (int) trim($_POST['product_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);
    $stock = (int) $_POST['stock'];
    $category_id = (int) $_POST['category_id'];
    $supplier_id = (int) $_POST['supplier_id'];

    // Check if product_id exists
    $check_sql = "SELECT * FROM Products WHERE product_id=$id";
    $check = $conn->query($check_sql);
    $executed_queries[] = ['query' => $check_sql, 'type' => 'SELECT', 'status' => $check ? 'success' : 'error'];
    
    if ($check->num_rows > 0) {
        echo "<p style='color:red;'>Product ID $id already exists!</p>";
    } else {
        $sql = "INSERT INTO Products (product_id, name, price, stock, category_id, supplier_id)
                VALUES ($id, '$name', $price, $stock, $category_id, $supplier_id)";
        $result = $conn->query($sql);
        $executed_queries[] = ['query' => $sql, 'type' => 'INSERT', 'status' => $result ? 'success' : 'error'];
        
        if ($result) {
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
    $view_result = $conn->query($query_view);
    $executed_queries[] = ['query' => $query_view, 'type' => 'CREATE VIEW', 'status' => $view_result ? 'success' : 'error'];

    $sql = "UPDATE view_products
        SET name='$name', price=$price, stock=$stock, category_id=$category_id, supplier_id=$supplier_id
        WHERE product_id=$id";
    $update_result = $conn->query($sql);
    $executed_queries[] = ['query' => $sql, 'type' => 'UPDATE', 'status' => $update_result ? 'success' : 'error'];
}

// --- DELETE ---
if (isset($_POST['delete_product'])) {
    $id = (int) $_POST['product_id'];
    $sql = "DELETE FROM Products WHERE product_id=$id";
    $result = $conn->query($sql);
    $executed_queries[] = ['query' => $sql, 'type' => 'DELETE', 'status' => $result ? 'success' : 'error'];
}

// --- SEARCH/FILTER ---
$search_mode = isset($_GET['search_mode']) ? $_GET['search_mode'] : 'all';
$selected_categories = isset($_GET['categories']) ? $_GET['categories'] : [];

// Build fetch query with category filtering
$fetch_sql = "
    SELECT p.*, c.name AS category_name, s.name AS supplier_name 
    FROM Products p
    JOIN Categories c ON p.category_id=c.category_id
    JOIN Suppliers s ON p.supplier_id=s.supplier_id
";

// Apply category filters using IN or NOT IN
if ($search_mode === 'include' && !empty($selected_categories)) {
    $category_ids = implode(',', array_map('intval', $selected_categories));
    $fetch_sql .= " WHERE p.category_id IN ($category_ids)";
} elseif ($search_mode === 'exclude' && !empty($selected_categories)) {
    $category_ids = implode(',', array_map('intval', $selected_categories));
    $fetch_sql .= " WHERE p.category_id NOT IN ($category_ids)";
}

$fetch_sql .= " ORDER BY product_id";

$result = $conn->query($fetch_sql);
$executed_queries[] = ['query' => $fetch_sql, 'type' => 'SELECT', 'status' => $result ? 'success' : 'error'];

// Fetch categories and suppliers for dropdowns
$categories = $conn->query("SELECT * FROM Categories ORDER BY name");
$suppliers = $conn->query("SELECT * FROM Suppliers ORDER BY name");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Products Management</title>
    <link rel="stylesheet" href="css/products.css">
    <style>
        .top-section {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .form-area {
            flex: 1;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .sql-sidebar {
            width: 400px;
            background-color: #1e1e1e;
            color: #d4d4d4;
            padding: 15px;
            border-radius: 8px;
            position: sticky;
            top: 20px;
            height: fit-content;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .sql-sidebar h3 {
            color: #4ec9b0;
            margin-top: 0;
            border-bottom: 2px solid #4ec9b0;
            padding-bottom: 10px;
            font-size: 16px;
        }

        .sql-query-item {
            background-color: #2d2d2d;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 12px;
            border-left: 4px solid #569cd6;
        }

        .sql-query-item.success {
            border-left-color: #4caf50;
        }

        .sql-query-item.error {
            border-left-color: #f44336;
        }

        .sql-query-item.insert {
            border-left-color: #2196f3;
        }

        .sql-query-item.update {
            border-left-color: #ff9800;
        }

        .sql-query-item.delete {
            border-left-color: #f44336;
        }

        .sql-query-item h4 {
            color: #ce9178;
            margin: 0 0 8px 0;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sql-query-item pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.4;
            color: #d4d4d4;
        }

        .query-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-select {
            background-color: #2196f3;
            color: white;
        }

        .badge-insert {
            background-color: #4caf50;
            color: white;
        }

        .badge-update {
            background-color: #ff9800;
            color: white;
        }

        .badge-delete {
            background-color: #f44336;
            color: white;
        }

        .badge-create {
            background-color: #9c27b0;
            color: white;
        }

        .status-icon {
            font-size: 14px;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background-color: white;
        }

        table th,
        table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #2c3e50;
            color: white;
            font-weight: bold;
        }

        table td input[type="number"],
        table td input[type="text"],
        table td select {
            width: 100%;
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        /* Actions column - prevent button overlapping */
        table td:last-child {
            min-width: 180px;
            white-space: nowrap;
        }

        table td input[type="submit"] {
            padding: 6px 12px;
            margin: 2px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }

        table td input[type="submit"][name="update_product"] {
            background-color: #3498db;
            color: white;
        }

        table td input[type="submit"][name="update_product"]:hover {
            background-color: #2980b9;
        }

        table td input[type="submit"][name="delete_product"] {
            background-color: #e74c3c;
            color: white;
        }

        table td input[type="submit"][name="delete_product"]:hover {
            background-color: #c0392b;
        }

        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        table tr:hover {
            background-color: #e8f4fd;
        }

        /* Search Filter Section */
        .search-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .search-section h3 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .filter-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }

        .filter-group select[multiple] {
            width: 100%;
            min-height: 100px;
            padding: 5px;
            border: 2px solid #ddd;
            border-radius: 4px;
            background-color: white;
        }

        .filter-group select[multiple]:focus {
            border-color: #3498db;
            outline: none;
        }

        .filter-group select[multiple] option {
            padding: 5px;
        }

        .radio-group {
            display: flex;
            gap: 15px;
            margin-top: 5px;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: normal;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-filter {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-search {
            background-color: #3498db;
            color: white;
        }

        .btn-search:hover {
            background-color: #2980b9;
        }

        .btn-reset {
            background-color: #95a5a6;
            color: white;
        }

        .btn-reset:hover {
            background-color: #7f8c8d;
        }

        .filter-hint {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }

        @media (max-width: 1200px) {
            .top-section {
                flex-direction: column;
            }

            .sql-sidebar {
                width: 100%;
                position: relative;
                max-height: 400px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <a href="delete_view.php"><button>Dashboard</button></a>
    </div>

    <h2>Products Management</h2>

    <div class="top-section">
        <!-- Form Area (Left) -->
        <div class="form-area">
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
        </div>

        <!-- SQL Sidebar (Right) -->
        <div class="sql-sidebar">
            <h3>📊 Executed SQL Queries</h3>

            <?php foreach ($executed_queries as $index => $query_data): ?>
                <div class="sql-query-item <?php echo strtolower(str_replace(' ', '-', $query_data['type'])) . ' ' . $query_data['status']; ?>">
                    <h4>
                        <span>
                            Query #<?php echo $index + 1; ?>
                            <span class="query-badge badge-<?php echo strtolower(str_replace(' ', '-', $query_data['type'])); ?>">
                                <?php echo $query_data['type']; ?>
                            </span>
                        </span>
                        <span class="status-icon">
                            <?php echo $query_data['status'] === 'success' ? '✓' : '✗'; ?>
                        </span>
                    </h4>
                    <pre><?php echo htmlspecialchars($query_data['query']); ?></pre>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <hr>

    <!-- Category Search/Filter Section -->
    <div class="search-section">
        <h3>🔍 Filter Products by Category</h3>
        <form method="get" class="filter-form">
            <div class="filter-group">
                <label>Select Categories:</label>
                <select name="categories[]" multiple size="5">
                    <?php
                    // Fetch categories for filter
                    $categories_filter = $conn->query("SELECT * FROM Categories ORDER BY name");
                    while ($cat = $categories_filter->fetch_assoc()):
                        $selected = in_array($cat['category_id'], $selected_categories) ? 'selected' : '';
                    ?>
                        <option value="<?php echo $cat['category_id']; ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <div class="filter-hint">Hold Ctrl/Cmd to select multiple categories</div>
            </div>

            <div class="filter-group">
                <label>Filter Mode:</label>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="search_mode" value="all" <?php echo $search_mode === 'all' ? 'checked' : ''; ?>>
                        Show All
                    </label>
                    <label>
                        <input type="radio" name="search_mode" value="include" <?php echo $search_mode === 'include' ? 'checked' : ''; ?>>
                        Include (IN)
                    </label>
                    <label>
                        <input type="radio" name="search_mode" value="exclude" <?php echo $search_mode === 'exclude' ? 'checked' : ''; ?>>
                        Exclude (NOT IN)
                    </label>
                </div>
            </div>

            <div class="filter-buttons">
                <button type="submit" class="btn-filter btn-search">🔍 Apply Filter</button>
                <button type="button" class="btn-filter btn-reset" onclick="window.location.href='products.php'">↻ Reset</button>
            </div>
        </form>
    </div>

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
