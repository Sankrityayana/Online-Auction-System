<?php
require_once 'includes/functions.php';

$categories = getAllCategories();
$featured_auctions = getActiveAuctions(null, null, 'most_bids', 6);
$ending_soon = getActiveAuctions(null, null, 'ending_soon', 8);

$page_title = 'Home';
include 'includes/header.php';
?>

<div class="hero">
    <div class="container">
        <h1>Welcome to <?php echo SITE_NAME; ?></h1>
        <p>Bid on amazing items and win great deals!</p>
        <form action="auctions.php" method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search auctions...">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
</div>

<div class="container">
    <section class="categories-section">
        <h2>Browse Categories</h2>
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
                <a href="auctions.php?category=<?php echo $category['id']; ?>" class="category-card category-<?php echo strtolower(str_replace(' ', '-', $category['name'])); ?>">
                    <h3><?php echo $category['name']; ?></h3>
                    <p><?php echo $category['auction_count']; ?> active auctions</p>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="auctions-section">
        <h2>Featured Auctions</h2>
        <div class="auctions-grid">
            <?php foreach ($featured_auctions as $auction): ?>
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
                            <?php if (isLoggedIn()): ?>
                                <button onclick="toggleWatchlist(<?php echo $auction['id']; ?>)" class="btn-icon">
                                    ♥
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="auctions-section">
        <h2>Ending Soon</h2>
        <div class="auctions-grid">
            <?php foreach ($ending_soon as $auction): ?>
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
                            <div class="countdown ending-soon" data-end="<?php echo $auction['end_time']; ?>">
                                Calculating...
                            </div>
                            <?php if (isLoggedIn()): ?>
                                <button onclick="toggleWatchlist(<?php echo $auction['id']; ?>)" class="btn-icon">
                                    ♥
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    updateAllCountdowns();
    setInterval(updateAllCountdowns, 1000);
});
</script>

<?php include 'includes/footer.php'; ?>
