-- ========================================
-- ALL SQL QUERIES - Shopping Management System
-- Generated: October 25, 2025
-- ========================================
-- This file contains ALL SQL queries used throughout the project
-- organized by source file for easy reference and documentation
-- Note: Some queries contain placeholders ($id, ?, etc.) that are
-- replaced with actual values at runtime
-- ========================================

-- LAB 01: BASIC OPERATIONS
-- ========================================

-- Check current user
SELECT USER();

-- Show all tables
SHOW TABLES;

-- Describe table structure
DESCRIBE Customers;
DESCRIBE Products;
DESCRIBE Orders;

-- ========================================
-- LAB 02: DDL & DML OPERATIONS
-- ========================================

-- ADD COLUMN
ALTER TABLE Customers ADD date_joined DATE DEFAULT CURRENT_DATE;
ALTER TABLE Products ADD discount_percentage DECIMAL(5,2) DEFAULT 0.00;
ALTER TABLE Suppliers ADD country VARCHAR(50);

-- MODIFY COLUMN
ALTER TABLE Customers MODIFY phone VARCHAR(30);
ALTER TABLE Products MODIFY stock INT DEFAULT 0;

-- RENAME COLUMN
ALTER TABLE Customers CHANGE COLUMN address shipping_address VARCHAR(255);

-- DROP COLUMN (uncomment to use)
-- ALTER TABLE Suppliers DROP COLUMN country;

-- INSERT DATA (Sample data for testing)
INSERT INTO Categories VALUES 
(1, 'Electronics', 'Electronic devices and accessories'),
(2, 'Clothing', 'Apparel and fashion items'),
(3, 'Books', 'Physical and digital books'),
(4, 'Home & Kitchen', 'Household items');

INSERT INTO Suppliers VALUES
(1, 'Tech Solutions Ltd', '123-456-7890', 'info@techsol.com'),
(2, 'Fashion Hub Inc', '098-765-4321', 'contact@fashionhub.com'),
(3, 'Book Distributors', '555-123-4567', 'books@dist.com');

INSERT INTO Products VALUES
(1, 'Laptop', 45000.00, 50, 1, 1),
(2, 'Smartphone', 25000.00, 100, 1, 1),
(3, 'T-Shirt', 500.00, 200, 2, 2),
(4, 'Jeans', 1500.00, 150, 2, 2),
(5, 'Database Systems Book', 800.00, 30, 3, 3);

INSERT INTO Customers (name, email, phone, shipping_address, password) VALUES
('Rahul Ahmed', 'rahul@email.com', '01711111111', 'Dhaka, Bangladesh', 'pass123'),
('Priya Das', 'priya@email.com', '01722222222', 'Chittagong, Bangladesh', 'pass456'),
('Kamal Hossain', 'kamal@email.com', '01733333333', 'Khulna, Bangladesh', 'pass789');

-- UPDATE DATA
UPDATE Products SET price = 44000.00 WHERE product_id = 1;
UPDATE Customers SET shipping_address = 'Dhaka, Mirpur' WHERE customer_id = 1;

-- DELETE DATA
DELETE FROM Products WHERE stock = 0;

-- SELECT with WHERE clause
SELECT * FROM Products WHERE price > 1000;
SELECT name, email FROM Customers WHERE shipping_address LIKE '%Dhaka%';

-- ========================================
-- LAB 03: CONSTRAINTS & SELECT OPERATIONS
-- ========================================

-- DISTINCT and ALL
SELECT DISTINCT category_id FROM Products;
SELECT ALL category_id FROM Products;

-- CALCULATIONS
SELECT name, price, price * 0.9 AS discounted_price FROM Products;
SELECT name, price, price * 0.9 AS "Price After 10% Discount" FROM Products;

-- RANGE SEARCH (BETWEEN)
SELECT * FROM Products WHERE price BETWEEN 500 AND 5000;
SELECT * FROM Products WHERE price NOT BETWEEN 10000 AND 50000;

-- SET MEMBERSHIP (IN)
SELECT * FROM Products WHERE category_id IN (1, 2);
SELECT * FROM Customers WHERE customer_id NOT IN (1, 2);

-- ORDER BY
SELECT * FROM Products ORDER BY price;
SELECT * FROM Products ORDER BY price DESC;
SELECT * FROM Products ORDER BY category_id, price DESC;


-- =============================
-- File: admin_aggregates.php
-- =============================
-- 1) Minimum product price with GROUP BY
SELECT MIN(price) AS cheapest_product_price, name 
FROM Products 
GROUP BY name 
ORDER BY cheapest_product_price ASC 
LIMIT 1

-- 2) Maximum single order value per customer
SELECT o.customer_id, MAX(oi.quantity * oi.price) AS max_order_value
FROM Orders o
JOIN Order_Items oi ON o.order_id = oi.order_id
WHERE o.payment_status = 'Done'
GROUP BY o.customer_id
ORDER BY max_order_value DESC
LIMIT 1

-- 3) Average order total (Done payments) - Uses NVL function
SELECT AVG(NVL(total_amount, 0)) AS avg_order_amount
FROM Orders
WHERE payment_status = 'Done'

-- 4) Total number of customers using COUNT
SELECT COUNT(*) AS total_customers FROM Customers

-- 5) Products sold per category with multiple JOINs and GROUP BY
SELECT c.name AS category_name, COUNT(oi.product_id) AS total_products_sold
FROM Categories c
JOIN Products p ON c.category_id = p.category_id
JOIN Order_Items oi ON p.product_id = oi.product_id
JOIN Orders o ON oi.order_id = o.order_id
WHERE o.payment_status = 'Done'
GROUP BY c.category_id, c.name
ORDER BY total_products_sold DESC

-- 6) Order statistics per customer (MIN, MAX, AVG aggregates)
SELECT 
    o.customer_id,
    MIN(oi.quantity * oi.price) AS min_order_value,
    MAX(oi.quantity * oi.price) AS max_order_value,
    AVG(oi.quantity * oi.price) AS avg_order_value
FROM Orders o
JOIN Order_Items oi ON o.order_id = oi.order_id
WHERE o.payment_status = 'Done'
GROUP BY o.customer_id
ORDER BY avg_order_value DESC

-- 7) Average orders per customer (subquery with AVG of COUNT)
SELECT AVG(order_count) AS avg_orders_per_customer
FROM (
    SELECT customer_id, COUNT(*) AS order_count
    FROM Orders
    WHERE payment_status = 'Done'
    GROUP BY customer_id
) AS sub


-- =============================
-- File: all_reviews.php
-- =============================
-- Fetch all reviews with product and customer info using DISTINCT and INNER JOIN
SELECT DISTINCT
    r.review_id,
    r.rating,
    r.comment,
    r.customer_id,
    c.name AS customer_name,
    r.product_id,
    p.name AS product_name
FROM Reviews r
INNER JOIN Customers c ON r.customer_id = c.customer_id
INNER JOIN Products p ON r.product_id = p.product_id
ORDER BY r.review_id DESC


-- =============================
-- File: all_orders.php
-- =============================
-- Query 1: Detailed orders with customer and product information (includes subquery for total order amount)
SELECT 
    o.order_id,
    o.order_date,
    o.payment_status AS status,
    c.name AS customer_name,
    c.email AS customer_email,
    c.phone AS customer_phone,
    c.address AS customer_address,
    oi.product_id,
    p.name AS product_name,
    oi.quantity,
    oi.price,
    (oi.quantity * oi.price) AS total_price,
    (SELECT SUM(quantity * price) FROM Order_Items WHERE order_id = o.order_id) AS total_order_amount
FROM Orders o
JOIN Customers c ON o.customer_id = c.customer_id
JOIN Order_Items oi ON o.order_id = oi.order_id
JOIN Products p ON oi.product_id = p.product_id
ORDER BY o.order_date DESC, o.order_id DESC

-- Query 2: Orders summary by product (UNION of LEFT and RIGHT JOIN to show all products)
SELECT 
    p.name AS product_name,
    SUM(oi.quantity) AS total_quantity_ordered,
    SUM(oi.quantity * oi.price) AS total_revenue
FROM Order_Items oi
LEFT JOIN Products p ON oi.product_id = p.product_id
GROUP BY p.product_id, p.name
HAVING total_quantity_ordered > 0

UNION

SELECT 
    p.name AS product_name,
    SUM(oi.quantity) AS total_quantity_ordered,
    SUM(oi.quantity * oi.price) AS total_revenue
FROM Order_Items oi
RIGHT JOIN Products p ON oi.product_id = p.product_id
GROUP BY p.product_id, p.name
HAVING total_quantity_ordered > 0
ORDER BY total_quantity_ordered DESC

-- Query 3: Natural Join - Orders with customer names
SELECT o.order_id, o.order_date, c.name AS customer_name
FROM Orders o
NATURAL JOIN Customers c
ORDER BY o.order_date DESC


-- =============================
-- File: categories.php
-- =============================
-- Check existing ID
SELECT * FROM Categories WHERE category_id=$id

-- Insert category
INSERT INTO Categories (category_id, name, description) VALUES ($id, '$name', '$description')

-- Update category
UPDATE Categories SET name='$name', description='$description' WHERE category_id=$id

-- Delete category
DELETE FROM Categories WHERE category_id=$id

-- Fetch all categories
SELECT * FROM Categories ORDER BY category_id


-- =============================
-- File: suppliers.php
-- =============================
-- Check existing ID
SELECT * FROM Suppliers WHERE supplier_id=$id

-- Insert supplier
INSERT INTO Suppliers (supplier_id, name, contact, email) VALUES ($id, '$name', '$contact', '$email')

-- Update supplier
UPDATE Suppliers SET name='$name', contact='$contact', email='$email' WHERE supplier_id=$id

-- Delete supplier
DELETE FROM Suppliers WHERE supplier_id=$id

-- Fetch all suppliers
SELECT * FROM Suppliers ORDER BY supplier_id


-- =============================
-- File: products.php
-- =============================
-- Check existing product_id before insert
SELECT * FROM Products WHERE product_id=$id

-- Insert product with manual ID
INSERT INTO Products (product_id, name, price, stock, category_id, supplier_id)
VALUES ($id, '$name', $price, $stock, $category_id, $supplier_id)

-- Create/replace view for updatable products
CREATE OR REPLACE VIEW view_products AS
SELECT product_id, name, price, stock, category_id, supplier_id
FROM Products

-- Update product via view
UPDATE view_products
SET name='$name', price=$price, stock=$stock, category_id=$category_id, supplier_id=$supplier_id
WHERE product_id=$id

-- Delete product
DELETE FROM Products WHERE product_id=$id

-- Fetch products with category and supplier (base query)
SELECT p.*, c.name AS category_name, s.name AS supplier_name 
FROM Products p
JOIN Categories c ON p.category_id=c.category_id
JOIN Suppliers s ON p.supplier_id=s.supplier_id
ORDER BY product_id

-- Category-based filtering with IN operator (include selected categories)
SELECT p.*, c.name AS category_name, s.name AS supplier_name 
FROM Products p
JOIN Categories c ON p.category_id=c.category_id
JOIN Suppliers s ON p.supplier_id=s.supplier_id
WHERE p.category_id IN ($category_ids)
ORDER BY product_id

-- Category-based filtering with NOT IN operator (exclude selected categories)
SELECT p.*, c.name AS category_name, s.name AS supplier_name 
FROM Products p
JOIN Categories c ON p.category_id=c.category_id
JOIN Suppliers s ON p.supplier_id=s.supplier_id
WHERE p.category_id NOT IN ($category_ids)
ORDER BY product_id

-- Fetch categories for dropdown
SELECT * FROM Categories ORDER BY name

-- Fetch suppliers for dropdown
SELECT * FROM Suppliers ORDER BY name


-- =============================
-- File: Rawquery.php
-- =============================
-- Table viewer (dynamic based on selection)
SELECT * FROM [selected_table]

-- Custom queries executed verbatim from user input in the page UI


-- =============================
-- File: showuser.php
-- =============================
-- Fetch all customers
SELECT customer_id, name, email, phone, address FROM Customers ORDER BY customer_id


-- =============================
-- File: customer_loyalty.php
-- =============================
-- Total spent and MOD-based loyalty tiers
SELECT 
    c.customer_id,
    c.name AS customer_name,
    c.email,
    COALESCE(SUM(oi.quantity * oi.price),0) AS total_spent,
    MOD(c.customer_id, 3) AS loyalty_tier_num,
    CASE MOD(c.customer_id, 3)
        WHEN 0 THEN 'Gold'
        WHEN 1 THEN 'Silver'
        ELSE 'Bronze'
    END AS loyalty_tier
FROM Customers c
LEFT JOIN Orders o ON c.customer_id = o.customer_id AND o.payment_status='Done'
LEFT JOIN Order_Items oi ON o.order_id = oi.order_id
GROUP BY c.customer_id, c.name, c.email
ORDER BY total_spent DESC


-- =============================
-- File: top_customer.php
-- =============================
-- Subquery + HAVING for high-spend customers (uses $threshold)
SELECT 
    c.customer_id,
    c.name AS customer_name,
    c.email,
    totals.total_spent
FROM Customers c
JOIN (
    SELECT 
        o.customer_id,
        SUM(oi.quantity * oi.price) AS total_spent
    FROM Orders o
    JOIN Order_Items oi ON o.order_id = oi.order_id
    WHERE o.payment_status = 'Done'
    GROUP BY o.customer_id
    HAVING total_spent > $threshold
) AS totals ON c.customer_id = totals.customer_id
ORDER BY totals.total_spent DESC


-- =============================
-- File: top_order.php
-- =============================
-- Top products using EXISTS and HAVING (uses $min_quantity)
SELECT 
    product_id,
    (SELECT name FROM Products p WHERE p.product_id = oi.product_id) AS product_name,
    SUM(quantity) AS total_quantity,
    SUM(quantity * price) AS total_revenue
FROM Order_Items oi
WHERE EXISTS (
    SELECT 1 
    FROM Orders o 
    WHERE o.order_id = oi.order_id AND o.payment_status='Done'
)
GROUP BY product_id
HAVING total_quantity > $min_quantity
ORDER BY total_quantity DESC


-- =============================
-- File: order_with_previous_order.php
-- =============================
-- Self-join to show previous order per customer
SELECT 
    o1.order_id AS current_order_id,
    o1.order_date AS current_order_date,
    o1.payment_status AS status,
    c.name AS customer_name,
    c.email AS customer_email,
    c.phone AS customer_phone,
    o2.order_id AS previous_order_id,
    o2.order_date AS previous_order_date
FROM Orders o1
LEFT JOIN Orders o2
    ON o1.customer_id = o2.customer_id
   AND o2.order_date < o1.order_date
JOIN Customers c ON o1.customer_id = c.customer_id
ORDER BY o1.customer_id, o1.order_date


-- =============================
-- File: customer_supplier_insights.php
-- =============================
-- Query 1: Customers who are also Suppliers (INTERSECT simulation using IN)
SELECT c.customer_id, c.name, c.email, c.phone, c.address
FROM Customers c
WHERE c.email IN (SELECT email FROM Suppliers)
ORDER BY c.name

-- Query 2: Customers who are NOT Suppliers (MINUS simulation using NOT IN)
SELECT c.customer_id, c.name, c.email, c.phone, c.address
FROM Customers c
WHERE c.email NOT IN (SELECT email FROM Suppliers)
ORDER BY c.name


-- =============================
-- File: product_insights.php
-- =============================
-- Query 1: Product with Category Name using USING clause
SELECT p.name AS product_name, c.name AS category_name
FROM Products p
JOIN Categories c USING(category_id)
ORDER BY c.name, p.name

-- Query 2: Products cheaper than category average (CTE - Common Table Expression)
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

-- Query 3: Products in the same category (SELF JOIN)
SELECT p1.name AS product1, p2.name AS product2, p1.category_id
FROM Products p1
JOIN Products p2 ON p1.category_id = p2.category_id
WHERE p1.product_id < p2.product_id
ORDER BY p1.category_id


-- =============================
-- File: delete_view.php
-- =============================
-- Drop view if exists
DROP VIEW IF EXISTS view_products
-- Create admin account (PDO prepared statement)
INSERT INTO admins (email, password) VALUES (?, ?)


-- =============================
-- File: login.php (admin)
-- =============================
-- Fetch admin by email (PDO prepared)
SELECT id, email, password FROM admins WHERE email = ?


-- =============================
-- File: users/dashboard.php
-- =============================
-- Fetch current customer details
SELECT name, email, phone, address FROM Customers WHERE customer_id = $customer_id


-- =============================
-- File: users/editProfile.php
-- =============================
-- Fetch current info (prepared)
SELECT name, email, phone, address FROM Customers WHERE customer_id = ?

-- Email uniqueness check (prepared)
SELECT customer_id FROM Customers WHERE email=? AND customer_id != ?

-- Update with password (prepared)
UPDATE Customers SET name=?, email=?, phone=?, address=?, password=? WHERE customer_id=?

-- Update without password (prepared)
UPDATE Customers SET name=?, email=?, phone=?, address=? WHERE customer_id=?


-- =============================
-- File: users/login.php
-- =============================
-- Customer login (dynamic)
SELECT * FROM Customers WHERE email='$email' AND password='$password'


-- =============================
-- File: users/registration.php
-- =============================
-- Email existence check
SELECT * FROM Customers WHERE email='$email'

-- Insert new customer
INSERT INTO Customers (name, email, phone, address, password)
VALUES ('$name', '$email', '$phone', '$address', '$password')


-- =============================
-- File: users/create_order.php
-- =============================
-- Create/replace view for available products with category name
CREATE OR REPLACE VIEW AvailableProducts AS
SELECT 
    p.product_id, 
    p.name, 
    p.price, 
    p.stock, 
    c.name AS category_name
FROM Products p
JOIN Categories c ON p.category_id = c.category_id
WHERE p.stock > 0

-- Fetch product details
SELECT name, price, stock FROM Products WHERE product_id = $product_id

-- Insert order
INSERT INTO Orders (customer_id, total_amount) VALUES ($customer_id, $total_amount)

-- Insert order item
INSERT INTO Order_Items (order_id, product_id, quantity, price) 
VALUES ($order_id, $product_id, $quantity, $price)

-- Decrement stock
UPDATE Products SET stock = stock - $quantity WHERE product_id = $product_id

-- Searchable products (base)
SELECT * FROM AvailableProducts WHERE 1=1

-- Optional filter appended when searching by name
-- AND TRIM(name) COLLATE utf8mb4_general_ci LIKE '%$search_name%'


-- =============================
-- File: users/make_order_v2.php
-- =============================
-- Create/replace view for available products
CREATE OR REPLACE VIEW AvailableProducts AS
SELECT product_id, name, price, stock
FROM Products
WHERE stock > 0

-- Fetch product details
SELECT name, price, stock FROM Products WHERE product_id = $product_id

-- Insert order
INSERT INTO Orders (customer_id, total_amount) VALUES ($customer_id, $total_amount)

-- Insert order item
INSERT INTO Order_Items (order_id, product_id, quantity, price) 
VALUES ($order_id, $product_id, $quantity, $price)

-- Decrement stock
UPDATE Products SET stock = stock - $quantity WHERE product_id = $product_id

-- Fetch available products
SELECT * FROM AvailableProducts


-- =============================
-- File: users/my_orders.php
-- =============================
-- Compute total amount for order (prepared statement)
SELECT SUM(quantity * price) AS total_amount FROM Order_Items WHERE order_id=?

-- Mark order as paid (prepared statement with UPDATE)
UPDATE Orders SET payment_status='Done' WHERE order_id=? AND customer_id=?

-- Insert payment record (prepared statement)
INSERT INTO Payments (order_id, payment_method, amount) VALUES (?, ?, ?)

-- Fetch items for stock restore (prepared statement)
SELECT product_id, quantity FROM Order_Items WHERE order_id=?

-- Restore stock when order deleted (prepared statement)
UPDATE Products SET stock = stock + ? WHERE product_id=?

-- Delete order items (prepared statement)
DELETE FROM Order_Items WHERE order_id=?

-- Delete unpaid order only (prepared statement)
DELETE FROM Orders WHERE order_id=? AND payment_status != 'Done'

-- Orders listing with CTE (Common Table Expression) and multiple JOINs
WITH OrderDetails AS (
    SELECT 
        o.order_id,
        o.customer_id,
        o.order_date,
        o.payment_status,
        oi.product_id,
        oi.quantity,
        oi.price,
        (oi.quantity * oi.price) AS total_price
    FROM Orders o
    JOIN Order_Items oi ON o.order_id = oi.order_id
)
SELECT 
    od.order_id,
    od.customer_id,
    od.order_date,
    od.payment_status,
    od.product_id,
    p.name AS product_name,
    od.quantity,
    od.price,
    od.total_price
FROM OrderDetails od
JOIN Products p ON od.product_id = p.product_id
WHERE od.customer_id = $customer_id
ORDER BY od.order_date DESC, od.order_id DESC


-- =============================
-- File: users/make_review.php
-- =============================
-- Insert review (prepared statement with 4 parameters)
INSERT INTO Reviews (customer_id, product_id, rating, comment) VALUES (?, ?, ?, ?)

-- Fetch purchased products using CROSS JOIN
SELECT p.product_id, p.name AS product_name, p.price, p.stock,
       o.order_id, o.order_date
FROM Orders o
CROSS JOIN Order_Items oi
CROSS JOIN Products p
WHERE o.order_id = oi.order_id
  AND oi.product_id = p.product_id
  AND o.customer_id = ?
  AND o.payment_status = 'Done'
ORDER BY o.order_date DESC, p.name

-- Fetch customer's submitted reviews
SELECT r.review_id, r.product_id, p.name AS product_name, r.rating, r.comment
FROM Reviews r
INNER JOIN Products p ON r.product_id = p.product_id
WHERE r.customer_id = ?
ORDER BY r.review_id DESC


-- =============================
-- File: users/registration.php
-- =============================
-- Check if email already exists
SELECT * FROM Customers WHERE email='$email'

-- Insert new customer with REGEXP_SUBSTR usage in comment
-- REGEXP_SUBSTR example for email domain extraction:
-- SELECT REGEXP_SUBSTR(email, '@(.+)', 1, 1, NULL, 1) AS domain FROM Customers

-- Actual insert query
INSERT INTO Customers (name, email, phone, address, password)
VALUES ('$name', '$email', '$phone', '$address', '$password')


-- ========================================
-- SQL OPERATORS & TECHNIQUES SUMMARY
-- ========================================
-- This project demonstrates the following SQL concepts:

-- 1. BASIC OPERATIONS:
--    - SELECT, INSERT, UPDATE, DELETE
--    - WHERE, ORDER BY, LIMIT

-- 2. JOINS:
--    - INNER JOIN / JOIN
--    - LEFT JOIN
--    - RIGHT JOIN
--    - NATURAL JOIN (all_orders.php)
--    - SELF JOIN (order_with_previous_order.php, product_insights.php)
--    - CROSS JOIN (users/make_review.php)

-- 3. SET OPERATIONS:
--    - UNION (all_orders.php)
--    - IN / NOT IN (products.php, customer_supplier_insights.php)

-- 4. AGGREGATE FUNCTIONS:
--    - COUNT, SUM, AVG, MIN, MAX (admin_aggregates.php)
--    - GROUP BY, HAVING (top_customer.php, top_order.php)

-- 5. SUBQUERIES:
--    - Scalar subqueries (all_orders.php)
--    - Derived tables (top_customer.php)
--    - Correlated subqueries (top_order.php with EXISTS)

-- 6. ADVANCED FEATURES:
--    - CTE (Common Table Expressions): users/my_orders.php, product_insights.php
--    - Views: CREATE OR REPLACE VIEW (products.php, users/create_order.php)
--    - CASE expressions (customer_loyalty.php)
--    - MOD function (customer_loyalty.php)
--    - NVL function (admin_aggregates.php)
--    - COALESCE (customer_loyalty.php)
--    - DISTINCT (all_reviews.php)
--    - EXISTS (top_order.php)
--    - USING clause (product_insights.php)

-- 7. PREPARED STATEMENTS:
--    - PDO prepared statements with ? placeholders (login.php, users/my_orders.php)
--    - MySQLi prepared statements (users/editProfile.php)

-- ========================================
-- END OF SQL QUERIES COLLECTION
-- ========================================
-- Total Files Documented: 22
-- Last Updated: October 25, 2025
-- ========================================
