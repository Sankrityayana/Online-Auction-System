<?php
require_once 'includes/functions.php';
requireLogin();

$bids = getUserBids($_SESSION['user_id']);

$page_title = 'My Bids';
include 'includes/header.php';
?>

<div class="container">
    <h1>My Bids</h1>

    <?php if (empty($bids)): ?>
        <div class="empty-state">
            <p>You haven't placed any bids yet.</p>
            <a href="auctions.php" class="btn btn-primary">Browse Auctions</a>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Auction</th>
                    <th>Your Bid</th>
                    <th>Current Price</th>
                    <th>Status</th>
                    <th>Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bids as $bid): ?>
                    <tr>
                        <td><a href="auction_detail.php?id=<?php echo $bid['auction_id']; ?>"><?php echo $bid['title']; ?></a></td>
                        <td><?php echo formatPrice($bid['bid_amount']); ?></td>
                        <td><?php echo formatPrice($bid['current_price']); ?></td>
                        <td><span class="status-badge status-<?php echo $bid['bid_status']; ?>"><?php echo ucfirst($bid['bid_status']); ?></span></td>
                        <td>
                            <?php if ($bid['status'] == 'active'): ?>
                                <div class="countdown-small" data-end="<?php echo $bid['end_time']; ?>">Calculating...</div>
                            <?php else: ?>
                                <?php echo formatDate($bid['end_time']); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($bid['status'] == 'active'): ?>
                                <a href="auction_detail.php?id=<?php echo $bid['auction_id']; ?>" class="btn btn-sm">Bid Again</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    updateAllCountdowns();
    setInterval(updateAllCountdowns, 1000);
});
</script>

<?php include 'includes/footer.php'; ?>
