<?php
/**
 * Past Papers API Endpoints
 */

header('Content-Type: application/json');
require_once '../config/Database.php';
require_once '../models/PastPaper.php';
require_once '../api/FileUploadHandler.php';
require_once '../api/RatingReviewHandler.php';
require_once '../api/BookmarkHandler.php';

$pdo = new Database();
$pdo = $pdo->connect();

$pastPaper = new PastPaper($pdo);
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
    global $pastPaper, $ratingHandler, $bookmarkHandler, $user_id;

    switch ($action) {
        case 'list':
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 12;
            $filters = [
                'course_code' => $_GET['course_code'] ?? '',
                'faculty' => $_GET['faculty'] ?? '',
                'year' => $_GET['year'] ?? '',
                'semester' => $_GET['semester'] ?? '',
                'difficulty' => $_GET['difficulty'] ?? '',
                'search' => $_GET['search'] ?? ''
            ];
            $result = $pastPaper->getAll($page, $limit, $filters);
            echo json_encode($result);
            break;

        case 'detail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                break;
            }
            $paper = $pastPaper->getById($id);
            if (!$paper) {
                http_response_code(404);
                echo json_encode(['error' => 'Paper not found']);
                break;
            }
            // Get reviews
            $reviews = $ratingHandler->getResourceReviews($id, 'past_paper');
            $summary = $ratingHandler->getRatingSummary($id, 'past_paper');
            $paper['reviews'] = $reviews;
            $paper['rating_summary'] = $summary;
            $paper['is_bookmarked'] = $user_id ? $bookmarkHandler->isBookmarked($user_id, $id, 'past_paper') : false;
            echo json_encode($paper);
            break;

        case 'trending':
            $limit = $_GET['limit'] ?? 10;
            $trending = $pastPaper->getTrending($limit);
            echo json_encode(['data' => $trending]);
            break;

        case 'user':
            if (!$user_id) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                break;
            }
            $page = $_GET['page'] ?? 1;
            $result = $pastPaper->getByUser($user_id, $page);
            echo json_encode($result);
            break;

        case 'statistics':
            $stats = $pastPaper->getStatistics();
            echo json_encode($stats);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function handlePOSTRequest($action) {
    global $pastPaper, $fileHandler, $ratingHandler, $bookmarkHandler, $user_id, $pdo;

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

            $upload = $fileHandler->uploadFile($_FILES['file'], $user_id, 'paper');
            if (!$upload['success']) {
                http_response_code(400);
                echo json_encode($upload);
                break;
            }

            $data['file_path'] = $upload['file_path'];
            $data['file_size'] = $upload['file_size'];
            $data['file_type'] = $upload['file_type'];
            $data['uploaded_by'] = $user_id;

            $result = $pastPaper->create($data);
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
            $pastPaper->incrementDownloads($id);
            // Log download
            $query = "INSERT INTO download_history (user_id, resource_id, resource_type, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$user_id, $id, 'past_paper', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
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
            
            $result = $ratingHandler->submitRating($user_id, $resource_id, 'past_paper', $rating, $review);
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
            
            $result = $bookmarkHandler->addBookmark($user_id, $resource_id, 'past_paper', $folder);
            echo json_encode($result);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function handlePUTRequest($action) {
    global $pastPaper, $user_id;

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
            $result = $pastPaper->update($id, $data);
            echo json_encode($result);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function handleDELETERequest($action) {
    global $pastPaper, $bookmarkHandler, $user_id;

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
            $result = $pastPaper->delete($id);
            echo json_encode($result);
            break;

        case 'unbookmark':
            $result = $bookmarkHandler->removeBookmark($user_id, $id, 'past_paper');
            echo json_encode($result);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}
?>