<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$categories = getAllCategories();
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id'        => $_SESSION['user_id'],
        'title'          => sanitize($_POST['title'] ?? ''),
        'description'    => sanitize($_POST['description'] ?? ''),
        'category_id'    => intval($_POST['category_id'] ?? 0),
        'location_found' => sanitize($_POST['location_found'] ?? ''),
        'date_found'     => $_POST['date_found'] ?? '',
    ];
    if (empty($data['title'])) {
        $error = 'Item title is required.';
    } else {
        $result = reportFoundItem($data, $_FILES['image'] ?? null);
        if ($result['success']) {
            header('Location: item-detail.php?type=found&id=' . $result['id'] . '&msg=reported');
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
    <title>Report Found Item - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="page-header">
    <div class="container">
        <div class="breadcrumb"><a href="index.php">Home</a> &rsaquo; Report Found Item</div>
        <h1><i class="fas fa-hand-holding text-success"></i> Report a Found Item</h1>
        <p>Help someone get their item back! Tell us what you found and where.</p>
    </div>
</div>
<div class="container" style="padding-bottom:48px;">
    <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>
    <div class="form-card" style="max-width:680px;">
        <h2 class="form-title"><i class="fas fa-plus-circle"></i> Found Item Details</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Item Title *</label>
                <input type="text" id="title" name="title" placeholder="e.g. Blue Jansport Backpack" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
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
                    <label for="date_found">Date Found</label>
                    <input type="date" id="date_found" name="date_found" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['date_found'] ?? date('Y-m-d')) ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="location_found">Location Where Found *</label>
                <input type="text" id="location_found" name="location_found" placeholder="e.g. Canteen near entrance" required value="<?= htmlspecialchars($_POST['location_found'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Describe the item - color, brand, contents, condition..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="image">Upload Image (optional)</label>
                <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this, 'imgPreview')">
                <img id="imgPreview" src="" style="display:none; max-width:200px; margin-top:8px; border-radius:8px;">
            </div>
            <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane"></i> Submit Found Item Report</button>
            <a href="index.php" class="btn btn-outline" style="color:#64748b; border-color:#e2e8f0; margin-left:8px;">Cancel</a>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>
