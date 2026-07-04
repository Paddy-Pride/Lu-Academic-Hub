<?php
/**
 * Bookmark Handler
 * Manages user bookmarks
 */

class BookmarkHandler {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Add bookmark
     */
    public function addBookmark($user_id, $resource_id, $resource_type, $folder_name = 'default') {
        // Check if already bookmarked
        $check_query = "SELECT id FROM bookmarks WHERE user_id = ? AND resource_id = ? AND resource_type = ?";
        $check_stmt = $this->pdo->prepare($check_query);
        $check_stmt->execute([$user_id, $resource_id, $resource_type]);
        
        if ($check_stmt->fetch()) {
            return ['success' => false, 'message' => 'Already bookmarked'];
        }

        $query = "INSERT INTO bookmarks (user_id, resource_id, resource_type, folder_name) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($query);
        
        try {
            $stmt->execute([$user_id, $resource_id, $resource_type, $folder_name]);
            return ['success' => true, 'message' => 'Bookmarked successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error adding bookmark'];
        }
    }

    /**
     * Remove bookmark
     */
    public function removeBookmark($user_id, $resource_id, $resource_type) {
        $query = "DELETE FROM bookmarks WHERE user_id = ? AND resource_id = ? AND resource_type = ?";
        $stmt = $this->pdo->prepare($query);
        
        try {
            $stmt->execute([$user_id, $resource_id, $resource_type]);
            return ['success' => true, 'message' => 'Bookmark removed'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error removing bookmark'];
        }
    }

    /**
     * Get user bookmarks
     */
    public function getUserBookmarks($user_id, $resource_type = null, $folder_name = null) {
        $query = "SELECT b.*, ";
        $params = [$user_id];

        if ($resource_type === 'past_paper') {
            $query .= "pp.title, pp.course_code, pp.course_name, pp.downloads_count 
                      FROM bookmarks b
                      JOIN past_papers pp ON b.resource_id = pp.id
                      WHERE b.user_id = ? AND b.resource_type = 'past_paper'";
            $params[] = 'past_paper';
        } elseif ($resource_type === 'learning_material') {
            $query .= "lm.title, lm.course_code, lm.course_name, lm.downloads_count 
                      FROM bookmarks b
                      JOIN learning_materials lm ON b.resource_id = lm.id
                      WHERE b.user_id = ? AND b.resource_type = 'learning_material'";
            $params[] = 'learning_material';
        } else {
            $query .= "'multiple' as type FROM bookmarks b WHERE b.user_id = ?";
        }

        if ($folder_name) {
            $query .= " AND b.folder_name = ?";
            $params[] = $folder_name;
        }

        $query .= " ORDER BY b.bookmarked_at DESC";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Get user bookmark folders
     */
    public function getBookmarkFolders($user_id) {
        $query = "SELECT DISTINCT folder_name, COUNT(*) as count FROM bookmarks WHERE user_id = ? GROUP BY folder_name";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$user_id]);
        
        return $stmt->fetchAll();
    }

    /**
     * Create bookmark folder
     */
    public function createFolder($user_id, $folder_name) {
        // Check if folder exists
        $check_query = "SELECT COUNT(*) as count FROM bookmarks WHERE user_id = ? AND folder_name = ?";
        $check_stmt = $this->pdo->prepare($check_query);
        $check_stmt->execute([$user_id, $folder_name]);
        $result = $check_stmt->fetch();
        
        if ($result['count'] > 0) {
            return ['success' => false, 'message' => 'Folder already exists'];
        }

        return ['success' => true, 'message' => 'Folder created successfully'];
    }

    /**
     * Check if resource is bookmarked
     */
    public function isBookmarked($user_id, $resource_id, $resource_type) {
        $query = "SELECT id FROM bookmarks WHERE user_id = ? AND resource_id = ? AND resource_type = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$user_id, $resource_id, $resource_type]);
        
        return $stmt->fetch() ? true : false;
    }
}
?>