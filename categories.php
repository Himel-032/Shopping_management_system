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

// INSERT
if (isset($_POST['add_category'])) {
    $id = (int) trim($_POST['category_id']); // manual ID
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);

    // Check if ID already exists
    $check_sql = "SELECT * FROM Categories WHERE category_id=$id";
    $check = $conn->query($check_sql);
    $executed_queries[] = ['query' => $check_sql, 'type' => 'SELECT', 'status' => $check ? 'success' : 'error'];
    
    if (!$check) {
        die("Check Query Error: " . $conn->error);
    }

    if ($check->num_rows > 0) {
        echo "<p style='color:red;'>Category ID $id already exists! Choose another ID.</p>";
    } else {
        $sql = "INSERT INTO Categories (category_id, name, description) VALUES ($id, '$name', '$description')";
        $result = $conn->query($sql);
        $executed_queries[] = ['query' => $sql, 'type' => 'INSERT', 'status' => $result ? 'success' : 'error'];
        
        if ($result) {
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
    $result = $conn->query($sql);
    $executed_queries[] = ['query' => $sql, 'type' => 'UPDATE', 'status' => $result ? 'success' : 'error'];
}

// DELETE via POST
if (isset($_POST['delete_category'])) {
    $id = (int) $_POST['category_id'];
    $sql = "DELETE FROM Categories WHERE category_id=$id";
    $result = $conn->query($sql);
    $executed_queries[] = ['query' => $sql, 'type' => 'DELETE', 'status' => $result ? 'success' : 'error'];
}


// Fetch categories - ALWAYS executed to display the table
$fetch_sql = "SELECT * FROM Categories ORDER BY category_id";
$result = $conn->query($fetch_sql);
$executed_queries[] = ['query' => $fetch_sql, 'type' => 'SELECT', 'status' => $result ? 'success' : 'error'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Categories Management</title>
    <link rel="stylesheet" href="css/suppliers.css">
    <style>
        .main-layout {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .sql-sidebar {
            width: 350px;
            background-color: #1e1e1e;
            color: #d4d4d4;
            padding: 15px;
            border-radius: 8px;
            position: sticky;
            top: 200px;
            height: fit-content;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            order: 2;
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

        .status-icon {
            font-size: 14px;
        }

        .content-area {
            flex: 1;
            min-width: 0;
            order: 1;
        }

        @media (max-width: 1200px) {
            .main-layout {
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
    <div class="main-layout">
        <!-- Main Content Area -->
        <div class="content-area">
            <div class="dashboard">
                <button><a href="dashboard.php">Dashboard</a></button>
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

            <!-- Category List -->
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
        </div>

        <!-- SQL Sidebar -->
        <div class="sql-sidebar">
            <h3>📊 Executed SQL Queries</h3>

            <?php foreach ($executed_queries as $index => $query_data): ?>
                <div class="sql-query-item <?php echo strtolower($query_data['type']) . ' ' . $query_data['status']; ?>">
                    <h4>
                        <span>
                            Query #<?php echo $index + 1; ?>
                            <span class="query-badge badge-<?php echo strtolower($query_data['type']); ?>">
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
</body>

</html>