<?php
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$auction_id = isset($_POST['auction_id']) ? intval($_POST['auction_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action == 'toggle') {
    $in_watchlist = isInWatchlist($_SESSION['user_id'], $auction_id);
    
    if ($in_watchlist) {
        removeFromWatchlist($_SESSION['user_id'], $auction_id);
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        addToWatchlist($_SESSION['user_id'], $auction_id);
        echo json_encode(['success' => true, 'action' => 'added']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
