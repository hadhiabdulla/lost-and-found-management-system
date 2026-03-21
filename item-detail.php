<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$type = $_GET['type'] ?? '';
$id = intval($_GET['id'] ?? 0);

if ($type === 'lost') {
    $item = getItemById('lost_items', $id);
    $title = 'Lost Item Details';
    $icon = 'fa-exclamation-triangle';
    $color = 'danger';
    $locationLabel = 'Location Lost';
    $dateLabel = 'Date Lost';
    $location = $item['location_lost'] ?? 'Unknown';
    $date = $item['date_lost'] ?? '';
} else {
    $item = getItemById('found_items', $id);
    $title = 'Found Item Details';
    $icon = 'fa-hand-holding';
    $color = 'success';
    $locationLabel = 'Location Found';
    $dateLabel = 'Date Found';
    $location = $item['location_found'] ?? 'Unknown';
    $date = $item['date_found'] ?? '';
}

if (!$item) {
    header('Location: browse.php?error=Item not found');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($item['title']) ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="page-header">
    <div class="container">
        <div class="breadcrumb"><a href="index.php">Home</a> &rsaquo; <a href="browse.php?type=<?= $type ?>">Browse</a> &rsaquo; Item Details</div>
        <h1><i class="fas <?= $icon ?> text-<?= $color ?>"></i> <?= $title ?></h1>
    </div>
</div>
<div class="container">
    <div class="item-detail-grid">
        <div>
            <?php if ($item['image_path']): ?>
            <div class="item-detail-image">
                <img src="uploads/<?= htmlspecialchars($item['image_path']) ?>" alt="Item Image">
            </div>
            <?php else: ?>
            <div class="item-detail-no-image"><i class="fas fa-image"></i></div>
            <?php endif; ?>
        </div>
        <div class="item-detail-info">
            <h1><?= htmlspecialchars($item['title']) ?></h1>
            <div style="margin:12px 0;">
                <span class="badge badge-<?= $type ?>"><?= ucfirst($type) ?></span>
                <span class="badge badge-category"><?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></span>
                <span class="badge badge-<?= strtolower($item['status']) ?>"><?= ucfirst($item['status']) ?></span>
            </div>
            <table class="detail-table">
                <tr><td><?= $locationLabel ?>:</td><td><?= htmlspecialchars($location) ?></td></tr>
                <tr><td><?= $dateLabel ?>:</td><td><?= $date ? date('F d, Y', strtotime($date)) : 'Not specified' ?></td></tr>
                <tr><td>Category:</td><td><?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></td></tr>
                <tr><td>Reported By:</td><td><?= htmlspecialchars($item['user_name']) ?></td></tr>
                <tr><td>Contact Email:</td><td><a href="mailto:<?= htmlspecialchars($item['user_email']) ?>"><?= htmlspecialchars($item['user_email']) ?></a></td></tr>
                <?php if ($item['user_phone']): ?>
                <tr><td>Phone:</td><td><?= htmlspecialchars($item['user_phone']) ?></td></tr>
                <?php endif; ?>
                <tr><td>Reported:</td><td><?= date('F d, Y \a\t g:i A', strtotime($item['created_at'])) ?></td></tr>
            </table>
            <h3 style="margin-top:24px; margin-bottom:8px;">Description</h3>
            <p style="line-height:1.6; color:#475569;"><?= nl2br(htmlspecialchars($item['description'] ?? 'No description provided.')) ?></p>
            <div style="margin-top:24px; display:flex; gap:12px;">
                <a href="browse.php?type=<?= $type ?>" class="btn btn-outline" style="color:#64748b; border-color:#e2e8f0;"><i class="fas fa-arrow-left"></i> Back to Browse</a>
                <?php if (isLoggedIn()): ?>
                <a href="mailto:<?= htmlspecialchars($item['user_email']) ?>" class="btn btn-primary"><i class="fas fa-envelope"></i> Contact Owner</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>
