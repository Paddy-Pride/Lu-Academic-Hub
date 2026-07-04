<?php
/**
 * User Repository
 * 
 * Data access layer for user operations
 * 
 * @category Authentication
 * @package LU Academic Hub
 * @author Paddy Pride
 */

class UserRepository {
    private $db;

    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get user by ID
     * 
     * @param int $userId User ID
     * @return array User data
     */
    public function getUserById($userId) {
        try {
            $stmt = $this->db->prepare(
                'SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.avatar_url, u.bio, 
                        u.status, u.email_verified, r.name as role, r.display_name as role_display 
                 FROM users u 
                 JOIN roles r ON u.role_id = r.id 
                 WHERE u.id = ?'
            );
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Get user error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user by email
     * 
     * @param string $email User email
     * @return array User data
     */
    public function getUserByEmail($email) {
        try {
            $stmt = $this->db->prepare(
                'SELECT u.id, u.first_name, u.last_name, u.email, u.status, r.name as role 
                 FROM users u 
                 JOIN roles r ON u.role_id = r.id 
                 WHERE u.email = ?'
            );
            $stmt->execute([$email]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Get user by email error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update user profile
     * 
     * @param int $userId User ID
     * @param array $data User data
     * @return bool Success status
     */
    public function updateProfile($userId, $data) {
        try {
            $stmt = $this->db->prepare(
                'UPDATE users SET first_name = ?, last_name = ?, phone = ?, bio = ?, 
                 avatar_url = ?, updated_at = NOW() WHERE id = ?'
            );
            return $stmt->execute([
                sanitize($data['first_name'] ?? ''),
                sanitize($data['last_name'] ?? ''),
                sanitize($data['phone'] ?? ''),
                sanitize($data['bio'] ?? ''),
                $data['avatar_url'] ?? null,
                $userId
            ]);
        } catch (PDOException $e) {
            error_log('Update profile error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update password
     * 
     * @param int $userId User ID
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return array Success/error response
     */
    public function updatePassword($userId, $currentPassword, $newPassword) {
        try {
            // Get current password hash
            $stmt = $this->db->prepare('SELECT password_hash FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            // Verify current password
            if (!verifyPassword($currentPassword, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            // Hash new password
            $newPasswordHash = hashPassword($newPassword);

            // Update password
            $stmt = $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->execute([$newPasswordHash, $userId]);

            logActivity('PASSWORD_CHANGED', 'User changed password', $userId);

            return ['success' => true, 'message' => 'Password updated successfully'];
        } catch (PDOException $e) {
            error_log('Update password error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Password update failed'];
        }
    }

    /**
     * Get user permissions
     * 
     * @param int $userId User ID
     * @return array Permissions list
     */
    public function getUserPermissions($userId) {
        try {
            $stmt = $this->db->prepare(
                'SELECT DISTINCT p.name FROM permissions p 
                 JOIN role_permissions rp ON p.id = rp.permission_id 
                 JOIN users u ON rp.role_id = u.role_id 
                 WHERE u.id = ?'
            );
            $stmt->execute([$userId]);
            $permissions = $stmt->fetchAll();
            return array_column($permissions, 'name');
        } catch (PDOException $e) {
            error_log('Get permissions error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if user has permission
     * 
     * @param int $userId User ID
     * @param string $permission Permission name
     * @return bool Has permission
     */
    public function hasPermission($userId, $permission) {
        try {
            $stmt = $this->db->prepare(
                'SELECT 1 FROM permissions p 
                 JOIN role_permissions rp ON p.id = rp.permission_id 
                 JOIN users u ON rp.role_id = u.role_id 
                 WHERE u.id = ? AND p.name = ? LIMIT 1'
            );
            $stmt->execute([$userId, $permission]);
            return $stmt->fetch() ? true : false;
        } catch (PDOException $e) {
            error_log('Check permission error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all users with pagination
     * 
     * @param int $page Page number
     * @param int $limit Items per page
     * @param string $status Filter by status
     * @return array Users list
     */
    public function getAllUsers($page = 1, $limit = 20, $status = null) {
        try {
            $offset = ($page - 1) * $limit;
            $query = 'SELECT u.id, u.first_name, u.last_name, u.email, u.status, r.display_name as role, u.created_at 
                     FROM users u 
                     JOIN roles r ON u.role_id = r.id 
                     WHERE 1=1';
            $params = [];

            if ($status) {
                $query .= ' AND u.status = ?';
                $params[] = $status;
            }

            $query .= ' ORDER BY u.created_at DESC LIMIT ? OFFSET ?';
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Get all users error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user count
     * 
     * @param string $status Filter by status
     * @return int User count
     */
    public function getUserCount($status = null) {
        try {
            $query = 'SELECT COUNT(*) as count FROM users WHERE 1=1';
            $params = [];

            if ($status) {
                $query .= ' AND status = ?';
                $params[] = $status;
            }

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log('Get user count error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update user status
     * 
     * @param int $userId User ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updateUserStatus($userId, $status) {
        try {
            $stmt = $this->db->prepare('UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?');
            return $stmt->execute([$status, $userId]);
        } catch (PDOException $e) {
            error_log('Update user status error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user preferences
     * 
     * @param int $userId User ID
     * @return array User preferences
     */
    public function getUserPreferences($userId) {
        try {
            $stmt = $this->db->prepare('SELECT * FROM user_preferences WHERE user_id = ?');
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Get user preferences error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update user preferences
     * 
     * @param int $userId User ID
     * @param array $preferences Preference data
     * @return bool Success status
     */
    public function updateUserPreferences($userId, $preferences) {
        try {
            $stmt = $this->db->prepare(
                'UPDATE user_preferences SET theme = ?, language = ?, email_notifications = ?, 
                 in_app_notifications = ?, private_profile = ?, newsletter_subscription = ? 
                 WHERE user_id = ?'
            );
            return $stmt->execute([
                $preferences['theme'] ?? 'auto',
                $preferences['language'] ?? 'en',
                $preferences['email_notifications'] ?? true,
                $preferences['in_app_notifications'] ?? true,
                $preferences['private_profile'] ?? false,
                $preferences['newsletter_subscription'] ?? true,
                $userId
            ]);
        } catch (PDOException $e) {
            error_log('Update user preferences error: ' . $e->getMessage());
            return false;
        }
    }
}

?>
