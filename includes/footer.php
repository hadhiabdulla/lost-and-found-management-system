<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h3><i class="fas fa-search-location"></i> Lost &amp; Found</h3>
                <p>Helping people reunite with their lost belongings. Report lost items or help others by reporting found ones.</p>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                    <li><a href="<?= BASE_URL ?>/browse.php?type=lost">Lost Items</a></li>
                    <li><a href="<?= BASE_URL ?>/browse.php?type=found">Found Items</a></li>
                    <li><a href="<?= BASE_URL ?>/report-lost.php">Report Lost</a></li>
                    <li><a href="<?= BASE_URL ?>/report-found.php">Report Found</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Account</h4>
                <ul>
                    <?php if (isLoggedIn()): ?>
                    <li><a href="<?= BASE_URL ?>/dashboard.php">My Dashboard</a></li>
                    <li><a href="<?= BASE_URL ?>/logout.php">Logout</a></li>
                    <?php else: ?>
                    <li><a href="<?= BASE_URL ?>/login.php">Login</a></li>
                    <li><a href="<?= BASE_URL ?>/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact</h4>
                <p><i class="fas fa-envelope"></i> admin@lostandfound.com</p>
                <p><i class="fas fa-map-marker-alt"></i> Your Institution Name</p>
                <p><i class="fas fa-phone"></i> +91 99999 99999</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. DBMS Lab Mini Project.</p>
        </div>
    </div>
</footer>
