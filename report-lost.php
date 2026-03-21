<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$categories = getAllCategories();
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id'       => $_SESSION['user_id'],
        'title'         => sanitize($_POST['title'] ?? ''),
        'description'   => sanitize($_POST['description'] ?? ''),
        'category_id'   => intval($_POST['category_id'] ?? 0),
        'location_lost' => sanitize($_POST['location_lost'] ?? ''),
        'date_lost'     => $_POST['date_lost'] ?? '',
    ];
    if (empty($data['title'])) {
        $error = 'Item title is required.';
    } else {
        $result = reportLostItem($data, $_FILES['image'] ?? null);
        if ($result['success']) {
            $success = 'Your lost item has been reported successfully!';
            header('Location: item-detail.php?type=lost&id=' . $result['id'] . '&msg=reported');
            exit;
        } else {
            $error = 'Failed to submit report. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Lost Item - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="page-header">
    <div class="container">
        <div class="breadcrumb"><a href="index.php">Home</a> &rsaquo; Report Lost Item</div>
        <h1><i class="fas fa-exclamation-triangle text-danger"></i> Report a Lost Item</h1>
        <p>Fill in the details about the item you've lost. The more specific, the better!</p>
    </div>
</div>
<div class="container" style="padding-bottom:48px;">
    <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
    <div class="form-card" style="max-width:680px;">
        <h2 class="form-title"><i class="fas fa-plus-circle"></i> Item Details</h2>
        <form method="POST" enctype="multipart/form-data" id="reportForm">
            <div class="form-group">
                <label for="title">Item Title *</label>
                <input type="text" id="title" name="title" placeholder="e.g. Black Samsung Galaxy S21" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id">
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_lost">Date Lost</label>
                    <input type="date" id="date_lost" name="date_lost" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['date_lost'] ?? date('Y-m-d')) ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="location_lost">Location Where Lost</label>
                <input type="text" id="location_lost" name="location_lost" placeholder="e.g. Central Library, 2nd Floor" value="<?= htmlspecialchars($_POST['location_lost'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Describe the item in detail - color, brand, distinguishing features, what was inside..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="image">Upload Image (optional)</label>
                <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this, 'imgPreview')">
                <img id="imgPreview" src="" style="display:none; max-width:200px; margin-top:8px; border-radius:8px;">
            </div>
            <button type="submit" class="btn btn-danger"><i class="fas fa-paper-plane"></i> Submit Lost Item Report</button>
            <a href="index.php" class="btn btn-outline" style="color:#64748b; border-color:#e2e8f0; margin-left:8px;">Cancel</a>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>
