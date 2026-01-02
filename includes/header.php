<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo"><?php echo SITE_NAME; ?></a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="auctions.php">Auctions</a>
                
                <?php if (isLoggedIn()): ?>
                    <?php if ($_SESSION['role'] == 'seller' || $_SESSION['role'] == 'admin'): ?>
                        <a href="create_auction.php">Create Auction</a>
                    <?php endif; ?>
                    <a href="my_auctions.php">My Auctions</a>
                    <a href="my_bids.php">My Bids</a>
                    <a href="watchlist.php">Watchlist</a>
                    <a href="notifications.php" class="notification-link">
                        Notifications
                        <?php 
                        $unread_count = getUnreadCount($_SESSION['user_id']);
                        if ($unread_count > 0): 
                        ?>
                            <span class="badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown">
                        <span><?php echo $_SESSION['username']; ?> ▼</span>
                        <div class="dropdown-content">
                            <a href="profile.php">Profile</a>
                            <a href="wallet.php">Wallet (<?php echo formatPrice(getUserBalance($_SESSION['user_id'])); ?>)</a>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main>
