<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$stats = getDashboardStats();
$recentLost = getLostItems(['status' => 'open'], 6);
$recentFound = getFoundItems(['status' => 'available'], 6);
$categories = getAllCategories();

$notifCount = 0;
if (isLoggedIn()) {
    $notifs = getUnreadNotifications($_SESSION['user_id']);
    $notifCount = count($notifs);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1><i class="fas fa-search"></i> Lost & Found</h1>
        <p>Report lost items, find what others have discovered, and help reunite people with their belongings.</p>
        <div class="hero-buttons">
            <a href="report-lost.php" class="btn btn-danger"><i class="fas fa-exclamation-triangle"></i> Report Lost Item</a>
            <a href="report-found.php" class="btn btn-success"><i class="fas fa-hand-holding"></i> Report Found Item</a>
            <a href="browse.php" class="btn btn-outline"><i class="fas fa-list"></i> Browse All</a>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-search stat-icon lost"></i>
                <div class="stat-info">
                    <span class="stat-number"><?= $stats['total_lost'] ?></span>
                    <span class="stat-label">Items Reported Lost</span>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-hand-holding stat-icon found"></i>
                <div class="stat-info">
                    <span class="stat-number"><?= $stats['total_found'] ?></span>
                    <span class="stat-label">Items Found</span>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle stat-icon resolved"></i>
                <div class="stat-info">
                    <span class="stat-number"><?= $stats['total_resolved'] ?></span>
                    <span class="stat-label">Successfully Resolved</span>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-users stat-icon users"></i>
                <div class="stat-info">
                    <span class="stat-number"><?= $stats['total_users'] ?></span>
                    <span class="stat-label">Registered Users</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="search-section">
    <div class="container">
        <form action="browse.php" method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search for lost or found items..." class="search-input">
            <select name="category" class="search-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="type" class="search-select">
                <option value="">Lost & Found</option>
                <option value="lost">Lost Items</option>
                <option value="found">Found Items</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>
</section>

<!-- Recent Lost Items -->
<section class="items-section">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-exclamation-circle text-danger"></i> Recently Lost</h2>
            <a href="browse.php?type=lost" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="items-grid">
            <?php if (empty($recentLost)): ?>
            <div class="no-items"><i class="fas fa-inbox"></i> No lost items reported yet.</div>
            <?php else: ?>
            <?php foreach ($recentLost as $item): ?>
            <div class="item-card lost-card">
                <?php if ($item['image_path']): ?>
                <img src="uploads/<?= htmlspecialchars($item['image_path']) ?>" alt="Item Image" class="item-image">
                <?php else: ?>
                <div class="item-no-image"><i class="fas fa-image"></i></div>
                <?php endif; ?>
                <div class="item-body">
                    <span class="badge badge-lost">Lost</span>
                    <span class="badge badge-category"><?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></span>
                    <h3 class="item-title"><?= htmlspecialchars($item['title']) ?></h3>
                    <p class="item-desc"><?= htmlspecialchars(substr($item['description'] ?? '', 0, 80)) ?>...</p>
                    <div class="item-meta">
                        <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($item['location_lost'] ?? 'Unknown') ?></span>
                        <span><i class="fas fa-clock"></i> <?= timeAgo($item['created_at']) ?></span>
                    </div>
                    <a href="item-detail.php?type=lost&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Recent Found Items -->
<section class="items-section bg-light">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-check-circle text-success"></i> Recently Found</h2>
            <a href="browse.php?type=found" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="items-grid">
            <?php if (empty($recentFound)): ?>
            <div class="no-items"><i class="fas fa-inbox"></i> No found items reported yet.</div>
            <?php else: ?>
            <?php foreach ($recentFound as $item): ?>
            <div class="item-card found-card">
                <?php if ($item['image_path']): ?>
                <img src="uploads/<?= htmlspecialchars($item['image_path']) ?>" alt="Item Image" class="item-image">
                <?php else: ?>
                <div class="item-no-image"><i class="fas fa-image"></i></div>
                <?php endif; ?>
                <div class="item-body">
                    <span class="badge badge-found">Found</span>
                    <span class="badge badge-category"><?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></span>
                    <h3 class="item-title"><?= htmlspecialchars($item['title']) ?></h3>
                    <p class="item-desc"><?= htmlspecialchars(substr($item['description'] ?? '', 0, 80)) ?>...</p>
                    <div class="item-meta">
                        <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($item['location_found'] ?? 'Unknown') ?></span>
                        <span><i class="fas fa-clock"></i> <?= timeAgo($item['created_at']) ?></span>
                    </div>
                    <a href="item-detail.php?type=found&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section">
    <div class="container">
        <h2 class="text-center">Browse by Category</h2>
        <div class="categories-grid">
            <?php
            $catIcons = ['Electronics'=>'fa-laptop','Clothing'=>'fa-tshirt','Accessories'=>'fa-glasses','Documents'=>'fa-file-alt','Wallets & Bags'=>'fa-wallet','Sports Equipment'=>'fa-futbol','Other'=>'fa-box'];
            foreach ($categories as $cat):
                $icon = $catIcons[$cat['name']] ?? 'fa-tag';
            ?>
            <a href="browse.php?category=<?= $cat['id'] ?>" class="category-card">
                <i class="fas <?= $icon ?> cat-icon"></i>
                <span><?= htmlspecialchars($cat['name']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>
