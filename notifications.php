<?php
require_once 'includes/functions.php';
requireLogin();

if (isset($_GET['mark_read']) && $_GET['mark_read']) {
    markNotificationRead(intval($_GET['mark_read']));
    header('Location: notifications.php');
    exit;
}

$unread_only = isset($_GET['unread']) && $_GET['unread'] == '1';
$notifications = getUserNotifications($_SESSION['user_id'], $unread_only);

$page_title = 'Notifications';
include 'includes/header.php';
?>

<div class="container">
    <h1>Notifications</h1>

    <div class="filter-tabs">
        <a href="notifications.php" class="<?php echo !$unread_only ? 'active' : ''; ?>">All</a>
        <a href="notifications.php?unread=1" class="<?php echo $unread_only ? 'active' : ''; ?>">Unread</a>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="empty-state">
            <p>No notifications.</p>
        </div>
    <?php else: ?>
        <div class="notifications-list">
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-card <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
                    <div class="notification-icon notification-<?php echo $notification['type']; ?>">
                        <?php
                        $icons = [
                            'bid_placed' => '💰',
                            'outbid' => '⚠️',
                            'auction_won' => '🎉',
                            'auction_ended' => '⏰',
                            'auction_starting' => '🔔'
                        ];
                        echo $icons[$notification['type']];
                        ?>
                    </div>
                    <div class="notification-content">
                        <p><?php echo $notification['message']; ?></p>
                        <?php if ($notification['auction_title']): ?>
                            <a href="auction_detail.php?id=<?php echo $notification['auction_id']; ?>" class="notification-link">
                                View Auction: <?php echo $notification['auction_title']; ?>
                            </a>
                        <?php endif; ?>
                        <span class="notification-time"><?php echo formatDate($notification['created_at']); ?></span>
                    </div>
                    <?php if (!$notification['is_read']): ?>
                        <a href="notifications.php?mark_read=<?php echo $notification['id']; ?>" class="mark-read">✓</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
