<?php
require_once 'includes/functions.php';
requireLogin();

$status_filter = isset($_GET['status']) ? $_GET['status'] : null;
$auctions = getUserAuctions($_SESSION['user_id'], $status_filter);

$page_title = 'My Auctions';
include 'includes/header.php';
?>

<div class="container">
    <h1>My Auctions</h1>

    <div class="filter-tabs">
        <a href="my_auctions.php" class="<?php echo !$status_filter ? 'active' : ''; ?>">All</a>
        <a href="my_auctions.php?status=active" class="<?php echo $status_filter == 'active' ? 'active' : ''; ?>">Active</a>
        <a href="my_auctions.php?status=pending" class="<?php echo $status_filter == 'pending' ? 'active' : ''; ?>">Pending</a>
        <a href="my_auctions.php?status=ended" class="<?php echo $status_filter == 'ended' ? 'active' : ''; ?>">Ended</a>
    </div>

    <?php if (empty($auctions)): ?>
        <div class="empty-state">
            <p>You haven't created any auctions yet.</p>
            <?php if ($_SESSION['role'] == 'seller' || $_SESSION['role'] == 'admin'): ?>
                <a href="create_auction.php" class="btn btn-primary">Create Your First Auction</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Starting Price</th>
                    <th>Current Price</th>
                    <th>Bids</th>
                    <th>Ends</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($auctions as $auction): ?>
                    <tr>
                        <td><?php echo $auction['title']; ?></td>
                        <td><?php echo $auction['category_name']; ?></td>
                        <td><span class="status-badge status-<?php echo $auction['status']; ?>"><?php echo ucfirst($auction['status']); ?></span></td>
                        <td><?php echo formatPrice($auction['starting_price']); ?></td>
                        <td><?php echo formatPrice($auction['current_price']); ?></td>
                        <td><?php echo $auction['bid_count']; ?></td>
                        <td><?php echo formatDate($auction['end_time']); ?></td>
                        <td>
                            <a href="auction_detail.php?id=<?php echo $auction['id']; ?>" class="btn btn-sm">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
