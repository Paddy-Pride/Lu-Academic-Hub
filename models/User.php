<?php
/**
 * User Model
 * Handles user authentication and profile management
 */

class User {
    private $pdo;
    private $table = 'users';

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Register new user
     */
    public function register($data) {
        $query = "INSERT INTO {$this->table} 
        (email, password, first_name, last_name, faculty, year_of_study)
        VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($query);
        
        try {
            $stmt->execute([
                $data['email'],
                password_hash($data['password'], PASSWORD_BCRYPT),
                $data['first_name'] ?? null,
                $data['last_name'] ?? null,
                $data['faculty'] ?? null,
                $data['year_of_study'] ?? null
            ]);

            return [
                'success' => true,
                'id' => $this->pdo->lastInsertId(),
                'message' => 'Registration successful'
            ];
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            return ['success' => false, 'message' => 'Registration error'];
        }
    }

    /**
     * Login user
     */
    public function login($email, $password) {
        $query = "SELECT * FROM {$this->table} WHERE email = ? AND is_active = true";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$email]);
        
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $update_query = "UPDATE {$this->table} SET last_login = NOW() WHERE id = ?";
            $update_stmt = $this->pdo->prepare($update_query);
            $update_stmt->execute([$user['id']]);
            
            // Don't return password hash
            unset($user['password']);
            
            return [
                'success' => true,
                'user' => $user,
                'message' => 'Login successful'
            ];
        }
        
        return ['success' => false, 'message' => 'Invalid credentials'];
    }

    /**
     * Get user by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$id]);
        
        $user = $stmt->fetch();
        if ($user) {
            unset($user['password']);
        }
        return $user;
    }

    /**
     * Get user by email
     */
    public function getByEmail($email) {
        $query = "SELECT * FROM {$this->table} WHERE email = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$email]);
        
        $user = $stmt->fetch();
        if ($user) {
            unset($user['password']);
        }
        return $user;
    }

    /**
     * Update user profile
     */
    public function updateProfile($id, $data) {
        $fields = [];
        $params = [];

        $allowed_fields = ['first_name', 'last_name', 'faculty', 'year_of_study', 'phone', 'avatar_url'];
        
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
            return ['success' => true, 'message' => 'Profile updated successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating profile'];
        }
    }

    /**
     * Change password
     */
    public function changePassword($id, $current_password, $new_password) {
        $user = $this->getById($id);
        
        $query = "SELECT password FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if (!password_verify($current_password, $result['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        $update_query = "UPDATE {$this->table} SET password = ? WHERE id = ?";
        $update_stmt = $this->pdo->prepare($update_query);
        
        try {
            $update_stmt->execute([password_hash($new_password, PASSWORD_BCRYPT), $id]);
            return ['success' => true, 'message' => 'Password changed successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error changing password'];
        }
    }

    /**
     * Verify email
     */
    public function verifyEmail($id) {
        $query = "UPDATE {$this->table} SET is_verified = true, verification_token = NULL WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        
        try {
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Email verified successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error verifying email'];
        }
    }

    /**
     * Get user statistics
     */
    public function getUserStats($user_id) {
        $queries = [
            'uploaded_papers' => "SELECT COUNT(*) as count FROM past_papers WHERE uploaded_by = ?",
            'uploaded_materials' => "SELECT COUNT(*) as count FROM learning_materials WHERE uploaded_by = ?",
            'total_downloads' => "SELECT SUM(downloads_count) as count FROM past_papers WHERE uploaded_by = ? UNION SELECT SUM(downloads_count) FROM learning_materials WHERE uploaded_by = ?",
            'average_rating' => "SELECT AVG(rating) as rating FROM ratings_reviews WHERE resource_type = 'past_paper' AND resource_id IN (SELECT id FROM past_papers WHERE uploaded_by = ?) OR resource_type = 'learning_material' AND resource_id IN (SELECT id FROM learning_materials WHERE uploaded_by = ?)"
        ];

        $stats = [];
        
        foreach ($queries as $key => $query) {
            $stmt = $this->pdo->prepare($query);
            if ($key === 'total_downloads' || $key === 'average_rating') {
                $stmt->execute([$user_id, $user_id]);
            } else {
                $stmt->execute([$user_id]);
            }
            $stats[$key] = $stmt->fetch();
        }

        return $stats;
    }

    /**
     * Get all users (admin only)
     */
    public function getAll($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $query = "SELECT id, email, first_name, last_name, faculty, role, is_verified, created_at, last_login FROM {$this->table} ORDER BY created_at DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$limit, $offset]);
        
        return [
            'users' => $stmt->fetchAll(),
            'page' => $page,
            'limit' => $limit
        ];
    }

    /**
     * Deactivate user account
     */
    public function deactivate($id) {
        $query = "UPDATE {$this->table} SET is_active = false WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        
        try {
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Account deactivated'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deactivating account'];
        }
    }

    /**
     * Reactivate user account
     */
    public function reactivate($id) {
        $query = "UPDATE {$this->table} SET is_active = true WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        
        try {
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Account reactivated'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error reactivating account'];
        }
    }
}
?>