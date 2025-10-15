-- 1. Customers Table
CREATE TABLE Customers (
    customer_id INT  PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    password VARCHAR(255) NOT NULL
);

-- 2. Categories Table
CREATE TABLE Categories (
    category_id INT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

-- 3. Suppliers Table
CREATE TABLE Suppliers (
    supplier_id INT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact VARCHAR(50),
    email VARCHAR(100)
);

-- 4. Products Table
CREATE TABLE Products (
    product_id INT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL,
    category_id INT NOT NULL,
    supplier_id INT NOT NULL,
    FOREIGN KEY (category_id) REFERENCES Categories(category_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES Suppliers(supplier_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);
-- Product Table
CREATE TABLE Product (
    product_id INT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL CHECK (price >= 0),
    stock INT NOT NULL,
    category_id INT NOT NULL,
    supplier_id INT NOT NULL,
    FOREIGN KEY (category_id) REFERENCES Categories(category_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES Suppliers(supplier_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);
-- 5. Orders Table
CREATE TABLE Orders (
    order_id INT  PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2),
    payment_status VARCHAR(50) DEFAULT 'Pending',
    FOREIGN KEY (customer_id) REFERENCES Customers(customer_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- 6. Order_Items Table
CREATE TABLE Order_Items (
    order_item_id INT  PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES Orders(order_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Products(product_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- 7. Payments Table
CREATE TABLE Payments (
    payment_id INT  PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    payment_method VARCHAR(50),
    amount DECIMAL(10,2),
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES Orders(order_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- 8. Reviews Table (optional)
CREATE TABLE Reviews (
    review_id INT  PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    FOREIGN KEY (customer_id) REFERENCES Customers(customer_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Products(product_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- 9. Admin table
CREATE TABLE Admins (
    admin_id INT AUTOINCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL
    password VARCHAR(255) NOT NULL,
    
);




// Use DBML to define your database structure
// Docs: https://dbml.dbdiagram.io/docs

Table Customers {
  customer_id int [pk]
  name varchar(100) [not null]
  email varchar(100) [not null, unique]
  phone varchar(20)
  address varchar(255)
  password varchar(255) [not null]
}

Table Orders {
  order_id int [pk]
  customer_id int [not null, ref: > Customers.customer_id]
  order_date datetime [default: `CURRENT_TIMESTAMP`]
  total_amount decimal(10,2)
}

Table Payments {
  payment_id int [pk]
  order_id int [not null, ref: > Orders.order_id]
  payment_method varchar(50)
  amount decimal(10,2)
  payment_date datetime [default: `CURRENT_TIMESTAMP`]
}

Table Order_Items {
  order_item_id int [pk]
  order_id int [not null, ref: > Orders.order_id]
  product_id int [not null, ref: > Products.product_id]
  quantity int [not null]
  price decimal(10,2) [not null]
}

Table Products {
  product_id int [pk]
  name varchar(100) [not null]
  price decimal(10,2) [not null]
  stock int [not null]
  category_id int [not null, ref: > Categories.category_id]
  supplier_id int [not null, ref: > Suppliers.supplier_id]
}

Table Suppliers {
  supplier_id int [pk]
  name varchar(100) [not null]
  phone varchar(50)
  email varchar(100)
}

Table Reviews {
  review_id int [pk]
  customer_id int [not null, ref: > Customers.customer_id]
  product_id int [not null, ref: > Products.product_id]
  rating int [note: '1-5 rating']
  comment text
}

Table Categories {
  category_id int [pk]
  name varchar(100) [not null]
  description text
}

Table Admins {
  admin_id int [pk, increment]
  email varchar(100) [not null, unique]
  password varchar(255) [not null]
}
