<?php
/**
 * Search Model
 * Handles advanced search and filtering across past papers and learning materials
 */

class Search {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Global search across all resources
     */
    public function globalSearch($query, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $search_term = '%' . $query . '%';

        // Search in past papers
        $papers_query = "SELECT id, title, course_code, course_name, description, 'paper' as type, created_at, downloads_count 
                        FROM past_papers 
                        WHERE (MATCH(title, course_name, description) AGAINST(? IN BOOLEAN MODE) 
                               OR title LIKE ? 
                               OR course_code LIKE ?) 
                        AND is_approved = true
                        ORDER BY downloads_count DESC
                        LIMIT ? OFFSET ?";

        // Search in learning materials
        $materials_query = "SELECT id, title, course_code, course_name, description, 'material' as type, created_at, downloads_count 
                           FROM learning_materials 
                           WHERE (MATCH(title, course_name, description, tags) AGAINST(? IN BOOLEAN MODE) 
                                  OR title LIKE ? 
                                  OR course_code LIKE ?) 
                           AND is_approved = true
                           ORDER BY downloads_count DESC
                           LIMIT ? OFFSET ?";

        $papers_stmt = $this->pdo->prepare($papers_query);
        $materials_stmt = $this->pdo->prepare($materials_query);

        $papers_stmt->execute([$query, $search_term, $search_term, $limit, $offset]);
        $materials_stmt->execute([$query, $search_term, $search_term, $limit, $offset]);

        $results = array_merge(
            $papers_stmt->fetchAll(),
            $materials_stmt->fetchAll()
        );

        // Sort by relevance
        usort($results, function($a, $b) {
            return $b['downloads_count'] - $a['downloads_count'];
        });

        // Log search
        $this->logSearch($query);

        return [
            'results' => array_slice($results, 0, $limit),
            'count' => count($results),
            'page' => $page,
            'limit' => $limit
        ];
    }

    /**
     * Advanced search with multiple filters
     */
    public function advancedSearch($filters, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $all_results = [];

        // Search papers
        if ($filters['search_in'] === 'all' || $filters['search_in'] === 'papers') {
            $papers_results = $this->searchPapers($filters, $limit, $offset);
            $all_results = array_merge($all_results, $papers_results);
        }

        // Search materials
        if ($filters['search_in'] === 'all' || $filters['search_in'] === 'materials') {
            $materials_results = $this->searchMaterials($filters, $limit, $offset);
            $all_results = array_merge($all_results, $materials_results);
        }

        return [
            'results' => array_slice($all_results, 0, $limit),
            'count' => count($all_results),
            'page' => $page,
            'limit' => $limit
        ];
    }

    /**
     * Search past papers with filters
     */
    private function searchPapers($filters, $limit, $offset) {
        $query = "SELECT * FROM past_papers WHERE is_approved = true";
        $params = [];

        if (!empty($filters['keyword'])) {
            $query .= " AND MATCH(title, course_name, description) AGAINST(? IN BOOLEAN MODE)";
            $params[] = $filters['keyword'];
        }

        if (!empty($filters['course_code'])) {
            $query .= " AND course_code = ?";
            $params[] = $filters['course_code'];
        }

        if (!empty($filters['faculty'])) {
            $query .= " AND faculty = ?";
            $params[] = $filters['faculty'];
        }

        if (!empty($filters['year'])) {
            $query .= " AND year = ?";
            $params[] = (int)$filters['year'];
        }

        if (!empty($filters['semester'])) {
            $query .= " AND semester = ?";
            $params[] = $filters['semester'];
        }

        if (!empty($filters['difficulty'])) {
            $query .= " AND difficulty_level = ?";
            $params[] = $filters['difficulty'];
        }

        if (!empty($filters['sort_by'])) {
            if ($filters['sort_by'] === 'recent') {
                $query .= " ORDER BY created_at DESC";
            } elseif ($filters['sort_by'] === 'popular') {
                $query .= " ORDER BY downloads_count DESC";
            } elseif ($filters['sort_by'] === 'rated') {
                $query .= " ORDER BY rating DESC";
            }
        } else {
            $query .= " ORDER BY created_at DESC";
        }

        $query .= " LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Search learning materials with filters
     */
    private function searchMaterials($filters, $limit, $offset) {
        $query = "SELECT * FROM learning_materials WHERE is_approved = true";
        $params = [];

        if (!empty($filters['keyword'])) {
            $query .= " AND MATCH(title, course_name, description, tags) AGAINST(? IN BOOLEAN MODE)";
            $params[] = $filters['keyword'];
        }

        if (!empty($filters['course_code'])) {
            $query .= " AND course_code = ?";
            $params[] = $filters['course_code'];
        }

        if (!empty($filters['material_type'])) {
            $query .= " AND material_type = ?";
            $params[] = $filters['material_type'];
        }

        if (!empty($filters['faculty'])) {
            $query .= " AND faculty = ?";
            $params[] = $filters['faculty'];
        }

        if (!empty($filters['tag'])) {
            $query .= " AND tags LIKE ?";
            $params[] = '%' . $filters['tag'] . '%';
        }

        if (!empty($filters['difficulty'])) {
            $query .= " AND difficulty_level = ?";
            $params[] = $filters['difficulty'];
        }

        if (!empty($filters['sort_by'])) {
            if ($filters['sort_by'] === 'recent') {
                $query .= " ORDER BY created_at DESC";
            } elseif ($filters['sort_by'] === 'popular') {
                $query .= " ORDER BY downloads_count DESC";
            } elseif ($filters['sort_by'] === 'rated') {
                $query .= " ORDER BY rating DESC";
            }
        } else {
            $query .= " ORDER BY created_at DESC";
        }

        $query .= " LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Log search query
     */
    private function logSearch($query, $user_id = null, $results_count = 0) {
        $insert_query = "INSERT INTO search_history (user_id, search_query, results_count, ip_address) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($insert_query);
        
        try {
            $stmt->execute([
                $user_id,
                $query,
                $results_count,
                $_SERVER['REMOTE_ADDR'] ?? null
            ]);
        } catch (PDOException $e) {
            // Log search query failure silently
        }
    }

    /**
     * Get search suggestions
     */
    public function getSearchSuggestions($query, $limit = 10) {
        $search_term = $query . '%';

        $query_str = "SELECT DISTINCT title FROM past_papers WHERE title LIKE ? AND is_approved = true
                     UNION
                     SELECT DISTINCT title FROM learning_materials WHERE title LIKE ? AND is_approved = true
                     LIMIT ?";

        $stmt = $this->pdo->prepare($query_str);
        $stmt->execute([$search_term, $search_term, $limit]);
        
        return $stmt->fetchAll();
    }

    /**
     * Get trending searches
     */
    public function getTrendingSearches($limit = 10) {
        $query = "SELECT search_query, COUNT(*) as count 
                 FROM search_history 
                 WHERE searched_at > NOW() - INTERVAL 30 DAY
                 GROUP BY search_query
                 ORDER BY count DESC
                 LIMIT ?";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
    }

    /**
     * Get course options for filtering
     */
    public function getCourseOptions() {
        $query = "SELECT DISTINCT course_code, course_name FROM past_papers WHERE is_approved = true
                 UNION
                 SELECT DISTINCT course_code, course_name FROM learning_materials WHERE is_approved = true
                 ORDER BY course_code";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get faculty options for filtering
     */
    public function getFacultyOptions() {
        $query = "SELECT DISTINCT faculty FROM past_papers WHERE is_approved = true AND faculty IS NOT NULL
                 UNION
                 SELECT DISTINCT faculty FROM learning_materials WHERE is_approved = true AND faculty IS NOT NULL
                 ORDER BY faculty";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get available material types
     */
    public function getMaterialTypes() {
        return [
            'notes' => 'Study Notes',
            'textbook' => 'Textbook',
            'video' => 'Video',
            'tutorial' => 'Tutorial',
            'guide' => 'Study Guide',
            'other' => 'Other'
        ];
    }
}
?>