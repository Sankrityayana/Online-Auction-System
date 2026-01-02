# Online Auction System

A comprehensive online auction platform built with PHP and MySQL featuring real-time bidding, countdown timers, and user notifications.

## Features

- **Real-time Bidding**: Place bids with live price updates
- **Countdown Timers**: Automatic countdown timers with color-coded urgency
- **Auction Management**: Create, manage, and track auctions
- **User Dashboard**: Personal dashboard for managing auctions and bids
- **Watchlist**: Save favorite auctions for quick access
- **Notification System**: Get notified about bid activity and auction updates
- **Wallet System**: Manage balance for bidding
- **Category Browsing**: Organized auction categories
- **Advanced Search**: Filter and search auctions by various criteria
- **Secure Authentication**: User registration and login system
- **Responsive Design**: Light multicolor theme with no gradients

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Web Server (XAMPP recommended)
- Modern web browser

## Installation

1. **Clone or download the repository**
   ```bash
   git clone https://github.com/Sankrityayana/Online-Auction-System.git
   ```

2. **Configure XAMPP**
   - Ensure Apache and MySQL are running
   - MySQL should be configured to use port 3307

3. **Import Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `online_auction_system`
   - Import the `database/database.sql` file

4. **Configure Database Connection**
   - Open `includes/config.php`
   - Update database credentials if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_PORT', '3307');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'online_auction_system');
     ```

5. **Access the Application**
   - Open your browser and navigate to `http://localhost/Online-Auction-System`

## Default Test Users

The database includes sample user accounts for testing:

**Seller Account:**
- Email: seller1@test.com
- Password: password123

**Bidder Accounts:**
- Email: bidder1@test.com - bidder4@test.com
- Password: password123

## Auction Rules

- Bids must exceed the current price by at least $5.00 (minimum bid increment)
- Users cannot bid on their own auctions
- Auctions automatically extend by 5 minutes if a bid is placed in the last 5 minutes
- Winners are automatically determined when auctions end
- Users must have sufficient wallet balance to place bids

## Database Schema

The system uses 9 main tables:

- **users**: User accounts and authentication
- **categories**: Auction categories
- **auctions**: Auction listings with pricing and timing
- **bids**: Bid history and tracking
- **watchlist**: User's saved auctions
- **notifications**: System notifications
- **payments**: Transaction history
- **reviews**: User ratings and reviews
- **auction_images**: Multiple images per auction

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL (Port 3307)
- **Frontend**: HTML5, CSS3, JavaScript
- **AJAX**: Real-time updates
- **Server**: Apache (XAMPP)

## Project Structure

```
Online-Auction-System/
├── css/
│   └── style.css
├── database/
│   └── database.sql
├── images/
│   └── auctions/
├── includes/
│   ├── config.php
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── js/
│   └── main.js
├── index.php
├── auctions.php
├── auction_detail.php
├── create_auction.php
├── my_auctions.php
├── my_bids.php
├── watchlist.php
├── profile.php
├── wallet.php
├── notifications.php
├── login.php
├── register.php
└── README.md
```

## License

MIT License - Copyright (c) 2026 Sankrityayana

## Support

For issues or questions, please open an issue on the GitHub repository.
