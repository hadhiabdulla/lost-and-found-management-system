<?php
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/../includes/auth.php';
}
$notifCount = 0;
if (isLoggedIn()) {
    $notifCount = count(getUnreadNotifications($_SESSION['user_id']));
}
?>
<nav class="navbar">
    <div class="container nav-container">
        <a href="<?= BASE_URL ?>/index.php" class="nav-brand">
            <i class="fas fa-search-location"></i> Lost &amp; Found
        </a>
        <button class="nav-toggle" onclick="toggleNav()"><i class="fas fa-bars"></i></button>
        <ul class="nav-links" id="navLinks">
            <li><a href="<?= BASE_URL ?>/index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="<?= BASE_URL ?>/browse.php?type=lost"><i class="fas fa-search"></i> Lost Items</a></li>
            <li><a href="<?= BASE_URL ?>/browse.php?type=found"><i class="fas fa-hand-holding"></i> Found Items</a></li>
            <?php if (isLoggedIn()): ?>
            <li><a href="<?= BASE_URL ?>/report-lost.php"><i class="fas fa-exclamation-triangle"></i> Report Lost</a></li>
            <li><a href="<?= BASE_URL ?>/report-found.php"><i class="fas fa-plus-circle"></i> Report Found</a></li>
            <li>
                <a href="<?= BASE_URL ?>/notifications.php" class="notif-link">
                    <i class="fas fa-bell"></i>
                    <?php if ($notifCount > 0): ?><span class="notif-badge"><?= $notifCount ?></span><?php endif; ?>
                </a>
            </li>
            <li class="nav-dropdown">
                <a href="#" class="dropdown-toggle"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['user_name']) ?> <i class="fas fa-chevron-down"></i></a>
                <ul class="dropdown-menu">
                    <li><a href="<?= BASE_URL ?>/dashboard.php"><i class="fas fa-tachometer-alt"></i> My Dashboard</a></li>
                    <?php if (isAdmin()): ?>
                    <li><a href="<?= BASE_URL ?>/admin/index.php"><i class="fas fa-cog"></i> Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="<?= BASE_URL ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </li>
            <?php else: ?>
            <li><a href="<?= BASE_URL ?>/login.php" class="btn btn-outline btn-sm"><i class="fas fa-sign-in-alt"></i> Login</a></li>
            <li><a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-sm"><i class="fas fa-user-plus"></i> Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<script>
function toggleNav() {
    document.getElementById('navLinks').classList.toggle('active');
}
document.querySelectorAll('.nav-dropdown > a').forEach(el => {
    el.addEventListener('click', e => {
        e.preventDefault();
        el.parentElement.classList.toggle('open');
    });
});
</script>
