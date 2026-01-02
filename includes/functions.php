<?php
require_once 'config.php';

// Authentication Functions
function register($username, $email, $password, $full_name, $phone = '', $address = '', $role = 'user') {
    global $pdo;
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $address, $role]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function login($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Auction Query Functions
function getActiveAuctions($category_id = null, $search = null, $sort = 'ending_soon', $limit = 20, $offset = 0) {
    global $pdo;
    
    $sql = "SELECT a.*, c.name as category_name, u.username as seller_name, 
            (SELECT COUNT(*) FROM bids WHERE auction_id = a.id) as bid_count
            FROM auctions a
            LEFT JOIN categories c ON a.category_id = c.id
            LEFT JOIN users u ON a.seller_id = u.id
            WHERE a.status = 'active'";
    
    $params = [];
    
    if ($category_id) {
        $sql .= " AND a.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($search) {
        $sql .= " AND (a.title LIKE ? OR a.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    switch ($sort) {
        case 'ending_soon':
            $sql .= " ORDER BY a.end_time ASC";
            break;
        case 'newest':
            $sql .= " ORDER BY a.created_at DESC";
            break;
        case 'price_low':
            $sql .= " ORDER BY a.current_price ASC";
            break;
        case 'price_high':
            $sql .= " ORDER BY a.current_price DESC";
            break;
        case 'most_bids':
            $sql .= " ORDER BY bid_count DESC";
            break;
    }
    
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getAuctionById($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT a.*, c.name as category_name, u.username as seller_name, u.id as seller_id,
                          (SELECT COUNT(*) FROM bids WHERE auction_id = a.id) as bid_count,
                          (SELECT username FROM users WHERE id = (SELECT bidder_id FROM bids WHERE auction_id = a.id ORDER BY bid_amount DESC LIMIT 1)) as high_bidder
                          FROM auctions a
                          LEFT JOIN categories c ON a.category_id = c.id
                          LEFT JOIN users u ON a.seller_id = u.id
                          WHERE a.id = ?");
    $stmt->execute([$id]);
    
    // Increment view count
    $update = $pdo->prepare("UPDATE auctions SET views = views + 1 WHERE id = ?");
    $update->execute([$id]);
    
    return $stmt->fetch();
}

function getAuctionBids($auction_id, $limit = 50) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT b.*, u.username FROM bids b
                          JOIN users u ON b.bidder_id = u.id
                          WHERE b.auction_id = ?
                          ORDER BY b.bid_amount DESC, b.bid_time DESC
                          LIMIT ?");
    $stmt->execute([$auction_id, $limit]);
    return $stmt->fetchAll();
}

function getUserAuctions($user_id, $status = null) {
    global $pdo;
    
    $sql = "SELECT a.*, c.name as category_name,
            (SELECT COUNT(*) FROM bids WHERE auction_id = a.id) as bid_count
            FROM auctions a
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.seller_id = ?";
    
    $params = [$user_id];
    
    if ($status) {
        $sql .= " AND a.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY a.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getUserBids($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT b.*, a.title, a.status, a.current_price, a.end_time, a.winner_id,
                          CASE 
                              WHEN a.winner_id = ? AND a.status = 'ended' THEN 'won'
                              WHEN a.winner_id != ? AND a.status = 'ended' THEN 'lost'
                              WHEN b.bid_amount = (SELECT MAX(bid_amount) FROM bids WHERE auction_id = a.id) THEN 'winning'
                              ELSE 'outbid'
                          END as bid_status
                          FROM bids b
                          JOIN auctions a ON b.auction_id = a.id
                          WHERE b.bidder_id = ?
                          ORDER BY b.bid_time DESC");
    $stmt->execute([$user_id, $user_id, $user_id]);
    return $stmt->fetchAll();
}

// Bidding Functions
function placeBid($auction_id, $user_id, $bid_amount) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Get auction details
        $auction = getAuctionById($auction_id);
        
        if (!$auction) {
            throw new Exception("Auction not found");
        }
        
        if ($auction['status'] != 'active') {
            throw new Exception("Auction is not active");
        }
        
        if ($auction['seller_id'] == $user_id) {
            throw new Exception("You cannot bid on your own auction");
        }
        
        $min_bid = $auction['current_price'] + MIN_BID_INCREMENT;
        if ($bid_amount < $min_bid) {
            throw new Exception("Minimum bid is $" . number_format($min_bid, 2));
        }
        
        // Check user balance
        $user = getCurrentUser();
        if ($user['balance'] < $bid_amount) {
            throw new Exception("Insufficient balance");
        }
        
        // Place bid
        $stmt = $pdo->prepare("INSERT INTO bids (auction_id, bidder_id, bid_amount) VALUES (?, ?, ?)");
        $stmt->execute([$auction_id, $user_id, $bid_amount]);
        
        // Update auction current price
        $stmt = $pdo->prepare("UPDATE auctions SET current_price = ? WHERE id = ?");
        $stmt->execute([$bid_amount, $auction_id]);
        
        // Notify previous high bidder
        if ($auction['high_bidder']) {
            $prev_bidder = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $prev_bidder->execute([$auction['high_bidder']]);
            $prev_bidder_id = $prev_bidder->fetchColumn();
            
            if ($prev_bidder_id) {
                createNotification($prev_bidder_id, 'outbid', $auction_id, "You have been outbid on '{$auction['title']}'");
            }
        }
        
        // Extend auction if bid placed near end
        $time_remaining = strtotime($auction['end_time']) - time();
        if ($time_remaining > 0 && $time_remaining < AUCTION_EXTENSION_TIME) {
            $new_end = date('Y-m-d H:i:s', strtotime($auction['end_time']) + AUCTION_EXTENSION_TIME);
            $stmt = $pdo->prepare("UPDATE auctions SET end_time = ? WHERE id = ?");
            $stmt->execute([$new_end, $auction_id]);
        }
        
        $pdo->commit();
        return ['success' => true, 'message' => 'Bid placed successfully'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Time Functions
function getRemainingTime($end_time) {
    $end = strtotime($end_time);
    $now = time();
    $diff = $end - $now;
    
    if ($diff <= 0) {
        return ['ended' => true, 'days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 0];
    }
    
    return [
        'ended' => false,
        'days' => floor($diff / 86400),
        'hours' => floor(($diff % 86400) / 3600),
        'minutes' => floor(($diff % 3600) / 60),
        'seconds' => $diff % 60
    ];
}

// Watchlist Functions
function addToWatchlist($user_id, $auction_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO watchlist (user_id, auction_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $auction_id]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function removeFromWatchlist($user_id, $auction_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("DELETE FROM watchlist WHERE user_id = ? AND auction_id = ?");
    $stmt->execute([$user_id, $auction_id]);
    return true;
}

function getWatchlist($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT a.*, c.name as category_name,
                          (SELECT COUNT(*) FROM bids WHERE auction_id = a.id) as bid_count
                          FROM watchlist w
                          JOIN auctions a ON w.auction_id = a.id
                          LEFT JOIN categories c ON a.category_id = c.id
                          WHERE w.user_id = ?
                          ORDER BY w.added_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function isInWatchlist($user_id, $auction_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM watchlist WHERE user_id = ? AND auction_id = ?");
    $stmt->execute([$user_id, $auction_id]);
    return $stmt->fetchColumn() > 0;
}

// Notification Functions
function createNotification($user_id, $type, $auction_id, $message) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, auction_id, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $type, $auction_id, $message]);
}

function getUserNotifications($user_id, $unread_only = false, $limit = 50) {
    global $pdo;
    
    $sql = "SELECT n.*, a.title as auction_title FROM notifications n
            LEFT JOIN auctions a ON n.auction_id = a.id
            WHERE n.user_id = ?";
    
    if ($unread_only) {
        $sql .= " AND n.is_read = 0";
    }
    
    $sql .= " ORDER BY n.created_at DESC LIMIT ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}

function getUnreadCount($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function markNotificationRead($notification_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->execute([$notification_id]);
}

// Category Functions
function getAllCategories() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT c.*, COUNT(a.id) as auction_count 
                        FROM categories c
                        LEFT JOIN auctions a ON c.id = a.category_id AND a.status = 'active'
                        GROUP BY c.id");
    return $stmt->fetchAll();
}

// Wallet Functions
function getUserBalance($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function addFunds($user_id, $amount, $method = 'credit_card') {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$amount, $user_id]);
        
        $transaction_id = 'TXN' . time() . rand(1000, 9999);
        $stmt = $pdo->prepare("INSERT INTO payments (user_id, amount, payment_method, transaction_id, status) 
                              VALUES (?, ?, ?, ?, 'completed')");
        $stmt->execute([$user_id, $amount, $method, $transaction_id]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// Helper Functions
function formatPrice($amount) {
    return '$' . number_format($amount, 2);
}

function formatDate($datetime) {
    return date('M d, Y H:i', strtotime($datetime));
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>
