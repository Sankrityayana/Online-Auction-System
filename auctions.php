<?php
require_once 'includes/functions.php';

$category = isset($_GET['category']) ? intval($_GET['category']) : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'ending_soon';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$auctions = getActiveAuctions($category, $search, $sort, $limit, $offset);
$categories = getAllCategories();

$page_title = 'Auctions';
include 'includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Browse Auctions</h1>
    </div>

    <div class="auctions-layout">
        <aside class="sidebar">
            <div class="filter-section">
                <h3>Categories</h3>
                <form method="GET" action="auctions.php">
                    <?php foreach ($categories as $cat): ?>
                        <label class="checkbox-label">
                            <input type="radio" name="category" value="<?php echo $cat['id']; ?>" 
                                   <?php echo $category == $cat['id'] ? 'checked' : ''; ?>>
                            <?php echo $cat['name']; ?> (<?php echo $cat['auction_count']; ?>)
                        </label>
                    <?php endforeach; ?>
                    <label class="checkbox-label">
                        <input type="radio" name="category" value="" 
                               <?php echo !$category ? 'checked' : ''; ?>>
                        All Categories
                    </label>
                    
                    <h3>Sort By</h3>
                    <select name="sort" onchange="this.form.submit()">
                        <option value="ending_soon" <?php echo $sort == 'ending_soon' ? 'selected' : ''; ?>>Ending Soon</option>
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest</option>
                        <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="most_bids" <?php echo $sort == 'most_bids' ? 'selected' : ''; ?>>Most Bids</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </form>
            </div>
        </aside>

        <div class="main-content">
            <?php if ($search): ?>
                <p>Search results for: <strong><?php echo htmlspecialchars($search); ?></strong></p>
            <?php endif; ?>

            <?php if (empty($auctions)): ?>
                <div class="empty-state">
                    <p>No auctions found.</p>
                </div>
            <?php else: ?>
                <div class="auctions-grid">
                    <?php foreach ($auctions as $auction): ?>
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

                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?category=<?php echo $category; ?>&search=<?php echo $search; ?>&sort=<?php echo $sort; ?>&page=<?php echo $page-1; ?>" class="btn">Previous</a>
                    <?php endif; ?>
                    <?php if (count($auctions) == $limit): ?>
                        <a href="?category=<?php echo $category; ?>&search=<?php echo $search; ?>&sort=<?php echo $sort; ?>&page=<?php echo $page+1; ?>" class="btn">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    updateAllCountdowns();
    setInterval(updateAllCountdowns, 1000);
});
</script>

<?php include 'includes/footer.php'; ?>
