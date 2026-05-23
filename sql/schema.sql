
CREATE DATABASE IF NOT EXISTS shelton_hire;
USE shelton_hire;
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS tools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    hourly_price DECIMAL(10, 2) NOT NULL,
    daily_price DECIMAL(10, 2) NOT NULL,
    weekly_price DECIMAL(10, 2) NOT NULL,
    availability_status ENUM('Available', 'Rented', 'Maintenance') DEFAULT 'Available',
    featured BOOLEAN DEFAULT FALSE,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tool_id INT,
    user_id INT,
    overall_rating DECIMAL(3, 2),
    comment TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tool_id) REFERENCES tools(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS review_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT,
    criterion VARCHAR(50) NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS review_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT,
    user_id INT,
    parent_comment_id INT NULL,
    comment TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_comment_id) REFERENCES review_comments(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS moderator_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action_type ENUM('approve', 'reject') NOT NULL,
    target_type ENUM('review', 'comment') NOT NULL,
    target_id INT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS rentals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tool_id INT,
    user_id INT,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    total_cost DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tool_id) REFERENCES tools(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
INSERT INTO categories (name, description) VALUES
('Power Tools', 'High-performance electric and cordless tools for all trades.'),
('Access Equipment', 'Ladders, scaffolding, and powered access platforms.'),
('Cleaning Equipment', 'Industrial vacuums, floor polishers, and pressure washers.'),
('Landscaping & Gardening', 'Mowers, shredders, and heavy-duty garden machinery.');
INSERT INTO tools (category_id, name, description, hourly_price, daily_price, weekly_price, image_path) VALUES
(1, 'DeWalt Cordless Drill', 'High-torque drill with two 5Ah batteries.', 5.00, 25.00, 75.00, 'drill.jpg'),
(2, 'Aluminium Extension Ladder', '3.5m reach, lightweight and sturdy.', 4.00, 20.00, 60.00, 'ladder.jpg'),
(4, 'Petrol Lawn Mower', 'Self-propelled with large collection bag.', 10.00, 50.00, 150.00, 'mower.jpg');
INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@shelton-hire.com', '$2y$10$oVYEsRBgaq0jJkDPJ6m7MuhC7l.aNBKg4svMLH/SFi51HrsSGIdQ6', 'admin');
INSERT INTO users (username, email, password_hash, role) VALUES
('customer', 'customer@example.com', '$2y$10$oVYEsRBgaq0jJkDPJ6m7MuhC7l.aNBKg4svMLH/SFi51HrsSGIdQ6', 'customer');
