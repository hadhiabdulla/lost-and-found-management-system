<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Require admin access
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Get statistics
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$stats['users'] = $result->fetch_assoc()['count'];

// Total items
$result = $conn->query("SELECT COUNT(*) as count FROM items");
$stats['items'] = $result->fetch_assoc()['count'];

// Lost items
$result = $conn->query("SELECT COUNT(*) as count FROM items WHERE status = 'lost'");
$stats['lost_items'] = $result->fetch_assoc()['count'];

// Found items
$result = $conn->query("SELECT COUNT(*) as count FROM items WHERE status = 'found'");
$stats['found_items'] = $result->fetch_assoc()['count'];

// Pending claims
$result = $conn->query("SELECT COUNT(*) as count FROM claims WHERE status = 'pending'");
$stats['pending_claims'] = $result->fetch_assoc()['count'];

// Get recent items
$recent_items = $conn->query("
    SELECT i.*, u.username, c.name as category_name 
    FROM items i
    JOIN users u ON i.user_id = u.id
    JOIN categories c ON i.category_id = c.id
    ORDER BY i.created_at DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Get recent users
$recent_users = $conn->query("
    SELECT id, username, email, created_at 
    FROM users 
    WHERE role = 'user'
    ORDER BY created_at DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Get recent claims
$recent_claims = $conn->query("
    SELECT c.*, i.name as item_name, u.username
    FROM claims c
    JOIN items i ON c.item_id = i.id
    JOIN users u ON c.user_id = u.id
    ORDER BY c.created_at DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

$page_title = 'Admin Panel';
include 'includes/navbar.php';
?>

<div class="container my-5">
    <h1 class="mb-4">Admin Dashboard</h1>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <h3 class="card-title"><?php echo $stats['users']; ?></h3>
                    <p class="card-text">Total Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-info text-white">
                <div class="card-body">
                    <h3 class="card-title"><?php echo $stats['items']; ?></h3>
                    <p class="card-text">Total Items</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-danger text-white">
                <div class="card-body">
                    <h3 class="card-title"><?php echo $stats['lost_items']; ?></h3>
                    <p class="card-text">Lost Items</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h3 class="card-title"><?php echo $stats['found_items']; ?></h3>
                    <p class="card-text">Found Items</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card text-center bg-warning text-dark">
                <div class="card-body">
                    <h3 class="card-title"><?php echo $stats['pending_claims']; ?></h3>
                    <p class="card-text">Pending Claims</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs" id="adminTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="items-tab" data-bs-toggle="tab" data-bs-target="#items" type="button">Recent Items</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button">Recent Users</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="claims-tab" data-bs-toggle="tab" data-bs-target="#claims" type="button">Recent Claims</button>
        </li>
    </ul>
    
    <div class="tab-content" id="adminTabContent">
        <!-- Recent Items -->
        <div class="tab-pane fade show active" id="items" role="tabpanel">
            <div class="table-responsive mt-3">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Reported By</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_items as $item): ?>
                            <tr>
                                <td><?php echo $item['id']; ?></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $item['status'] === 'lost' ? 'danger' : 'success'; ?>">
                                        <?php echo ucfirst($item['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($item['username']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                                <td>
                                    <a href="item-detail.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Users -->
        <div class="tab-pane fade" id="users" role="tabpanel">
            <div class="table-responsive mt-3">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Claims -->
        <div class="tab-pane fade" id="claims" role="tabpanel">
            <div class="table-responsive mt-3">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Item</th>
                            <th>Claimer</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_claims as $claim): ?>
                            <tr>
                                <td><?php echo $claim['id']; ?></td>
                                <td><?php echo htmlspecialchars($claim['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($claim['username']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $claim['status'] === 'pending' ? 'warning' : ($claim['status'] === 'approved' ? 'success' : 'danger'); ?>">
                                        <?php echo ucfirst($claim['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($claim['created_at'])); ?></td>
                                <td>
                                    <a href="claim-detail.php?id=<?php echo $claim['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
