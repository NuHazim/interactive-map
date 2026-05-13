<?php
// Database configuration - CORRECTED
define('DB_HOST', 'host'); // ✅ This is correct for InfinityFree
define('DB_USER', 'user');
define('DB_PASS', 'pass');
define('DB_NAME', 'name');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Define upload paths - FIXED FOR INFINITYFREE
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/uploads/');
define('UPLOAD_URL', 'https://interactive-map.page.gd/uploads/');

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Handle AJAX requests
if (isset($_POST['action']) || isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_map':
            getMapConfig($conn);
            break;
        case 'upload_map':
            uploadMap($conn);
            break;
        case 'get_points':
            getPoints($conn);
            break;
        case 'add_point':
            addPoint($conn);
            break;
        case 'update_point':
            updatePoint($conn);
            break;
        case 'delete_point':
            deletePoint($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

// API Functions
function getMapConfig($conn) {
    $result = $conn->query("SELECT * FROM map_config ORDER BY id DESC LIMIT 1");
    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No map found']);
    }
}

function uploadMap($conn) {
    if (!isset($_FILES['map_image'])) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded']);
        return;
    }
    
    if ($_FILES['map_image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Upload error code: ' . $_FILES['map_image']['error']]);
        return;
    }
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['map_image']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF allowed']);
        return;
    }
    
    $new_filename = 'map_' . time() . '.' . $ext;
    $upload_path = UPLOAD_DIR . $new_filename; // Server file path
    $db_url = UPLOAD_URL . $new_filename; // URL for database and display
    
    if (move_uploaded_file($_FILES['map_image']['tmp_name'], $upload_path)) {
        // Delete old map points when new map is uploaded
        $conn->query("DELETE FROM map_points");
        
        // Update or insert map config - STORE URL
        $stmt = $conn->prepare("INSERT INTO map_config (map_image) VALUES (?) ON DUPLICATE KEY UPDATE map_image = ?");
        $stmt->bind_param("ss", $db_url, $db_url);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Map uploaded successfully', 'path' => $db_url]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
    }
}

function getPoints($conn) {
    $result = $conn->query("SELECT * FROM map_points ORDER BY id ASC");
    $points = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $points[] = $row;
        }
    }
    
    echo json_encode(['success' => true, 'data' => $points]);
}

function addPoint($conn) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $link = $_POST['link'] ?? '';
    $x = $_POST['x_coordinate'] ?? 0;
    $y = $_POST['y_coordinate'] ?? 0;
    $color = $_POST['icon_color'] ?? '#FF0000';
    
    // Handle image upload
    $image_url = null;
    if (isset($_FILES['point_image']) && $_FILES['point_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['point_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = 'point_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $upload_path = UPLOAD_DIR . $new_filename;
            
            if (move_uploaded_file($_FILES['point_image']['tmp_name'], $upload_path)) {
                $image_url = UPLOAD_URL . $new_filename; // Store URL
            }
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO map_points (title, description, image, link, x_coordinate, y_coordinate, icon_color) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdds", $title, $description, $image_url, $link, $x, $y, $color);
    
    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        echo json_encode(['success' => true, 'message' => 'Point added successfully', 'id' => $new_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add point: ' . $conn->error]);
    }
    $stmt->close();
}

function updatePoint($conn) {
    $id = $_POST['id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $link = $_POST['link'] ?? '';
    $x = $_POST['x_coordinate'] ?? 0;
    $y = $_POST['y_coordinate'] ?? 0;
    $color = $_POST['icon_color'] ?? '#FF0000';
    
    // Get current image
    $result = $conn->query("SELECT image FROM map_points WHERE id = $id");
    $current_image = null;
    if ($result && $row = $result->fetch_assoc()) {
        $current_image = $row['image'];
    }
    
    // Handle image upload
    $image_url = $current_image;
    if (isset($_FILES['point_image']) && $_FILES['point_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['point_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = 'point_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $upload_path = UPLOAD_DIR . $new_filename;
            
            if (move_uploaded_file($_FILES['point_image']['tmp_name'], $upload_path)) {
                // Delete old image file
                if ($current_image) {
                    $old_filename = basename($current_image);
                    $old_path = UPLOAD_DIR . $old_filename;
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
                $image_url = UPLOAD_URL . $new_filename;
            }
        }
    }
    
    $stmt = $conn->prepare("UPDATE map_points SET title = ?, description = ?, image = ?, link = ?, x_coordinate = ?, y_coordinate = ?, icon_color = ? WHERE id = ?");
    $stmt->bind_param("ssssddsi", $title, $description, $image_url, $link, $x, $y, $color, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Point updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update point: ' . $conn->error]);
    }
    $stmt->close();
}

function deletePoint($conn) {
    $id = $_POST['id'] ?? 0;
    
    // Get image URL before deleting
    $result = $conn->query("SELECT image FROM map_points WHERE id = $id");
    if ($result && $row = $result->fetch_assoc()) {
        $image_url = $row['image'];
        if ($image_url) {
            $filename = basename($image_url);
            $file_path = UPLOAD_DIR . $filename;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
    
    $stmt = $conn->prepare("DELETE FROM map_points WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Point deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete point: ' . $conn->error]);
    }
    $stmt->close();
}
?>