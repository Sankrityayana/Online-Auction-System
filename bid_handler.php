<?php
require_once 'includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to place a bid']);
    exit;
}

$auction_id = isset($_POST['auction_id']) ? intval($_POST['auction_id']) : 0;
$bid_amount = isset($_POST['bid_amount']) ? floatval($_POST['bid_amount']) : 0;

$result = placeBid($auction_id, $_SESSION['user_id'], $bid_amount);

if ($result['success']) {
    $auction = getAuctionById($auction_id);
    $result['new_current_price'] = formatPrice($auction['current_price']);
    $result['new_bid_count'] = $auction['bid_count'];
}

echo json_encode($result);
?>
