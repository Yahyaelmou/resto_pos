-- CosyPOS Database (import via phpMyAdmin)
CREATE DATABASE IF NOT EXISTS cosypos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cosypos;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    role VARCHAR(20) NOT NULL
);

INSERT IGNORE INTO users (id, name, role) VALUES
(1, 'Alice', 'waiter'),
(2, 'Bob', 'manager');

-- Tables (restaurant tables)
CREATE TABLE IF NOT EXISTS tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL,
    status VARCHAR(20) DEFAULT 'available'
);

INSERT IGNORE INTO tables (id, name, status) VALUES
(1, 'Table 1', 'available'),
(2, 'Table 2', 'available'),
(3, 'Table 3', 'available'),
(4, 'Table 4', 'available');

-- Categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

INSERT IGNORE INTO categories (id, name) VALUES
(1, 'Breakfast'),
(2, 'Soups'),
(3, 'Main Course'),
(4, 'Desserts'),
(5, 'Drinks');

-- Menu Items
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_id INT NOT NULL,
    price DECIMAL(8,2) NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

INSERT IGNORE INTO menu_items (id, name, category_id, price) VALUES
(1, 'Pancakes', 1, 5.00),
(2, 'Omelette', 1, 4.50),
(3, 'Tomato Soup', 2, 3.75),
(4, 'Chicken Soup', 2, 4.25),
(5, 'Fish and Chips', 3, 7.50),
(6, 'Grilled Chicken', 3, 8.75),
(7, 'Chocolate Cake', 4, 3.00),
(8, 'Ice Cream', 4, 2.50),
(9, 'Coffee', 5, 2.00),
(10, 'Orange Juice', 5, 2.25);

-- Orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_id INT NOT NULL,
    user_id INT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(30),
    total DECIMAL(10,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (table_id) REFERENCES tables(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order Items
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);

-- Reservations
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(80) NOT NULL,
    table_id INT NOT NULL,
    guests INT NOT NULL,
    datetime DATETIME NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    FOREIGN KEY (table_id) REFERENCES tables(id)
);

-- Settings (for e.g. tax)
CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(50) PRIMARY KEY,
    `value` VARCHAR(80) NOT NULL
);

INSERT IGNORE INTO settings (`key`, `value`) VALUES
('tax_rate', '0.10');