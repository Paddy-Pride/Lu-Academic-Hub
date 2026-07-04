<?php
/**
 * Learning Materials Model
 * Handles all learning material operations
 */

class LearningMaterial {
    private $pdo;
    private $table = 'learning_materials';

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get all learning materials with pagination and filters
     */
    public function getAll($page = 1, $limit = 12, $filters = []) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT * FROM {$this->table} WHERE is_approved = true";
        $params = [];

        if (!empty($filters['course_code'])) {
            $query .= " AND course_code LIKE ?";
            $params[] = '%' . $filters['course_code'] . '%';
        }

        if (!empty($filters['material_type'])) {
            $query .= " AND material_type = ?";
            $params[] = $filters['material_type'];
        }

        if (!empty($filters['faculty'])) {
            $query .= " AND faculty = ?";
            $params[] = $filters['faculty'];
        }

        if (!empty($filters['year'])) {
            $query .= " AND year = ?";
            $params[] = (int)$filters['year'];
        }

        if (!empty($filters['difficulty'])) {
            $query .= " AND difficulty_level = ?";
            $params[] = $filters['difficulty'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND MATCH(title, course_name, description, tags) AGAINST(? IN BOOLEAN MODE)";
            $params[] = $filters['search'];
        }

        if (!empty($filters['tag'])) {
            $query .= " AND tags LIKE ?";
            $params[] = '%' . $filters['tag'] . '%';
        }

        $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        
        return [
            'materials' => $stmt->fetchAll(),
            'page' => $page,
            'limit' => $limit,
            'total' => $this->getFilteredCount($filters)
        ];
    }

    /**
     * Get single learning material by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ? AND is_approved = true";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$id]);
        
        $material = $stmt->fetch();
        if ($material) {
            $this->incrementViews($id);
        }
        return $material;
    }

    /**
     * Create new learning material
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
        (title, course_code, course_name, faculty, year, material_type, file_path, file_size, file_type, uploaded_by, description, tags, difficulty_level)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($query);
        
        try {
            $stmt->execute([
                $data['title'],
                $data['course_code'],
                $data['course_name'],
                $data['faculty'],
                $data['year'],
                $data['material_type'],
                $data['file_path'],
                $data['file_size'],
                $data['file_type'],
                $data['uploaded_by'],
                $data['description'] ?? null,
                $data['tags'] ?? null,
                $data['difficulty_level'] ?? 'beginner'
            ]);

            return [
                'success' => true,
                'id' => $this->pdo->lastInsertId(),
                'message' => 'Learning material uploaded successfully. Awaiting approval.'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error creating learning material: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update learning material
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];

        $allowed_fields = ['title', 'description', 'tags', 'difficulty_level', 'material_type'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_fields)) {
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
            return ['success' => true, 'message' => 'Learning material updated successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating learning material'];
        }
    }

    /**
     * Delete learning material
     */
    public function delete($id) {
        $material = $this->getById($id);
        
        if (!$material) {
            return ['success' => false, 'message' => 'Learning material not found'];
        }

        // Delete file from storage
        if (file_exists($material['file_path'])) {
            unlink($material['file_path']);
        }

        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($query);

        try {
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Learning material deleted successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting learning material'];
        }
    }

    /**
     * Approve learning material (admin only)
     */
    public function approve($id, $admin_id) {
        $query = "UPDATE {$this->table} SET is_approved = true, approved_by = ?, approved_at = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($query);

        try {
            $stmt->execute([$admin_id, $id]);
            return ['success' => true, 'message' => 'Learning material approved'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error approving learning material'];
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
     * Get trending learning materials
     */
    public function getTrending($limit = 10) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE is_approved = true 
                  ORDER BY downloads_count DESC, rating DESC
                  LIMIT ?";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get materials by user
     */
    public function getByUser($user_id, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $query = "SELECT * FROM {$this->table} WHERE uploaded_by = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$user_id, $limit, $offset]);
        
        return [
            'materials' => $stmt->fetchAll(),
            'page' => $page,
            'limit' => $limit
        ];
    }

    /**
     * Get materials by type
     */
    public function getByType($type, $page = 1, $limit = 12) {
        $offset = ($page - 1) * $limit;
        $query = "SELECT * FROM {$this->table} WHERE material_type = ? AND is_approved = true ORDER BY created_at DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$type, $limit, $offset]);
        
        return [
            'materials' => $stmt->fetchAll(),
            'page' => $page,
            'limit' => $limit
        ];
    }

    /**
     * Get all available tags
     */
    public function getAllTags() {
        $query = "SELECT DISTINCT tags FROM {$this->table} WHERE tags IS NOT NULL";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        
        $results = $stmt->fetchAll();
        $tags = [];
        
        foreach ($results as $row) {
            $row_tags = array_map('trim', explode(',', $row['tags']));
            $tags = array_merge($tags, $row_tags);
        }
        
        return array_unique(array_filter($tags));
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

        if (!empty($filters['material_type'])) {
            $query .= " AND material_type = ?";
            $params[] = $filters['material_type'];
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
                    COUNT(*) as total_materials,
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