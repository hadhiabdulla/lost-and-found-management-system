<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';
$categoryId = $_GET['category'] ?? '';

$filters = [];
if ($search) $filters['search'] = $search;
if ($categoryId) $filters['category_id'] = $categoryId;

if ($type === 'lost') {
    $items = getLostItems($filters);
    $title = 'Lost Items';
    $icon = 'fa-exclamation-triangle';
    $color = 'danger';
} elseif ($type === 'found') {
    $items = getFoundItems($filters);
    $title = 'Found Items';
    $icon = 'fa-hand-holding';
    $color = 'success';
} else {
    $lostItems = getLostItems($filters, 10);
    $foundItems = getFoundItems($filters, 10);
    $items = array_merge($lostItems, $foundItems);
    $title = 'All Items';
    $icon = 'fa-list';
    $color = 'primary';
}

$categories = getAllCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="page-header">
    <div class="container">
        <div class="breadcrumb"><a href="index.php">Home</a> &rsaquo; <?= $title ?></div>
        <h1><i class="fas <?= $icon ?> text-<?= $color ?>"></i> <?= $title ?></h1>
        <p>Browse all reported items and search for what you're looking for</p>
    </div>
</div>
<div class="container" style="padding-bottom:48px;">
    <div class="search-section" style="background:white; padding:24px; border-radius:8px; margin-bottom:24px; box-shadow:0 2px 4px rgba(0,0,0,0.1);">
        <form method="GET" action="browse.php" class="search-form">
            <input type="text" name="search" placeholder="Search items..." value="<?= htmlspecialchars($search) ?>" class="search-input">
            <select name="category" class="search-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="type" class="search-select">
                <option value="" <?= !$type ? 'selected' : '' ?>>All Types</option>
                <option value="lost" <?= $type === 'lost' ? 'selected' : '' ?>>Lost Items</option>
                <option value="found" <?= $type === 'found' ? 'selected' : '' ?>>Found Items</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>
    <?php if (empty($items)): ?>
    <div class="no-items" style="text-align:center; padding:60px 20px; background:white; border-radius:8px;">
        <i class="fas fa-inbox" style="font-size:4rem; color:#cbd5e1; margin-bottom:16px;"></i>
        <h3>No Items Found</h3>
        <p style="color:#64748b;">Try adjusting your search or filters.</p>
    </div>
    <?php else: ?>
    <div class="items-grid">
        <?php foreach ($items as $item): 
            $isLost = isset($item['location_lost']);
            $itemType = $isLost ? 'lost' : 'found';
            $cardClass = $isLost ? 'lost-card' : 'found-card';
            $badgeClass = $isLost ? 'badge-lost' : 'badge-found';
            $badgeText = $isLost ? 'Lost' : 'Found';
            $location = $isLost ? $item['location_lost'] : $item['location_found'];
        ?>
        <div class="item-card <?= $cardClass ?>">
            <?php if ($item['image_path']): ?>
            <img src="uploads/<?= htmlspecialchars($item['image_path']) ?>" alt="Item" class="item-image">
            <?php else: ?>
            <div class="item-no-image"><i class="fas fa-image"></i></div>
            <?php endif; ?>
            <div class="item-body">
                <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                <span class="badge badge-category"><?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></span>
                <h3 class="item-title"><?= htmlspecialchars($item['title']) ?></h3>
                <p class="item-desc"><?= htmlspecialchars(substr($item['description'] ?? 'No description', 0, 80)) ?>...</p>
                <div class="item-meta">
                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($location ?? 'Unknown') ?></span>
                    <span><i class="fas fa-clock"></i> <?= timeAgo($item['created_at']) ?></span>
                </div>
                <a href="item-detail.php?type=<?= $itemType ?>&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline">View Details</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>
