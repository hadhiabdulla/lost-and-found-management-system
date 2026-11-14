-- Lost and Found Management System - Database Schema
-- DBMS Lab Mini Project

CREATE DATABASE IF NOT EXISTS lost_found_db;
USE lost_found_db;

-- Users table (admins and regular users)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories for items
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

-- Lost items reported by users
CREATE TABLE lost_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    category_id INT,
    location_lost VARCHAR(255),
    date_lost DATE,
    image_path VARCHAR(255),
    status ENUM('open', 'resolved', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Found items reported by users
CREATE TABLE found_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    category_id INT,
    location_found VARCHAR(255),
    date_found DATE,
    image_path VARCHAR(255),
    status ENUM('available', 'claimed', 'disposed') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Claims: when a user claims a found item matches their lost item
CREATE TABLE claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lost_item_id INT,
    found_item_id INT NOT NULL,
    claimant_id INT NOT NULL,
    proof_description TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (lost_item_id) REFERENCES lost_items(id) ON DELETE SET NULL,
    FOREIGN KEY (found_item_id) REFERENCES found_items(id) ON DELETE CASCADE,
    FOREIGN KEY (claimant_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Match suggestions (auto-matched lost & found items)
CREATE TABLE matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lost_item_id INT NOT NULL,
    found_item_id INT NOT NULL,
    match_score DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('suggested', 'confirmed', 'dismissed') DEFAULT 'suggested',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lost_item_id) REFERENCES lost_items(id) ON DELETE CASCADE,
    FOREIGN KEY (found_item_id) REFERENCES found_items(id) ON DELETE CASCADE
);

-- Notifications for users
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    type ENUM('match', 'claim', 'general') DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Audit log for admin tracking
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    ip_address VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Default categories
INSERT INTO categories (name, description) VALUES
('Electronics', 'Phones, laptops, tablets, chargers, earphones etc.'),
('Clothing', 'Shirts, jackets, bags, shoes etc.'),
('Accessories', 'Watches, jewellery, glasses, keys etc.'),
('Documents', 'ID cards, passports, certificates, books etc.'),
('Wallets & Bags', 'Purses, wallets, backpacks, handbags etc.'),
('Sports Equipment', 'Balls, rackets, helmets, gear etc.'),
('Other', 'Miscellaneous items');

-- No default admin user is seeded (a shipped default password would be public knowledge).
-- After importing this schema, register an account through the app, then promote it:
--   UPDATE users SET role = 'admin' WHERE email = 'you@example.com';
-- Use a strong, unique password when registering.
