<?php
/**
 * Past Papers Model
 * Handles all past paper operations
 */

class PastPaper {
    private $pdo;
    private $table = 'past_papers';

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get all past papers with pagination and filters
     */
    public function getAll($page = 1, $limit = 12, $filters = []) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT * FROM {$this->table} WHERE is_approved = true";
        $params = [];

        if (!empty($filters['course_code'])) {
            $query .= " AND course_code LIKE ?";
            $params[] = '%' . $filters['course_code'] . '%';
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

        if (!empty($filters['search'])) {
            $query .= " AND MATCH(title, course_name, description) AGAINST(? IN BOOLEAN MODE)";
            $params[] = $filters['search'];
        }

        $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        
        return [
            'papers' => $stmt->fetchAll(),
            'page' => $page,
            'limit' => $limit,
            'total' => $this->getFilteredCount($filters)
        ];
    }

    /**
     * Get single past paper by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ? AND is_approved = true";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$id]);
        
        $paper = $stmt->fetch();
        if ($paper) {
            $this->incrementViews($id);
        }
        return $paper;
    }

    /**
     * Create new past paper
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
        (title, course_code, course_name, faculty, year, semester, file_path, file_size, file_type, uploaded_by, description, difficulty_level)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($query);
        
        try {
            $stmt->execute([
                $data['title'],
                $data['course_code'],
                $data['course_name'],
                $data['faculty'],
                $data['year'],
                $data['semester'],
                $data['file_path'],
                $data['file_size'],
                $data['file_type'],
                $data['uploaded_by'],
                $data['description'] ?? null,
                $data['difficulty_level'] ?? 'medium'
            ]);

            return [
                'success' => true,
                'id' => $this->pdo->lastInsertId(),
                'message' => 'Past paper uploaded successfully. Awaiting approval.'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error creating past paper: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update past paper
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, ['title', 'description', 'difficulty_level'])) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }

        if (empty($fields)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }

        $params[] = $id;
        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->pdo->prepare($query);
        
        try {
            $stmt->execute($params);
            return ['success' => true, 'message' => 'Past paper updated successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating past paper'];
        }
    }

    /**
     * Delete past paper
     */
    public function delete($id) {
        $paper = $this->getById($id);
        
        if (!$paper) {
            return ['success' => false, 'message' => 'Past paper not found'];
        }

        // Delete file from storage
        if (file_exists($paper['file_path'])) {
            unlink($paper['file_path']);
        }

        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($query);

        try {
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Past paper deleted successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting past paper'];
        }
    }

    /**
     * Approve past paper (admin only)
     */
    public function approve($id, $admin_id) {
        $query = "UPDATE {$this->table} SET is_approved = true, approved_by = ?, approved_at = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($query);

        try {
            $stmt->execute([$admin_id, $id]);
            return ['success' => true, 'message' => 'Past paper approved'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error approving past paper'];
        }
    }

    /**
     * Increment download count
     */
    public function incrementDownloads($id) {
        $query = "UPDATE {$this->table} SET downloads_count = downloads_count + 1 WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Increment views count
     */
    public function incrementViews($id) {
        $query = "UPDATE {$this->table} SET views_count = views_count + 1 WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Get trending past papers
     */
    public function getTrending($limit = 10) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE is_approved = true 
                  ORDER BY downloads_count DESC 
                  LIMIT ?";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get papers by user
     */
    public function getByUser($user_id, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $query = "SELECT * FROM {$this->table} WHERE uploaded_by = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$user_id, $limit, $offset]);
        
        return [
            'papers' => $stmt->fetchAll(),
            'page' => $page,
            'limit' => $limit
        ];
    }

    /**
     * Get filtered count
     */
    private function getFilteredCount($filters = []) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE is_approved = true";
        $params = [];

        if (!empty($filters['course_code'])) {
            $query .= " AND course_code LIKE ?";
            $params[] = '%' . $filters['course_code'] . '%';
        }

        if (!empty($filters['faculty'])) {
            $query .= " AND faculty = ?";
            $params[] = $filters['faculty'];
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] ?? 0;
    }

    /**
     * Get statistics
     */
    public function getStatistics() {
        $query = "SELECT 
                    COUNT(*) as total_papers,
                    SUM(downloads_count) as total_downloads,
                    SUM(views_count) as total_views,
                    AVG(rating) as average_rating
                  FROM {$this->table}
                  WHERE is_approved = true";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetch();
    }
}
?>