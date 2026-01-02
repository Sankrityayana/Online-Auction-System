<?php
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['unread_count' => 0]);
    exit;
}

$unread_count = getUnreadCount($_SESSION['user_id']);
$notifications = getUserNotifications($_SESSION['user_id'], true, 5);

echo json_encode([
    'unread_count' => $unread_count,
    'notifications' => $notifications
]);
?>
