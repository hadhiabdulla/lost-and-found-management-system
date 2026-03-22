<?php
require_once __DIR__ . '/../config/db.php';

function getAllCategories() {
    $conn = getDBConnection();
    $result = $conn->query('SELECT * FROM categories ORDER BY name');
    $cats = $result->fetch_all(MYSQLI_ASSOC);
    $conn->close();
    return $cats;
}

function getLostItems($filters = [], $limit = 20, $offset = 0) {
    $conn = getDBConnection();
    $where = ['1=1'];
    $params = [];
    $types = '';

    if (!empty($filters['category_id'])) {
        $where[] = 'l.category_id = ?';
        $params[] = $filters['category_id'];
        $types .= 'i';
    }
    if (!empty($filters['status'])) {
        $where[] = 'l.status = ?';
        $params[] = $filters['status'];
        $types .= 's';
    }
    if (!empty($filters['search'])) {
        $where[] = '(l.title LIKE ? OR l.description LIKE ? OR l.location_lost LIKE ?)';
        $search = '%' . $filters['search'] . '%';
        $params[] = $search; $params[] = $search; $params[] = $search;
        $types .= 'sss';
    }
    if (!empty($filters['user_id'])) {
        $where[] = 'l.user_id = ?';
        $params[] = $filters['user_id'];
        $types .= 'i';
    }

    $whereStr = implode(' AND ', $where);
    $sql = "SELECT l.*, u.name as user_name, c.name as category_name
            FROM lost_items l
            LEFT JOIN users u ON l.user_id = u.id
            LEFT JOIN categories c ON l.category_id = c.id
            WHERE $whereStr
            ORDER BY l.created_at DESC
            LIMIT ? OFFSET ?";
    $params[] = $limit; $params[] = $offset;
    $types .= 'ii';

    $stmt = $conn->prepare($sql);
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close(); $conn->close();
    return $items;
}

function getFoundItems($filters = [], $limit = 20, $offset = 0) {
    $conn = getDBConnection();
    $where = ['1=1'];
    $params = [];
    $types = '';

    if (!empty($filters['category_id'])) {
        $where[] = 'f.category_id = ?';
        $params[] = $filters['category_id'];
        $types .= 'i';
    }
    if (!empty($filters['status'])) {
        $where[] = 'f.status = ?';
        $params[] = $filters['status'];
        $types .= 's';
    }
    if (!empty($filters['search'])) {
        $where[] = '(f.title LIKE ? OR f.description LIKE ? OR f.location_found LIKE ?)';
        $search = '%' . $filters['search'] . '%';
        $params[] = $search; $params[] = $search; $params[] = $search;
        $types .= 'sss';
    }
    if (!empty($filters['user_id'])) {
        $where[] = 'f.user_id = ?';
        $params[] = $filters['user_id'];
        $types .= 'i';
    }

    $whereStr = implode(' AND ', $where);
    $sql = "SELECT f.*, u.name as user_name, c.name as category_name
            FROM found_items f
            LEFT JOIN users u ON f.user_id = u.id
            LEFT JOIN categories c ON f.category_id = c.id
            WHERE $whereStr
            ORDER BY f.created_at DESC
            LIMIT ? OFFSET ?";
    $params[] = $limit; $params[] = $offset;
    $types .= 'ii';

    $stmt = $conn->prepare($sql);
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close(); $conn->close();
    return $items;
}

function getItemById($table, $id) {
    $conn = getDBConnection();
    $loc = ($table === 'lost_items') ? 'location_lost' : 'location_found';
    $stmt = $conn->prepare("SELECT t.*, u.name as user_name, u.email as user_email, u.phone as user_phone, c.name as category_name
        FROM $table t
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    $stmt->close(); $conn->close();
    return $item;
}

function uploadImage($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > MAX_FILE_SIZE) return null;
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) return null;

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_') . '.' . strtolower($ext);
    $dest = UPLOAD_DIR . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) return $filename;
    return null;
}

function reportLostItem($data, $imageFile = null) {
    $conn = getDBConnection();
    $imagePath = uploadImage($imageFile);
    $stmt = $conn->prepare('INSERT INTO lost_items (user_id, title, description, category_id, location_lost, date_lost, image_path) VALUES (?,?,?,?,?,?,?)');
    $stmt->bind_param('issssss',
        $data['user_id'], $data['title'], $data['description'],
        $data['category_id'], $data['location_lost'], $data['date_lost'], $imagePath
    );
    $ok = $stmt->execute();
    $id = $conn->insert_id;
    $stmt->close(); $conn->close();
    if ($ok) {
        findMatches($id, 'lost');
        return ['success' => true, 'id' => $id];
    }
    return ['success' => false];
}

function reportFoundItem($data, $imageFile = null) {
    $conn = getDBConnection();
    $imagePath = uploadImage($imageFile);
    $stmt = $conn->prepare('INSERT INTO found_items (user_id, title, description, category_id, location_found, date_found, image_path) VALUES (?,?,?,?,?,?,?)');
    $stmt->bind_param('issssss',
        $data['user_id'], $data['title'], $data['description'],
        $data['category_id'], $data['location_found'], $data['date_found'], $imagePath
    );
    $ok = $stmt->execute();
    $id = $conn->insert_id;
    $stmt->close(); $conn->close();
    if ($ok) {
        findMatches($id, 'found');
        return ['success' => true, 'id' => $id];
    }
    return ['success' => false];
}

function findMatches($itemId, $type) {
    $conn = getDBConnection();
    if ($type === 'lost') {
        $item = getItemById('lost_items', $itemId);
        if (!$item) return;
        $stmt = $conn->prepare('SELECT id FROM found_items WHERE category_id = ? AND status = "available"');
        $stmt->bind_param('i', $item['category_id']);
        $stmt->execute();
        $results = $stmt->get_result();
        while ($row = $results->fetch_assoc()) {
            $ins = $conn->prepare('INSERT IGNORE INTO matches (lost_item_id, found_item_id, match_score) VALUES (?,?,?)');
            $score = 70.00;
            $ins->bind_param('iid', $itemId, $row['id'], $score);
            $ins->execute(); $ins->close();
        }
        $stmt->close();
    } else {
        $item = getItemById('found_items', $itemId);
        if (!$item) return;
        $stmt = $conn->prepare('SELECT id, user_id FROM lost_items WHERE category_id = ? AND status = "open"');
        $stmt->bind_param('i', $item['category_id']);
        $stmt->execute();
        $results = $stmt->get_result();
        while ($row = $results->fetch_assoc()) {
            $ins = $conn->prepare('INSERT IGNORE INTO matches (lost_item_id, found_item_id, match_score) VALUES (?,?,?)');
            $score = 70.00;
            $ins->bind_param('iid', $row['id'], $itemId, $score);
            $ins->execute(); $ins->close();
            // Notify lost item owner
            addNotification($row['user_id'], 'A possible match was found for your lost item!', 'match');
        }
        $stmt->close();
    }
    $conn->close();
}

function addNotification($userId, $message, $type = 'general') {
    $conn = getDBConnection();
    $stmt = $conn->prepare('INSERT INTO notifications (user_id, message, type) VALUES (?,?,?)');
    $stmt->bind_param('iss', $userId, $message, $type);
    $stmt->execute(); $stmt->close(); $conn->close();
}

function getUnreadNotifications($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare('SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $notifs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close(); $conn->close();
    return $notifs;
}

function markNotificationsRead($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute(); $stmt->close(); $conn->close();
}

function getDashboardStats() {
    $conn = getDBConnection();
    $stats = [];
    $stats['total_lost'] = $conn->query('SELECT COUNT(*) as c FROM lost_items')->fetch_assoc()['c'];
    $stats['total_found'] = $conn->query('SELECT COUNT(*) as c FROM found_items')->fetch_assoc()['c'];
    $stats['total_resolved'] = $conn->query('SELECT COUNT(*) as c FROM lost_items WHERE status="resolved"')->fetch_assoc()['c'];
    $stats['pending_claims'] = $conn->query('SELECT COUNT(*) as c FROM claims WHERE status="pending"')->fetch_assoc()['c'];
    $stats['total_users'] = $conn->query('SELECT COUNT(*) as c FROM users WHERE role="user"')->fetch_assoc()['c'];
    $conn->close();
    return $stats;
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff/60) . ' min ago';
    if ($diff < 86400) return floor($diff/3600) . ' hours ago';
    if ($diff < 604800) return floor($diff/86400) . ' days ago';
    return date('d M Y', $time);
}
?>

