<?php
/**
 * User Authentication API Endpoints
 */

header('Content-Type: application/json');
require_once '../config/Database.php';
require_once '../models/User.php';
require_once '../api/AuthHandler.php';

session_start();

$pdo = new Database();
$pdo = $pdo->connect();

$user = new User($pdo);
$auth = new AuthHandler($pdo);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'POST':
            handleAuthRequest($action);
            break;
        case 'GET':
            handleAuthGET($action);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

function handleAuthRequest($action) {
    global $user, $auth;
    $data = json_decode(file_get_contents('php://input'), true);

    switch ($action) {
        case 'register':
            if (empty($data['email']) || empty($data['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Email and password required']);
                break;
            }

            // Validate email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid email format']);
                break;
            }

            // Validate password strength
            if (strlen($data['password']) < 6) {
                http_response_code(400);
                echo json_encode(['error' => 'Password must be at least 6 characters']);
                break;
            }

            $result = $user->register($data);
            http_response_code($result['success'] ? 201 : 400);
            echo json_encode($result);
            break;

        case 'login':
            if (empty($data['email']) || empty($data['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Email and password required']);
                break;
            }

            $result = $auth->login($data['email'], $data['password']);
            if ($result['success']) {
                $user_data = $auth->getCurrentUser();
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => $user_data
                ]);
            } else {
                http_response_code(401);
                echo json_encode($result);
            }
            break;

        case 'logout':
            $result = $auth->logout();
            echo json_encode($result);
            break;

        case 'update-profile':
            if (!$auth->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                break;
            }

            $result = $user->updateProfile($auth->getUserId(), $data);
            echo json_encode($result);
            break;

        case 'change-password':
            if (!$auth->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                break;
            }

            if (empty($data['current_password']) || empty($data['new_password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Current password and new password required']);
                break;
            }

            $result = $user->changePassword($auth->getUserId(), $data['current_password'], $data['new_password']);
            http_response_code($result['success'] ? 200 : 400);
            echo json_encode($result);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function handleAuthGET($action) {
    global $auth, $user;

    switch ($action) {
        case 'profile':
            if (!$auth->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                break;
            }
            
            $user_data = $auth->getCurrentUser();
            echo json_encode($user_data);
            break;

        case 'verify-session':
            if ($auth->checkSessionTimeout()) {
                echo json_encode(['authenticated' => true, 'user_id' => $auth->getUserId()]);
            } else {
                http_response_code(401);
                echo json_encode(['authenticated' => false]);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}
?>