<?php
require_once 'includes/functions.php';
requireLogin();

if ($_SESSION['role'] !== 'seller' && $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title']);
    $category_id = intval($_POST['category_id']);
    $description = sanitizeInput($_POST['description']);
    $starting_price = floatval($_POST['starting_price']);
    $duration_days = intval($_POST['duration']);
    
    if ($starting_price < 1) {
        $error = 'Starting price must be at least $1';
    } else {
        $start_time = date('Y-m-d H:i:s');
        $end_time = date('Y-m-d H:i:s', strtotime("+$duration_days days"));
        
        try {
            $stmt = $pdo->prepare("INSERT INTO auctions (seller_id, category_id, title, description, starting_price, current_price, start_time, end_time, status) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$_SESSION['user_id'], $category_id, $title, $description, $starting_price, $starting_price, $start_time, $end_time]);
            $success = 'Auction created successfully!';
        } catch (PDOException $e) {
            $error = 'Error creating auction';
        }
    }
}

$categories = getAllCategories();

$page_title = 'Create Auction';
include 'includes/header.php';
?>

<div class="container">
    <h1>Create New Auction</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" class="auction-form">
        <div class="form-group">
            <label>Title *</label>
            <input type="text" name="title" required>
        </div>

        <div class="form-group">
            <label>Category *</label>
            <select name="category_id" required>
                <option value="">Select a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Description *</label>
            <textarea name="description" rows="6" required></textarea>
        </div>

        <div class="form-group">
            <label>Starting Price ($) *</label>
            <input type="number" step="0.01" name="starting_price" min="1" required>
        </div>

        <div class="form-group">
            <label>Duration *</label>
            <select name="duration" required>
                <option value="1">1 Day</option>
                <option value="3">3 Days</option>
                <option value="5" selected>5 Days</option>
                <option value="7">7 Days</option>
                <option value="10">10 Days</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Create Auction</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
