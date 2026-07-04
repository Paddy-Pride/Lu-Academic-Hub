<?php
/**
 * Rating & Review Handler
 * Manages ratings and reviews for resources
 */

class RatingReviewHandler {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Add or update rating and review
     */
    public function submitRating($user_id, $resource_id, $resource_type, $rating, $review_text = null) {
        // Validate rating
        if ($rating < 1 || $rating > 5) {
            return ['success' => false, 'message' => 'Rating must be between 1 and 5'];
        }

        // Check if user already rated
        $check_query = "SELECT id FROM ratings_reviews WHERE user_id = ? AND resource_id = ? AND resource_type = ?";
        $check_stmt = $this->pdo->prepare($check_query);
        $check_stmt->execute([$user_id, $resource_id, $resource_type]);
        $existing = $check_stmt->fetch();

        if ($existing) {
            // Update existing rating
            $query = "UPDATE ratings_reviews SET rating = ?, review_text = ?, updated_at = NOW() WHERE user_id = ? AND resource_id = ? AND resource_type = ?";
            $stmt = $this->pdo->prepare($query);
            
            try {
                $stmt->execute([$rating, $review_text, $user_id, $resource_id, $resource_type]);
                return ['success' => true, 'message' => 'Rating updated successfully'];
            } catch (PDOException $e) {
                return ['success' => false, 'message' => 'Error updating rating'];
            }
        } else {
            // Insert new rating
            $query = "INSERT INTO ratings_reviews (user_id, resource_id, resource_type, rating, review_text) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($query);
            
            try {
                $stmt->execute([$user_id, $resource_id, $resource_type, $rating, $review_text]);
                $this->updateResourceRating($resource_id, $resource_type);
                return ['success' => true, 'message' => 'Rating submitted successfully'];
            } catch (PDOException $e) {
                return ['success' => false, 'message' => 'Error submitting rating'];
            }
        }
    }

    /**
     * Get ratings and reviews for a resource
     */
    public function getResourceReviews($resource_id, $resource_type, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT rr.*, u.first_name, u.last_name, u.avatar_url 
                 FROM ratings_reviews rr
                 JOIN users u ON rr.user_id = u.id
                 WHERE rr.resource_id = ? AND rr.resource_type = ? AND rr.review_text IS NOT NULL
                 ORDER BY rr.created_at DESC
                 LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$resource_id, $resource_type, $limit, $offset]);
        
        return $stmt->fetchAll();
    }

    /**
     * Get rating summary
     */
    public function getRatingSummary($resource_id, $resource_type) {
        $query = "SELECT 
                    COUNT(*) as total_ratings,
                    AVG(rating) as average_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                 FROM ratings_reviews 
                 WHERE resource_id = ? AND resource_type = ?";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$resource_id, $resource_type]);
        
        return $stmt->fetch();
    }

    /**
     * Mark review as helpful
     */
    public function markHelpful($review_id) {
        $query = "UPDATE ratings_reviews SET helpful_count = helpful_count + 1, is_helpful = true WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        
        try {
            $stmt->execute([$review_id]);
            return ['success' => true, 'message' => 'Marked as helpful'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error marking as helpful'];
        }
    }

    /**
     * Delete rating
     */
    public function deleteRating($review_id, $user_id) {
        $query = "DELETE FROM ratings_reviews WHERE id = ? AND user_id = ?";
        $stmt = $this->pdo->prepare($query);
        
        try {
            $stmt->execute([$review_id, $user_id]);
            return ['success' => true, 'message' => 'Rating deleted'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting rating'];
        }
    }

    /**
     * Update resource average rating
     */
    private function updateResourceRating($resource_id, $resource_type) {
        $query = "SELECT AVG(rating) as avg_rating FROM ratings_reviews WHERE resource_id = ? AND resource_type = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$resource_id, $resource_type]);
        $result = $stmt->fetch();
        
        $avg_rating = $result['avg_rating'] ?? 0;
        
        if ($resource_type === 'past_paper') {
            $update_query = "UPDATE past_papers SET rating = ? WHERE id = ?";
        } else {
            $update_query = "UPDATE learning_materials SET rating = ? WHERE id = ?";
        }
        
        $update_stmt = $this->pdo->prepare($update_query);
        $update_stmt->execute([$avg_rating, $resource_id]);
    }
}
?>