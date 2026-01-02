<?php
require_once 'includes/functions.php';
requireLogin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $method = $_POST['method'];
    
    if ($amount < 10) {
        $error = 'Minimum deposit is $10';
    } else {
        if (addFunds($_SESSION['user_id'], $amount, $method)) {
            $success = 'Funds added successfully!';
        } else {
            $error = 'Error processing payment';
        }
    }
}

$balance = getUserBalance($_SESSION['user_id']);
$transactions = $pdo->prepare("SELECT * FROM payments WHERE user_id = ? ORDER BY payment_date DESC LIMIT 20");
$transactions->execute([$_SESSION['user_id']]);
$transactions = $transactions->fetchAll();

$page_title = 'Wallet';
include 'includes/header.php';
?>

<div class="container">
    <h1>My Wallet</h1>

    <div class="wallet-balance">
        <h2>Current Balance</h2>
        <div class="balance-amount"><?php echo formatPrice($balance); ?></div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="add-funds-section">
        <h3>Add Funds</h3>
        <form method="POST" class="wallet-form">
            <div class="form-group">
                <label>Amount ($)</label>
                <input type="number" step="0.01" name="amount" min="10" required>
            </div>

            <div class="form-group">
                <label>Payment Method</label>
                <select name="method" required>
                    <option value="credit_card">Credit Card</option>
                    <option value="paypal">PayPal</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Add Funds</button>
        </form>
    </div>

    <div class="transactions-section">
        <h3>Transaction History</h3>
        <?php if (empty($transactions)): ?>
            <p>No transactions yet.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction ID</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo formatDate($transaction['payment_date']); ?></td>
                            <td><?php echo $transaction['transaction_id']; ?></td>
                            <td><?php echo formatPrice($transaction['amount']); ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $transaction['payment_method'])); ?></td>
                            <td><span class="status-badge status-<?php echo $transaction['status']; ?>"><?php echo ucfirst($transaction['status']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
