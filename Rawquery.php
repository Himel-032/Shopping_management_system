<?php
session_start();
include './config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";
$query_result = null;
$table_result = null;
$executed_query = "";
$selected_table = "";

// List of all tables in the database
$tables = ['Customers', 'Categories', 'Suppliers', 'Products', 'Orders', 'Order_Items', 'Payments', 'Reviews', 'Admins'];

// Handle table selection
if (isset($_POST['view_table']) && !empty($_POST['table_name'])) {
    $selected_table = $_POST['table_name'];
    $table_query = "SELECT * FROM " . $selected_table;
    $table_result = mysqli_query($conn, $table_query);
    
    if (!$table_result) {
        $error = "Error viewing table: " . mysqli_error($conn);
    } else {
        $success = "Showing all records from table: " . $selected_table;
    }
}

// Handle custom query execution
if (isset($_POST['execute_query']) && !empty($_POST['custom_query'])) {
    $custom_query = trim($_POST['custom_query']);
    $executed_query = $custom_query;
    
    // Security check - only allow SELECT queries for safety
    $query_type = strtoupper(substr(trim($custom_query), 0, 6));
    
    if ($query_type === 'SELECT') {
        $query_result = mysqli_query($conn, $custom_query);
        
        if (!$query_result) {
            $error = "Query Error: " . mysqli_error($conn);
        } else {
            $affected_rows = mysqli_num_rows($query_result);
            $success = "Query executed successfully! Rows returned: " . $affected_rows;
        }
    } else {
        // Allow other queries (INSERT, UPDATE, DELETE, etc.) with confirmation
        $query_result = mysqli_query($conn, $custom_query);
        
        if (!$query_result) {
            $error = "Query Error: " . mysqli_error($conn);
        } else {
            $affected_rows = mysqli_affected_rows($conn);
            $success = "Query executed successfully! Affected rows: " . $affected_rows;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SQL Query Executor - Admin</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
        }

        .section {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .section h2 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
            margin-top: 0;
        }

        .message {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        select {
            width: 300px;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        select:focus {
            outline: none;
            border-color: #3498db;
        }

        textarea {
            width: 100%;
            min-height: 150px;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            resize: vertical;
            transition: border-color 0.3s;
        }

        textarea:focus {
            outline: none;
            border-color: #3498db;
        }

        .btn {
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.3s;
            margin-right: 10px;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-success {
            background-color: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background-color: #229954;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background-color: white;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #34495e;
            color: white;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e8f4fd;
        }

        .table-container {
            max-height: 500px;
            overflow-y: auto;
            overflow-x: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
        }

        .table-container table {
            min-width: 100%;
            width: max-content;
        }

        .table-container th,
        .table-container td {
            white-space: nowrap;
            min-width: 100px;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .table-container td:hover {
            white-space: normal;
            overflow: visible;
            word-wrap: break-word;
        }

        .query-info {
            background-color: #e8f4fd;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #3498db;
        }

        .query-info strong {
            color: #2c3e50;
        }

        .query-info code {
            background-color: #34495e;
            color: #ecf0f1;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }

        .help-text {
            color: #7f8c8d;
            font-size: 13px;
            margin-top: 5px;
        }

        .example-queries {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }

        .example-queries h4 {
            color: #2c3e50;
            margin-top: 0;
        }

        .example-queries pre {
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 12px;
        }

        .stats {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            text-align: center;
            flex: 1;
            margin: 0 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            margin: 0;
            font-size: 28px;
        }

        .stat-card p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>🔍 SQL Query Executor</h1>
        <div class="user-info">
            <a href="dashboard.php" class="logout-btn">Back to Dashboard</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($error != ""): ?>
            <div class="message error">❌ <?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success != ""): ?>
            <div class="message success">✅ <?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Table Viewer Section -->
        <div class="section">
            <h2>📋 View Table Contents</h2>
            <form method="post">
                <div class="form-group">
                    <label for="table_name">Select a table to view:</label>
                    <select name="table_name" id="table_name" required>
                        <option value="">-- Choose a table --</option>
                        <?php foreach ($tables as $table): ?>
                            <option value="<?php echo $table; ?>" <?php echo ($selected_table == $table) ? 'selected' : ''; ?>>
                                <?php echo $table; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="view_table" class="btn btn-primary">View Table</button>
            </form>

            <?php if ($table_result && mysqli_num_rows($table_result) > 0): ?>
                <div class="query-info">
                    <strong>Table:</strong> <code><?php echo $selected_table; ?></code> | 
                    <strong>Total Records:</strong> <?php echo mysqli_num_rows($table_result); ?>
                </div>
                
                <div class="table-container">
                    <table>
                        <tr>
                            <?php
                            // Get column names
                            $fields = mysqli_fetch_fields($table_result);
                            foreach ($fields as $field) {
                                echo "<th>" . htmlspecialchars($field->name) . "</th>";
                            }
                            ?>
                        </tr>
                        <?php
                        // Reset pointer to beginning
                        mysqli_data_seek($table_result, 0);
                        while ($row = mysqli_fetch_assoc($table_result)):
                            ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                    <td><?php echo htmlspecialchars($value ?? 'NULL'); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            <?php elseif ($table_result && mysqli_num_rows($table_result) == 0): ?>
                <p style="color: #7f8c8d; margin-top: 15px;">📭 No records found in this table.</p>
            <?php endif; ?>
        </div>

        <!-- Custom Query Executor Section -->
        <div class="section">
            <h2>⚡ Execute Custom SQL Query</h2>
            <form method="post">
                <div class="form-group">
                    <label for="custom_query">Write your SQL query:</label>
                    <textarea name="custom_query" id="custom_query" placeholder="SELECT * FROM Customers WHERE ...
JOIN Orders ON ...
-- Write any SQL query here" required><?php echo htmlspecialchars($executed_query); ?></textarea>
                    <div class="help-text">
                        ⚠️ Be careful with UPDATE, DELETE, and INSERT queries. They will modify your database!
                    </div>
                </div>
                <button type="submit" name="execute_query" class="btn btn-success">Execute Query</button>
                <button type="button" onclick="document.getElementById('custom_query').value=''" class="btn btn-danger">Clear</button>
            </form>

            <!-- Example Queries -->
            <div class="example-queries">
                <h4>💡 Example Queries:</h4>
                <pre>-- Simple SELECT
SELECT * FROM Customers LIMIT 10;

-- Modern JOIN Query (RECOMMENDED)
SELECT o.order_id, c.name, p.name AS product_name 
FROM Orders o 
JOIN Customers c ON o.customer_id = c.customer_id
JOIN Order_Items oi ON o.order_id = oi.order_id
JOIN Products p ON oi.product_id = p.product_id;

-- Use ALIASES for duplicate column names (IMPORTANT!)
SELECT c.customer_id AS customer_id, o.customer_id AS order_customer_id 
FROM Customers c, Orders o 
WHERE c.customer_id = o.customer_id;

-- Old-style Comma JOIN
SELECT * FROM Customers c, Orders o 
WHERE c.customer_id = o.customer_id;

-- Aggregate Query
SELECT p.name, COUNT(oi.order_id) AS total_orders 
FROM Products p 
LEFT JOIN Order_Items oi ON p.product_id = oi.product_id 
GROUP BY p.product_id ORDER BY total_orders DESC;</pre>

                <div style="background-color: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin-top: 10px; border-left: 4px solid #ffc107;">
                    <strong>⚠️ Important:</strong> When selecting columns with the same name from multiple tables, 
                    <strong>ALWAYS use aliases (AS)</strong> to rename them. Otherwise, one column will overwrite the other!<br>
                    <br>
                    ❌ <strong>Wrong:</strong> <code>SELECT c.customer_id, o.customer_id FROM Customers c, Orders o</code><br>
                    ✅ <strong>Correct:</strong> <code>SELECT c.customer_id AS cust_id, o.customer_id AS order_cust_id FROM Customers c, Orders o</code>
                </div>
            </div>

            <?php if ($query_result !== null): ?>
                <div class="query-info">
                    <strong>Executed Query:</strong><br>
                    <code style="display: block; margin-top: 5px; white-space: pre-wrap;"><?php echo htmlspecialchars($executed_query); ?></code>
                </div>

                <?php if (is_bool($query_result) && $query_result === true): ?>
                    <p style="color: #27ae60; font-weight: bold; margin-top: 15px;">✅ Query executed successfully (non-SELECT query)!</p>
                <?php elseif (is_object($query_result) && mysqli_num_rows($query_result) > 0): ?>
                    <div class="stats">
                        <div class="stat-card">
                            <h3><?php echo mysqli_num_rows($query_result); ?></h3>
                            <p>Rows Returned</p>
                        </div>
                        <div class="stat-card">
                            <h3><?php echo mysqli_num_fields($query_result); ?></h3>
                            <p>Columns</p>
                        </div>
                    </div>

                    <?php if (mysqli_num_fields($query_result) > 10): ?>
                        <div style="background-color: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;">
                            ⚠️ <strong>Wide Result Set:</strong> This query returns <?php echo mysqli_num_fields($query_result); ?> columns. 
                            Scroll horizontally to see all data. Consider selecting specific columns instead of using SELECT *.
                        </div>
                    <?php endif; ?>

                    <div class="table-container" style="margin-top: 20px;">
                        <table>
                            <tr>
                                <?php
                                $fields = mysqli_fetch_fields($query_result);
                                foreach ($fields as $field) {
                                    echo "<th title='" . htmlspecialchars($field->table . "." . $field->name) . "'>" . 
                                         htmlspecialchars($field->name) . 
                                         "<br><small style='font-weight:normal; opacity:0.8;'>(" . $field->table . ")</small></th>";
                                }
                                ?>
                            </tr>
                            <?php
                            mysqli_data_seek($query_result, 0);
                            while ($row = mysqli_fetch_assoc($query_result)):
                                ?>
                                <tr>
                                    <?php foreach ($row as $value): ?>
                                        <td title="<?php echo htmlspecialchars($value ?? 'NULL'); ?>">
                                            <?php echo htmlspecialchars($value ?? 'NULL'); ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endwhile; ?>
                        </table>
                    </div>
                <?php elseif (is_object($query_result)): ?>
                    <p style="color: #7f8c8d; margin-top: 15px;">📭 Query returned no results (0 rows matched).</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-resize textarea
        const textarea = document.getElementById('custom_query');
        textarea.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Syntax highlighting hint
        textarea.addEventListener('keydown', function (e) {
            // Tab key support for indentation
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = this.selectionStart;
                const end = this.selectionEnd;
                this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
                this.selectionStart = this.selectionEnd = start + 4;
            }
        });
    </script>
</body>

</html>