<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Require login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user statistics
$stats = [];

// Count reported lost items
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM items WHERE user_id = ? AND status = 'lost'");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stats['lost'] = $stmt->get_result()->fetch_assoc()['count'];

// Count reported found items
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM items WHERE user_id = ? AND status = 'found'");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stats['found'] = $stmt->get_result()->fetch_assoc()['count'];

// Count claims made
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM claims WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stats['claims'] = $stmt->get_result()->fetch_assoc()['count'];

// Count pending claims on user's items
$stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM claims c
    JOIN items i ON c.item_id = i.id
    WHERE i.user_id = ? AND c.status = 'pending'
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stats['pending_claims'] = $stmt->get_result()->fetch_assoc()['count'];

// Get user's recent items
$stmt = $conn->prepare("
    SELECT i.*, c.name as category_name, l.name as location_name
    FROM items i
    JOIN categories c ON i.category_id = c.id
    LEFT JOIN locations l ON i.location_id = l.id
    WHERE i.user_id = ?
    ORDER BY i.created_at DESC
    LIMIT 5
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$recent_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get claims on user's items
$stmt = $conn->prepare("
    SELECT c.*, i.name as item_name, u.username, u.email
    FROM claims c
    JOIN items i ON c.item_id = i.id
    JOIN users u ON c.user_id = u.id
    WHERE i.user_id = ?
    ORDER BY c.created_at DESC
    LIMIT 5
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$claims_received = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get user's claims
$stmt = $conn->prepare("
    SELECT c.*, i.name as item_name, u.username as owner_name
    FROM claims c
    JOIN items i ON c.item_id = i.id
    JOIN users u ON i.user_id = u.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
    LIMIT 5
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$claims_made = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = 'Dashboard';
include 'includes/navbar.php';
?>

<div class="container my-5">
    <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="card-title text-danger"><?php echo $stats['lost']; ?></h3>
                    <p class="card-text">Lost Items Reported</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="card-title text-success"><?php echo $stats['found']; ?></h3>
                    <p class="card-text">Found Items Reported</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="card-title text-primary"><?php echo $stats['claims']; ?></h3>
                    <p class="card-text">Claims Submitted</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="card-title text-warning"><?php echo $stats['pending_claims']; ?></h3>
                    <p class="card-text">Pending Claims</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="report-lost.php" class="btn btn-danger me-2">Report Lost Item</a>
                    <a href="report-found.php" class="btn btn-success me-2">Report Found Item</a>
                    <a href="browse.php" class="btn btn-primary me-2">Browse Items</a>
                    <a href="profile.php" class="btn btn-secondary">Edit Profile</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Items -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Your Recent Items</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_items)): ?>
                        <p class="text-muted">You haven't reported any items yet.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($recent_items as $item): ?>
                                <a href="item-detail.php?id=<?php echo $item['id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                        <small class="text-muted"><?php echo timeAgo($item['created_at']); ?></small>
                                    </div>
                                    <p class="mb-1">
                                        <span class="badge bg-<?php echo $item['status'] === 'lost' ? 'danger' : 'success'; ?>">
                                            <?php echo ucfirst($item['status']); ?>
                                        </span>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                    </p>
                                    <small class="text-muted"><?php echo htmlspecialchars(substr($item['description'], 0, 100)); ?>...</small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Claims Received -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Claims on Your Items</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($claims_received)): ?>
                        <p class="text-muted">No claims have been made on your items.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($claims_received as $claim): ?>
                                <a href="claim-detail.php?id=<?php echo $claim['id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($claim['item_name']); ?></h6>
                                        <small class="text-muted"><?php echo timeAgo($claim['created_at']); ?></small>
                                    </div>
                                    <p class="mb-1">
                                        <span class="badge bg-<?php echo $claim['status'] === 'pending' ? 'warning' : ($claim['status'] === 'approved' ? 'success' : 'danger'); ?>">
                                            <?php echo ucfirst($claim['status']); ?>
                                        </span>
                                    </p>
                                    <small class="text-muted">Claimed by: <?php echo htmlspecialchars($claim['username']); ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- User's Claims -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Your Claims</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($claims_made)): ?>
                        <p class="text-muted">You haven't made any claims yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Owner</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($claims_made as $claim): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($claim['item_name']); ?></td>
                                            <td><?php echo htmlspecialchars($claim['owner_name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $claim['status'] === 'pending' ? 'warning' : ($claim['status'] === 'approved' ? 'success' : 'danger'); ?>">
                                                    <?php echo ucfirst($claim['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo timeAgo($claim['created_at']); ?></td>
                                            <td>
                                                <a href="claim-detail.php?id=<?php echo $claim['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
