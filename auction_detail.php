<?php
require_once 'includes/functions.php';

$auction_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$auction = getAuctionById($auction_id);

if (!$auction) {
    header('Location: index.php');
    exit;
}

$bids = getAuctionBids($auction_id, 20);
$in_watchlist = isLoggedIn() ? isInWatchlist($_SESSION['user_id'], $auction_id) : false;

$page_title = $auction['title'];
include 'includes/header.php';
?>

<div class="container">
    <div class="auction-detail-layout">
        <div class="auction-main">
            <div class="auction-image-section">
                <img src="<?php echo $auction['image'] ?: 'images/placeholder.jpg'; ?>" alt="<?php echo $auction['title']; ?>" class="main-image">
            </div>

            <div class="auction-info-card">
                <span class="category-badge"><?php echo $auction['category_name']; ?></span>
                <h1><?php echo $auction['title']; ?></h1>

                <div class="price-section">
                    <div class="current-price-box">
                        <span class="label">Current Price</span>
                        <span class="price" id="currentPrice"><?php echo formatPrice($auction['current_price']); ?></span>
                    </div>
                    <div class="info-grid">
                        <div>
                            <span class="label">Starting Price:</span>
                            <span><?php echo formatPrice($auction['starting_price']); ?></span>
                        </div>
                        <div>
                            <span class="label">Bids:</span>
                            <span id="bidCount"><?php echo $auction['bid_count']; ?></span>
                        </div>
                        <div>
                            <span class="label">Views:</span>
                            <span><?php echo $auction['views']; ?></span>
                        </div>
                    </div>
                </div>

                <div class="countdown-box" id="mainCountdown" data-end="<?php echo $auction['end_time']; ?>">
                    <span class="label">Time Remaining:</span>
                    <span class="time">Calculating...</span>
                </div>

                <div class="seller-info">
                    <span class="label">Seller:</span>
                    <strong><?php echo $auction['seller_name']; ?></strong>
                </div>

                <?php if (isLoggedIn() && $_SESSION['user_id'] != $auction['seller_id'] && $auction['status'] == 'active'): ?>
                    <div class="bidding-section">
                        <h3>Place Your Bid</h3>
                        <?php if ($auction['high_bidder']): ?>
                            <p>Current high bidder: <strong><?php echo $auction['high_bidder']; ?></strong></p>
                        <?php else: ?>
                            <p>No bids yet. Be the first to bid!</p>
                        <?php endif; ?>

                        <form id="bidForm" onsubmit="return placeBidAjax(event, <?php echo $auction_id; ?>)">
                            <div class="form-group">
                                <label>Bid Amount</label>
                                <input type="number" step="0.01" name="bid_amount" id="bidAmount" 
                                       min="<?php echo $auction['current_price'] + MIN_BID_INCREMENT; ?>" 
                                       value="<?php echo $auction['current_price'] + MIN_BID_INCREMENT; ?>" required>
                                <small>Minimum bid: <?php echo formatPrice($auction['current_price'] + MIN_BID_INCREMENT); ?></small>
                            </div>
                            <div class="balance-info">
                                Your balance: <?php echo formatPrice(getUserBalance($_SESSION['user_id'])); ?>
                            </div>
                            <button type="submit" class="btn btn-primary">Place Bid</button>
                        </form>
                        <div id="bidMessage"></div>
                    </div>
                <?php elseif (!isLoggedIn()): ?>
                    <div class="alert">
                        <a href="login.php">Login</a> to place a bid on this auction.
                    </div>
                <?php endif; ?>

                <?php if (isLoggedIn()): ?>
                    <button onclick="toggleWatchlist(<?php echo $auction_id; ?>)" class="btn btn-secondary">
                        <?php echo $in_watchlist ? '♥ Remove from Watchlist' : '♡ Add to Watchlist'; ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="auction-sidebar">
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('description')">Description</button>
                <button class="tab-btn" onclick="switchTab('bids')">Bid History</button>
            </div>

            <div id="description" class="tab-content active">
                <h3>Description</h3>
                <p><?php echo nl2br($auction['description']); ?></p>
            </div>

            <div id="bids" class="tab-content">
                <h3>Bid History</h3>
                <div id="bidHistory">
                    <?php if (empty($bids)): ?>
                        <p>No bids yet.</p>
                    <?php else: ?>
                        <table class="bids-table">
                            <thead>
                                <tr>
                                    <th>Bidder</th>
                                    <th>Amount</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bids as $bid): ?>
                                    <tr>
                                        <td><?php echo $bid['username']; ?></td>
                                        <td><?php echo formatPrice($bid['bid_amount']); ?></td>
                                        <td><?php echo formatDate($bid['bid_time']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const auctionId = <?php echo $auction_id; ?>;

document.addEventListener('DOMContentLoaded', function() {
    updateMainCountdown();
    setInterval(updateMainCountdown, 1000);
    setInterval(() => refreshBidHistory(auctionId), 10000);
});

function updateMainCountdown() {
    const countdown = document.getElementById('mainCountdown');
    if (!countdown) return;
    
    const endTime = countdown.dataset.end;
    const timeDisplay = countdown.querySelector('.time');
    const remaining = getRemainingTime(endTime);
    
    if (remaining.ended) {
        timeDisplay.textContent = 'Auction Ended';
        countdown.classList.add('ended');
    } else {
        let timeStr = '';
        if (remaining.days > 0) timeStr += remaining.days + 'd ';
        timeStr += remaining.hours + 'h ' + remaining.minutes + 'm ' + remaining.seconds + 's';
        timeDisplay.textContent = timeStr;
        
        if (remaining.days === 0 && remaining.hours < 1) {
            countdown.classList.add('ending-critical');
        } else if (remaining.days === 0 && remaining.hours < 24) {
            countdown.classList.add('ending-soon');
        }
    }
}

function switchTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}
</script>

<?php include 'includes/footer.php'; ?>
