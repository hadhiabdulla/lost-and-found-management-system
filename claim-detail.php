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
$claim_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($claim_id === 0) {
    header('Location: dashboard.php');
    exit();
}

// Get claim details
$stmt = $conn->prepare("
    SELECT c.*, 
           i.name as item_name, i.description as item_description, i.status as item_status,
           i.user_id as item_owner_id,
           claimer.username as claimer_name, claimer.email as claimer_email, claimer.phone as claimer_phone,
           owner.username as owner_name, owner.email as owner_email, owner.phone as owner_phone
    FROM claims c
    JOIN items i ON c.item_id = i.id
    JOIN users claimer ON c.user_id = claimer.id
    JOIN users owner ON i.user_id = owner.id
    WHERE c.id = ?
");
$stmt->bind_param('i', $claim_id);
$stmt->execute();
$claim = $stmt->get_result()->fetch_assoc();

if (!$claim) {
    header('Location: dashboard.php');
    exit();
}

// Check if user has access to this claim
$is_owner = ($claim['item_owner_id'] == $user_id);
$is_claimer = ($claim['user_id'] == $user_id);

if (!$is_owner && !$is_claimer) {
    header('Location: dashboard.php');
    exit();
}

$success = '';
$error = '';

// Handle claim status update (only item owner can do this)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_owner) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE claims SET status = 'approved', updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('i', $claim_id);
        if ($stmt->execute()) {
            $success = 'Claim approved successfully!';
            $claim['status'] = 'approved';
        } else {
            $error = 'Failed to approve claim.';
        }
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE claims SET status = 'rejected', updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('i', $claim_id);
        if ($stmt->execute()) {
            $success = 'Claim rejected.';
            $claim['status'] = 'rejected';
        } else {
            $error = 'Failed to reject claim.';
        }
    }
}

$page_title = 'Claim Details';
include 'includes/navbar.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Claim Details</h1>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Claim Information -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Claim Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Status</h6>
                        <span class="badge bg-<?php echo $claim['status'] === 'pending' ? 'warning' : ($claim['status'] === 'approved' ? 'success' : 'danger'); ?> fs-6">
                            <?php echo ucfirst($claim['status']); ?>
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Item Being Claimed</h6>
                        <p>
                            <a href="item-detail.php?id=<?php echo $claim['item_id']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($claim['item_name']); ?>
                            </a>
                            <span class="badge bg-<?php echo $claim['item_status'] === 'lost' ? 'danger' : 'success'; ?> ms-2">
                                <?php echo ucfirst($claim['item_status']); ?>
                            </span>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Claim Reason</h6>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($claim['description'])); ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Submitted</h6>
                        <p class="text-muted"><?php echo date('F d, Y \\a\\t g:i A', strtotime($claim['created_at'])); ?></p>
                    </div>
                    
                    <?php if ($claim['updated_at'] !== $claim['created_at']): ?>
                        <div class="mb-3">
                            <h6>Last Updated</h6>
                            <p class="text-muted"><?php echo date('F d, Y \\a\\t g:i A', strtotime($claim['updated_at'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Contact Information & Actions -->
        <div class="col-lg-4 mb-4">
            <?php if ($is_owner): ?>
                <!-- Claimer Information -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Claimer Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($claim['claimer_name']); ?></p>
                        <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($claim['claimer_email']); ?>"><?php echo htmlspecialchars($claim['claimer_email']); ?></a></p>
                        <?php if ($claim['claimer_phone']): ?>
                            <p><strong>Phone:</strong> <a href="tel:<?php echo htmlspecialchars($claim['claimer_phone']); ?>"><?php echo htmlspecialchars($claim['claimer_phone']); ?></a></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Actions for Owner -->
                <?php if ($claim['status'] === 'pending'): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Actions</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Review the claim and decide whether to approve or reject it.</p>
                            <form method="POST" class="mb-2">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-success w-100">Approve Claim</button>
                            </form>
                            <form method="POST">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-danger w-100">Reject Claim</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Owner Information -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Item Owner Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($claim['owner_name']); ?></p>
                        <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($claim['owner_email']); ?>"><?php echo htmlspecialchars($claim['owner_email']); ?></a></p>
                        <?php if ($claim['owner_phone']): ?>
                            <p><strong>Phone:</strong> <a href="tel:<?php echo htmlspecialchars($claim['owner_phone']); ?>"><?php echo htmlspecialchars($claim['owner_phone']); ?></a></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Status Info for Claimer -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Claim Status</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($claim['status'] === 'pending'): ?>
                            <p class="text-muted">Your claim is pending review by the item owner. You will be notified when they respond.</p>
                        <?php elseif ($claim['status'] === 'approved'): ?>
                            <p class="text-success">Your claim has been approved! Please contact the item owner to arrange collection.</p>
                        <?php else: ?>
                            <p class="text-danger">Unfortunately, your claim has been rejected.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
