-- Online Auction System Database

CREATE DATABASE IF NOT EXISTS online_auction_system;
USE online_auction_system;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    balance DECIMAL(10, 2) DEFAULT 0.00,
    rating DECIMAL(3, 2) DEFAULT 0.00,
    total_ratings INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Auctions table
CREATE TABLE auctions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    starting_price DECIMAL(10, 2) NOT NULL,
    current_price DECIMAL(10, 2) NOT NULL,
    reserve_price DECIMAL(10, 2),
    buy_now_price DECIMAL(10, 2),
    image VARCHAR(255),
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    status ENUM('pending', 'active', 'ended', 'cancelled') DEFAULT 'pending',
    total_bids INT DEFAULT 0,
    winner_id INT,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (winner_id) REFERENCES users(id),
    INDEX idx_status (status),
    INDEX idx_end_time (end_time),
    INDEX idx_seller (seller_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bids table
CREATE TABLE bids (
    id INT PRIMARY KEY AUTO_INCREMENT,
    auction_id INT NOT NULL,
    bidder_id INT NOT NULL,
    bid_amount DECIMAL(10, 2) NOT NULL,
    bid_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_auto_bid BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (auction_id) REFERENCES auctions(id) ON DELETE CASCADE,
    FOREIGN KEY (bidder_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_auction (auction_id),
    INDEX idx_bidder (bidder_id),
    INDEX idx_bid_time (bid_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Watchlist table
CREATE TABLE watchlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    auction_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (auction_id) REFERENCES auctions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_watchlist (user_id, auction_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Messages table
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    auction_id INT,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (auction_id) REFERENCES auctions(id) ON DELETE SET NULL,
    INDEX idx_receiver (receiver_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('bid_placed', 'bid_outbid', 'auction_won', 'auction_ended', 'message', 'payment') NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data

-- Insert categories
INSERT INTO categories (name, description, icon) VALUES
('Electronics', 'Smartphones, laptops, gadgets and more', '💻'),
('Collectibles', 'Rare items, antiques, and memorabilia', '🏺'),
('Fashion', 'Clothing, accessories, and jewelry', '👗'),
('Art', 'Paintings, sculptures, and artwork', '🎨'),
('Vehicles', 'Cars, motorcycles, and boats', '🚗'),
('Home & Garden', 'Furniture, decor, and tools', '🏡'),
('Sports', 'Equipment, memorabilia, and gear', '⚽'),
('Books', 'Rare books, manuscripts, and comics', '📚');

-- Insert sample users (password: password123)
INSERT INTO users (username, email, password, full_name, phone, balance, rating, total_ratings) VALUES
('john_seller', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', '+1234567890', 1000.00, 4.5, 25),
('jane_bidder', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', '+1234567891', 2500.00, 4.8, 40),
('mike_collector', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike Johnson', '+1234567892', 5000.00, 4.7, 35),
('sarah_trader', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Williams', '+1234567893', 1500.00, 4.6, 20);

-- Insert sample auctions
INSERT INTO auctions (seller_id, category_id, title, description, starting_price, current_price, reserve_price, buy_now_price, start_time, end_time, status, total_bids, views) VALUES
(1, 1, 'iPhone 15 Pro Max 256GB', 'Brand new sealed iPhone 15 Pro Max in Titanium Blue. Never opened, full warranty included.', 800.00, 950.00, 1000.00, 1200.00, '2026-01-01 10:00:00', '2026-01-05 18:00:00', 'active', 8, 245),
(1, 1, 'MacBook Pro M3 16" Laptop', 'Excellent condition MacBook Pro with M3 chip, 32GB RAM, 1TB SSD. Used for 6 months only.', 1500.00, 1750.00, 2000.00, 2500.00, '2026-01-02 09:00:00', '2026-01-06 20:00:00', 'active', 5, 189),
(2, 2, 'Vintage Rolex Submariner 1960s', 'Authentic vintage Rolex Submariner from the 1960s. Excellent condition with papers.', 5000.00, 6500.00, 8000.00, NULL, '2026-01-01 12:00:00', '2026-01-07 12:00:00', 'active', 12, 456),
(2, 4, 'Original Picasso Sketch', 'Authenticated original sketch by Pablo Picasso. Comes with certificate of authenticity.', 10000.00, 12000.00, 15000.00, NULL, '2026-01-02 14:00:00', '2026-01-10 18:00:00', 'active', 6, 678),
(3, 3, 'Designer Handbag Collection', 'Set of 5 authentic designer handbags from Louis Vuitton, Gucci, and Prada. All in excellent condition.', 2000.00, 2400.00, 3000.00, 3500.00, '2026-01-01 15:00:00', '2026-01-05 15:00:00', 'active', 9, 312),
(3, 5, '2020 Tesla Model 3 Performance', 'Tesla Model 3 Performance with autopilot. 25,000 miles, excellent condition, white exterior.', 30000.00, 32000.00, 35000.00, 40000.00, '2026-01-02 10:00:00', '2026-01-08 18:00:00', 'active', 4, 892),
(4, 6, 'Antique Dining Table Set', 'Beautiful solid oak dining table with 8 chairs. Circa 1920s, fully restored.', 800.00, 850.00, 1000.00, 1200.00, '2026-01-01 11:00:00', '2026-01-04 20:00:00', 'active', 3, 156),
(4, 7, 'Signed Michael Jordan Jersey', 'Authentic signed Chicago Bulls jersey from Michael Jordan. Comes with COA.', 1500.00, 1800.00, 2000.00, 2500.00, '2026-01-02 13:00:00', '2026-01-06 19:00:00', 'active', 7, 523);

-- Insert sample bids
INSERT INTO bids (auction_id, bidder_id, bid_amount, bid_time) VALUES
(1, 2, 850.00, '2026-01-01 11:30:00'),
(1, 3, 900.00, '2026-01-01 14:20:00'),
(1, 2, 950.00, '2026-01-02 09:15:00'),
(2, 3, 1600.00, '2026-01-02 10:30:00'),
(2, 4, 1750.00, '2026-01-02 15:45:00'),
(3, 1, 5500.00, '2026-01-01 13:00:00'),
(3, 4, 6000.00, '2026-01-01 18:30:00'),
(3, 1, 6500.00, '2026-01-02 11:00:00'),
(5, 4, 2200.00, '2026-01-01 16:30:00'),
(5, 1, 2400.00, '2026-01-02 10:00:00');

-- Insert sample watchlist
INSERT INTO watchlist (user_id, auction_id) VALUES
(2, 1), (2, 3), (2, 5),
(3, 2), (3, 4), (3, 6),
(4, 1), (4, 3), (4, 8);

-- Insert sample notifications
INSERT INTO notifications (user_id, type, title, message, link) VALUES
(2, 'bid_outbid', 'You have been outbid!', 'Your bid on "iPhone 15 Pro Max 256GB" has been outbid.', 'auction.php?id=1'),
(1, 'bid_placed', 'New bid received', 'Someone placed a bid on your "iPhone 15 Pro Max 256GB" auction.', 'auction.php?id=1'),
(3, 'auction_won', 'Congratulations!', 'You won the auction for "MacBook Pro M3 16" Laptop".', 'auction.php?id=2'),
(4, 'bid_outbid', 'You have been outbid!', 'Your bid on "Vintage Rolex Submariner 1960s" has been outbid.', 'auction.php?id=3');
