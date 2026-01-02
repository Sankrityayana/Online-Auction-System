<?php
require_once 'includes/functions.php';
requireLogin();

$user = getCurrentUser();

$page_title = 'Profile';
include 'includes/header.php';
?>

<div class="container">
    <h1>My Profile</h1>

    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-info">
                <h2><?php echo $user['username']; ?></h2>
                <p><?php echo $user['email']; ?></p>
                <span class="role-badge"><?php echo ucfirst($user['role']); ?></span>
            </div>
        </div>

        <div class="profile-details">
            <div class="detail-row">
                <span class="label">Full Name:</span>
                <span><?php echo $user['full_name']; ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Phone:</span>
                <span><?php echo $user['phone'] ?: 'Not provided'; ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Address:</span>
                <span><?php echo $user['address'] ?: 'Not provided'; ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Member Since:</span>
                <span><?php echo formatDate($user['created_at']); ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Account Balance:</span>
                <span><?php echo formatPrice($user['balance']); ?></span>
            </div>
        </div>

        <div class="profile-actions">
            <a href="wallet.php" class="btn btn-primary">Manage Wallet</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
