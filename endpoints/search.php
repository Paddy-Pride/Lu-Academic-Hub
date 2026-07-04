<?php
/**
 * Search API Endpoints
 */

header('Content-Type: application/json');
require_once '../config/Database.php';
require_once '../models/Search.php';

$pdo = new Database();
$pdo = $pdo->connect();

$search = new Search($pdo);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'GET') {
        switch ($action) {
            case 'global':
                $query = $_GET['q'] ?? '';
                if (empty($query)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Query parameter required']);
                    break;
                }
                $page = $_GET['page'] ?? 1;
                $limit = $_GET['limit'] ?? 20;
                $result = $search->globalSearch($query, $page, $limit);
                echo json_encode($result);
                break;

            case 'advanced':
                $filters = [
                    'keyword' => $_GET['keyword'] ?? '',
                    'course_code' => $_GET['course_code'] ?? '',
                    'faculty' => $_GET['faculty'] ?? '',
                    'year' => $_GET['year'] ?? '',
                    'semester' => $_GET['semester'] ?? '',
                    'material_type' => $_GET['type'] ?? '',
                    'difficulty' => $_GET['difficulty'] ?? '',
                    'tag' => $_GET['tag'] ?? '',
                    'sort_by' => $_GET['sort'] ?? 'recent',
                    'search_in' => $_GET['search_in'] ?? 'all'
                ];
                $page = $_GET['page'] ?? 1;
                $limit = $_GET['limit'] ?? 20;
                $result = $search->advancedSearch($filters, $page, $limit);
                echo json_encode($result);
                break;

            case 'suggestions':
                $query = $_GET['q'] ?? '';
                if (empty($query)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Query parameter required']);
                    break;
                }
                $suggestions = $search->getSearchSuggestions($query);
                echo json_encode(['data' => $suggestions]);
                break;

            case 'trending':
                $trending = $search->getTrendingSearches();
                echo json_encode(['data' => $trending]);
                break;

            case 'courses':
                $courses = $search->getCourseOptions();
                echo json_encode(['data' => $courses]);
                break;

            case 'faculties':
                $faculties = $search->getFacultyOptions();
                echo json_encode(['data' => $faculties]);
                break;

            case 'types':
                $types = $search->getMaterialTypes();
                echo json_encode(['data' => $types]);
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>