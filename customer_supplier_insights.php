<?php
include 'config.php'; // Database connection

// --- Query 1: Customers who are also Suppliers (INTERSECT simulation) ---
$sql1 = "
SELECT c.customer_id, c.name, c.email, c.phone, c.address
FROM Customers c
WHERE c.email IN (SELECT email FROM Suppliers)
ORDER BY c.name
";
$result1 = $conn->query($sql1);

// --- Query 2: Customers who are NOT Suppliers (MINUS simulation) ---
$sql2 = "
SELECT c.customer_id, c.name, c.email, c.phone, c.address
FROM Customers c
WHERE c.email NOT IN (SELECT email FROM Suppliers)
ORDER BY c.name
";
$result2 = $conn->query($sql2);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Customer-Supplier Insights</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #222;
            margin-bottom: 30px;
        }

        .section {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 25px;
        }

        .section h2 {
            background: #007BFF;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        code {
            display: block;
            background: #eee;
            padding: 10px;
            border-radius: 5px;
            white-space: pre-wrap;
            margin-bottom: 10px;
        }
        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h1 {
            color: #667eea;
            font-size: 28px;
            font-weight: 700;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .header-actions a {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .header-actions a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>📊 Customer-Supplier Insights</h1>
        <div class="header-actions">
            <a href="dashboard.php">← Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    

    <div class="section">
        <h2>🔗 Customers Who Are Also Suppliers</h2>
        <label>Query:</label>
        <code><?= htmlspecialchars($sql1) ?></code>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
            </tr>
            <?php while ($row = $result1->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['customer_id']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="section">
        <h2>🟢 Customers Who Are NOT Suppliers</h2>
        <label>Query:</label>
        <code><?= htmlspecialchars($sql2) ?></code>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
            </tr>
            <?php while ($row = $result2->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['customer_id']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

</body>

</html>