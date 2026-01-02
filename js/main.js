// Time Calculation
function getRemainingTime(endTime) {
    const end = new Date(endTime).getTime();
    const now = new Date().getTime();
    const diff = end - now;

    if (diff <= 0) {
        return { ended: true, days: 0, hours: 0, minutes: 0, seconds: 0 };
    }

    return {
        ended: false,
        days: Math.floor(diff / (1000 * 60 * 60 * 24)),
        hours: Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
        minutes: Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)),
        seconds: Math.floor((diff % (1000 * 60)) / 1000)
    };
}

// Update All Countdowns
function updateAllCountdowns() {
    const countdowns = document.querySelectorAll('.countdown, .countdown-small');
    
    countdowns.forEach(countdown => {
        const endTime = countdown.dataset.end;
        const remaining = getRemainingTime(endTime);

        if (remaining.ended) {
            countdown.textContent = 'Ended';
            countdown.classList.add('ended');
        } else {
            let timeStr = '';
            if (remaining.days > 0) timeStr += remaining.days + 'd ';
            timeStr += remaining.hours + 'h ' + remaining.minutes + 'm ' + remaining.seconds + 's';
            countdown.textContent = timeStr;

            // Add warning classes
            if (remaining.days === 0 && remaining.hours < 1) {
                countdown.classList.add('ending-critical');
            } else if (remaining.days === 0 && remaining.hours < 24) {
                countdown.classList.add('ending-soon');
            }
        }
    });
}

// Place Bid via AJAX
function placeBidAjax(event, auctionId) {
    event.preventDefault();
    
    const form = event.target;
    const bidAmount = form.bid_amount.value;
    const messageDiv = document.getElementById('bidMessage');

    fetch('bid_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `auction_id=${auctionId}&bid_amount=${bidAmount}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
            
            // Update displayed prices
            if (document.getElementById('currentPrice')) {
                document.getElementById('currentPrice').textContent = data.new_current_price;
            }
            if (document.getElementById('bidCount')) {
                document.getElementById('bidCount').textContent = data.new_bid_count;
            }

            // Update minimum bid
            const minBid = parseFloat(bidAmount) + 5.00;
            form.bid_amount.min = minBid.toFixed(2);
            form.bid_amount.value = minBid.toFixed(2);

            // Refresh bid history
            setTimeout(() => refreshBidHistory(auctionId), 1000);
        } else {
            messageDiv.innerHTML = '<div class="alert alert-error">' + data.message + '</div>';
        }
    })
    .catch(error => {
        messageDiv.innerHTML = '<div class="alert alert-error">Error placing bid</div>';
    });

    return false;
}

// Refresh Bid History
function refreshBidHistory(auctionId) {
    fetch(`get_bids.php?auction_id=${auctionId}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#bidHistory tbody');
            if (tbody && data.bids) {
                tbody.innerHTML = '';
                data.bids.forEach(bid => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${bid.username}</td>
                            <td>$${parseFloat(bid.bid_amount).toFixed(2)}</td>
                            <td>${bid.bid_time}</td>
                        </tr>
                    `;
                });
            }
        })
        .catch(error => console.error('Error refreshing bids:', error));
}

// Watchlist Toggle
function toggleWatchlist(auctionId) {
    fetch('watchlist_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `auction_id=${auctionId}&action=toggle`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Check Notifications
function checkNotifications() {
    if (!document.querySelector('.notification-link')) return;

    fetch('get_notifications.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.notification-link .badge');
            if (badge && data.unread_count > 0) {
                badge.textContent = data.unread_count;
                badge.style.display = 'inline';
            } else if (badge) {
                badge.style.display = 'none';
            }
        })
        .catch(error => console.error('Error:', error));
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Start countdown timers
    updateAllCountdowns();
    setInterval(updateAllCountdowns, 1000);

    // Check notifications every 30 seconds
    if (document.querySelector('.notification-link')) {
        setInterval(checkNotifications, 30000);
    }
});
