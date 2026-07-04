<?php
/**
 * Authentication Handler
 * Manages user authentication and session management
 */

class AuthHandler {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Check user role
     */
    public function hasRole($required_role) {
        if (!$this->isAuthenticated()) {
            return false;
        }
        return $_SESSION['user_role'] === $required_role;
    }

    /**
     * Check if user has admin privileges
     */
    public function isAdmin() {
        return $this->hasRole('admin');
    }

    /**
     * Get current user ID
     */
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user data
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }

        $query = "SELECT id, email, first_name, last_name, role, faculty, year_of_study FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$this->getUserId()]);
        
        return $stmt->fetch();
    }

    /**
     * Login user
     */
    public function login($email, $password) {
        $query = "SELECT id, email, first_name, password, role FROM users WHERE email = ? AND is_active = true";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$email]);
        
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['login_time'] = time();

            // Update last login
            $update_query = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $update_stmt = $this->pdo->prepare($update_query);
            $update_stmt->execute([$user['id']]);

            return ['success' => true, 'message' => 'Login successful'];
        }
        
        return ['success' => false, 'message' => 'Invalid credentials'];
    }

    /**
     * Logout user
     */
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    /**
     * Check session timeout
     */
    public function checkSessionTimeout($timeout = 3600) {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $current_time = time();
        $session_time = $_SESSION['login_time'] ?? $current_time;

        if ($current_time - $session_time > $timeout) {
            $this->logout();
            return false;
        }

        // Update session time
        $_SESSION['login_time'] = $current_time;
        return true;
    }
}
?>