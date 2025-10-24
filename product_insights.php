<?php
include 'config.php'; // Database connection

// ✅ Query 1: Product with Category Name
$sql1 = "
SELECT p.name AS product_name, c.name AS category_name
FROM Products p
JOIN Categories c USING(category_id)
ORDER BY c.name, p.name
";
$result1 = $conn->query($sql1);

// ✅ Query 2: Products cheaper than category average
$sql2 = "
WITH category_avg AS (
    SELECT category_id, AVG(price) AS avg_price
    FROM Products
    GROUP BY category_id
)
SELECT p.product_id, p.name, p.price, ca.avg_price
FROM Products p
JOIN category_avg ca ON p.category_id = ca.category_id
WHERE p.price < ca.avg_price
ORDER BY p.category_id
";
$result2 = $conn->query($sql2);

// ✅ Query 3: Products in the same category (SELF JOIN)
$sql3 = "
SELECT p1.name AS product1, p2.name AS product2, p1.category_id
FROM Products p1
JOIN Products p2 ON p1.category_id = p2.category_id
WHERE p1.product_id < p2.product_id
ORDER BY p1.category_id
";
$result3 = $conn->query($sql3);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Product Insights</title>
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
        <h1>📊 Product Insights Dashboard</h1>
        <div class="header-actions">
            <a href="dashboard.php">← Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    

    <div class="section">
        <h2>🟢 Product & Category Overview</h2>
        <label>Query:</label>
        <p><code>SELECT p.name, c.name FROM Products p JOIN Categories c USING(category_id);</code></p>
        <table>
            <tr>
                <th>Product Name</th>
                <th>Category Name</th>
            </tr>
            <?php while ($row = $result1->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="section">
        <h2>🟡 Products Cheaper than Category Average</h2>
        <label>Query:</label>
        <p><code>WITH category_avg AS (<br>
    SELECT category_id, AVG(price) AS avg_price<br>
    FROM Products<br>
    GROUP BY category_id<br>
    )<br>
    SELECT p.product_id, p.name, p.price, ca.avg_price<br>
    FROM Products p<br>
    JOIN category_avg ca ON p.category_id = ca.category_id<br>
    WHERE p.price < ca.avg_price<br>
    ORDER BY p.category_id;</code></p>
        <table>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Category Avg</th>
            </tr>
            <?php while ($row = $result2->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['product_id']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td>$<?= number_format($row['price'], 2) ?></td>
                    <td>$<?= number_format($row['avg_price'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="section">
        <h2>🔵 Products in the Same Category</h2>
        <label>Query:</label>
        <p><code>SELECT p1.name AS product1, p2.name AS product2, p1.category_id<br>
FROM Products p1<br>
JOIN Products p2 ON p1.category_id = p2.category_id<br>
WHERE p1.product_id < p2.product_id<br>
ORDER BY p1.category_id;</code>
        </p>
        <table>
            <tr>
                <th>Product 1</th>
                <th>Product 2</th>
                <th>Category ID</th>
            </tr>
            <?php while ($row = $result3->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['product1']) ?></td>
                    <td><?= htmlspecialchars($row['product2']) ?></td>
                    <td><?= htmlspecialchars($row['category_id']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

</body>

</html>