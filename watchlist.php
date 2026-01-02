<?php
require_once 'includes/functions.php';
requireLogin();

$watchlist = getWatchlist($_SESSION['user_id']);

$page_title = 'Watchlist';
include 'includes/header.php';
?>

<div class="container">
    <h1>My Watchlist</h1>

    <?php if (empty($watchlist)): ?>
        <div class="empty-state">
            <p>Your watchlist is empty.</p>
            <a href="auctions.php" class="btn btn-primary">Browse Auctions</a>
        </div>
    <?php else: ?>
        <div class="auctions-grid">
            <?php foreach ($watchlist as $auction): ?>
                <div class="auction-card">
                    <div class="auction-image">
                        <img src="<?php echo $auction['image'] ?: 'images/placeholder.jpg'; ?>" alt="<?php echo $auction['title']; ?>">
                        <span class="category-badge"><?php echo $auction['category_name']; ?></span>
                    </div>
                    <div class="auction-content">
                        <h3><a href="auction_detail.php?id=<?php echo $auction['id']; ?>"><?php echo $auction['title']; ?></a></h3>
                        <div class="price-info">
                            <div>
                                <span class="label">Current Price:</span>
                                <span class="current-price"><?php echo formatPrice($auction['current_price']); ?></span>
                            </div>
                            <div>
                                <span class="label">Bids:</span>
                                <span><?php echo $auction['bid_count']; ?></span>
                            </div>
                        </div>
                        <div class="auction-footer">
                            <div class="countdown" data-end="<?php echo $auction['end_time']; ?>">
                                Calculating...
                            </div>
                            <button onclick="toggleWatchlist(<?php echo $auction['id']; ?>)" class="btn-icon active">
                                ♥
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    updateAllCountdowns();
    setInterval(updateAllCountdowns, 1000);
});
</script>

<?php include 'includes/footer.php'; ?>
