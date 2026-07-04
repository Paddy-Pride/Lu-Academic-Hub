<?php
/**
 * Learning Materials API Endpoints
 */

header('Content-Type: application/json');
require_once '../config/Database.php';
require_once '../models/LearningMaterial.php';
require_once '../api/FileUploadHandler.php';
require_once '../api/RatingReviewHandler.php';
require_once '../api/BookmarkHandler.php';

$pdo = new Database();
$pdo = $pdo->connect();

$material = new LearningMaterial($pdo);
$fileHandler = new FileUploadHandler($pdo);
$ratingHandler = new RatingReviewHandler($pdo);
$bookmarkHandler = new BookmarkHandler($pdo);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;

try {
    switch ($method) {
        case 'GET':
            handleGETRequest($action);
            break;
        case 'POST':
            handlePOSTRequest($action);
            break;
        case 'PUT':
            handlePUTRequest($action);
            break;
        case 'DELETE':
            handleDELETERequest($action);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

function handleGETRequest($action) {
    global $material, $ratingHandler, $bookmarkHandler, $user_id;

    switch ($action) {
        case 'list':
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 12;
            $filters = [
                'course_code' => $_GET['course_code'] ?? '',
                'material_type' => $_GET['type'] ?? '',
                'faculty' => $_GET['faculty'] ?? '',
                'year' => $_GET['year'] ?? '',
                'difficulty' => $_GET['difficulty'] ?? '',
                'tag' => $_GET['tag'] ?? '',
                'search' => $_GET['search'] ?? ''
            ];
            $result = $material->getAll($page, $limit, $filters);
            echo json_encode($result);
            break;

        case 'detail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                break;
            }
            $mat = $material->getById($id);
            if (!$mat) {
                http_response_code(404);
                echo json_encode(['error' => 'Material not found']);
                break;
            }
            // Get reviews
            $reviews = $ratingHandler->getResourceReviews($id, 'learning_material');
            $summary = $ratingHandler->getRatingSummary($id, 'learning_material');
            $mat['reviews'] = $reviews;
            $mat['rating_summary'] = $summary;
            $mat['is_bookmarked'] = $user_id ? $bookmarkHandler->isBookmarked($user_id, $id, 'learning_material') : false;
            echo json_encode($mat);
            break;

        case 'trending':
            $limit = $_GET['limit'] ?? 10;
            $trending = $material->getTrending($limit);
            echo json_encode(['data' => $trending]);
            break;

        case 'bytype':
            $type = $_GET['type'] ?? null;
            if (!$type) {
                http_response_code(400);
                echo json_encode(['error' => 'Type required']);
                break;
            }
            $page = $_GET['page'] ?? 1;
            $result = $material->getByType($type, $page);
            echo json_encode($result);
            break;

        case 'user':
            if (!$user_id) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                break;
            }
            $page = $_GET['page'] ?? 1;
            $result = $material->getByUser($user_id, $page);
            echo json_encode($result);
            break;

        case 'tags':
            $tags = $material->getAllTags();
            echo json_encode(['data' => $tags]);
            break;

        case 'statistics':
            $stats = $material->getStatistics();
            echo json_encode($stats);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function handlePOSTRequest($action) {
    global $material, $fileHandler, $ratingHandler, $bookmarkHandler, $user_id, $pdo;

    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    switch ($action) {
        case 'create':
            if (!isset($_FILES['file'])) {
                http_response_code(400);
                echo json_encode(['error' => 'File required']);
                break;
            }

            $upload = $fileHandler->uploadFile($_FILES['file'], $user_id, 'material');
            if (!$upload['success']) {
                http_response_code(400);
                echo json_encode($upload);
                break;
            }

            $data['file_path'] = $upload['file_path'];
            $data['file_size'] = $upload['file_size'];
            $data['file_type'] = $upload['file_type'];
            $data['uploaded_by'] = $user_id;

            $result = $material->create($data);
            http_response_code($result['success'] ? 201 : 400);
            echo json_encode($result);
            break;

        case 'download':
            $id = $data['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                break;
            }
            $material->incrementDownloads($id);
            // Log download
            $query = "INSERT INTO download_history (user_id, resource_id, resource_type, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$user_id, $id, 'learning_material', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
            echo json_encode(['success' => true, 'message' => 'Download recorded']);
            break;

        case 'rate':
            $resource_id = $data['resource_id'] ?? null;
            $rating = $data['rating'] ?? null;
            $review = $data['review'] ?? null;
            
            if (!$resource_id || !$rating) {
                http_response_code(400);
                echo json_encode(['error' => 'Resource ID and rating required']);
                break;
            }
            
            $result = $ratingHandler->submitRating($user_id, $resource_id, 'learning_material', $rating, $review);
            echo json_encode($result);
            break;

        case 'bookmark':
            $resource_id = $data['resource_id'] ?? null;
            $folder = $data['folder'] ?? 'default';
            
            if (!$resource_id) {
                http_response_code(400);
                echo json_encode(['error' => 'Resource ID required']);
                break;
            }
            
            $result = $bookmarkHandler->addBookmark($user_id, $resource_id, 'learning_material', $folder);
            echo json_encode($result);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function handlePUTRequest($action) {
    global $material, $user_id;

    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID required']);
        return;
    }

    switch ($action) {
        case 'update':
            $result = $material->update($id, $data);
            echo json_encode($result);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function handleDELETERequest($action) {
    global $material, $bookmarkHandler, $user_id;

    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }

    $id = $_GET['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID required']);
        return;
    }

    switch ($action) {
        case 'delete':
            $result = $material->delete($id);
            echo json_encode($result);
            break;

        case 'unbookmark':
            $result = $bookmarkHandler->removeBookmark($user_id, $id, 'learning_material');
            echo json_encode($result);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}
?>