<?php
/**
 * Admin Management API Endpoints
 */

header('Content-Type: application/json');
require_once '../config/Database.php';
require_once '../models/User.php';
require_once '../models/PastPaper.php';
require_once '../models/LearningMaterial.php';
require_once '../api/AuthHandler.php';

session_start();

$pdo = new Database();
$pdo = $pdo->connect();

$auth = new AuthHandler($pdo);
$user = new User($pdo);
$pastPaper = new PastPaper($pdo);
$material = new LearningMaterial($pdo);

// Check admin privileges
if (!$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - Admin access required']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$admin_id = $auth->getUserId();

try {
    switch ($method) {
        case 'GET':
            handleAdminGET($action);
            break;
        case 'POST':
            handleAdminPOST($action);
            break;
        case 'PUT':
            handleAdminPUT($action);
            break;
        case 'DELETE':
            handleAdminDELETE($action);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

function handleAdminGET($action) {
    global $user, $pdo;

    switch ($action) {
        case 'users':
            $page = $_GET['page'] ?? 1;
            $result = $user->getAll($page);
            echo json_encode($result);
            break;

        case 'pending-papers':
            $query = "SELECT * FROM past_papers WHERE is_approved = false ORDER BY created_at ASC";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            echo json_encode(['data' => $stmt->fetchAll()]);
            break;

        case 'pending-materials':
            $query = "SELECT * FROM learning_materials WHERE is_approved = false ORDER BY created_at ASC";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            echo json_encode(['data' => $stmt->fetchAll()]);
            break;

        case 'statistics':
            $stats = [
                'total_users' => $getTotalUsers($pdo),
                'total_papers' => $getTotalPapers($pdo),
                'total_materials' => $getTotalMaterials($pdo),
                'pending_approvals' => $getPendingApprovals($pdo)
            ];
            echo json_encode($stats);
            break;

        case 'logs':
            $limit = $_GET['limit'] ?? 50;
            $query = "SELECT * FROM admin_logs ORDER BY logged_at DESC LIMIT ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$limit]);
            echo json_encode(['data' => $stmt->fetchAll()]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function handleAdminPOST($action) {
    global $pastPaper, $material, $admin_id, $pdo;
    $data = json_decode(file_get_contents('php://input'), true);

    switch ($action) {
        case 'approve-paper':
            $id = $data['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                break;
            }
            
            $result = $pastPaper->approve($id, $admin_id);
            logAdminAction('APPROVE_PAPER', 'past_papers', $id, $admin_id, $pdo);
            echo json_encode($result);
            break;

        case 'approve-material':
            $id = $data['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                break;
            }
            
            $result = $material->approve($id, $admin_id);
            logAdminAction('APPROVE_MATERIAL', 'learning_materials', $id, $admin_id, $pdo);
            echo json_encode($result);
            break;

        case 'reject-paper':
            $id = $data['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                break;
            }
            
            $query = "DELETE FROM past_papers WHERE id = ? AND is_approved = false";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$id]);
            logAdminAction('REJECT_PAPER', 'past_papers', $id, $admin_id, $pdo, $data['reason'] ?? 'Not specified');
            echo json_encode(['success' => true, 'message' => 'Paper rejected']);
            break;

        case 'reject-material':
            $id = $data['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                break;
            }
            
            $query = "DELETE FROM learning_materials WHERE id = ? AND is_approved = false";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$id]);
            logAdminAction('REJECT_MATERIAL', 'learning_materials', $id, $admin_id, $pdo, $data['reason'] ?? 'Not specified');
            echo json_encode(['success' => true, 'message' => 'Material rejected']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function handleAdminPUT($action) {
    global $user, $admin_id, $pdo;
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['user_id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'User ID required']);
        return;
    }

    switch ($action) {
        case 'update-user':
            $result = $user->updateProfile($id, $data);
            logAdminAction('UPDATE_USER', 'users', $id, $admin_id, $pdo);
            echo json_encode($result);
            break;

        case 'set-role':
            $role = $data['role'] ?? null;
            if (!in_array($role, ['student', 'moderator', 'admin'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid role']);
                break;
            }
            
            $query = "UPDATE users SET role = ? WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$role, $id]);
            logAdminAction('SET_USER_ROLE', 'users', $id, $admin_id, $pdo);
            echo json_encode(['success' => true, 'message' => 'User role updated']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function handleAdminDELETE($action) {
    global $admin_id, $pdo;
    $id = $_GET['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID required']);
        return;
    }

    switch ($action) {
        case 'deactivate-user':
            $query = "UPDATE users SET is_active = false WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$id]);
            logAdminAction('DEACTIVATE_USER', 'users', $id, $admin_id, $pdo);
            echo json_encode(['success' => true, 'message' => 'User deactivated']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function logAdminAction($action, $entity_type, $entity_id, $admin_id, $pdo, $message = null) {
    $query = "INSERT INTO admin_logs (admin_id, action, entity_type, entity_id, ip_address, user_agent, log_message) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        $admin_id,
        $action,
        $entity_type,
        $entity_id,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $message
    ]);
}

function getTotalUsers($pdo) {
    $query = "SELECT COUNT(*) as count FROM users";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

function getTotalPapers($pdo) {
    $query = "SELECT COUNT(*) as count FROM past_papers WHERE is_approved = true";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

function getTotalMaterials($pdo) {
    $query = "SELECT COUNT(*) as count FROM learning_materials WHERE is_approved = true";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

function getPendingApprovals($pdo) {
    $papers = $getTotalPapers = $pdo->query("SELECT COUNT(*) as count FROM past_papers WHERE is_approved = false")->fetch()['count'] ?? 0;
    $materials = $getMaterialCount = $pdo->query("SELECT COUNT(*) as count FROM learning_materials WHERE is_approved = false")->fetch()['count'] ?? 0;
    return $papers + $materials;
}
?>