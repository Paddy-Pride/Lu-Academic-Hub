<?php
/**
 * Authentication Middleware
 * 
 * Handles authentication checks and authorization
 * 
 * @category Authentication
 * @package LU Academic Hub
 * @author Paddy Pride
 */

class AuthMiddleware {
    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    public static function isAuthenticated() {
        startSecureSession();
        return isLoggedIn();
    }

    /**
     * Require authentication
     * Redirects to login if not authenticated
     */
    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            redirect(BASE_URL . 'auth/login.php');
        }
    }

    /**
     * Require specific role
     * 
     * @param string|array $roles Required role(s)
     */
    public static function requireRole($roles) {
        self::requireAuth();
        
        if (!hasRole($roles)) {
            http_response_code(403);
            die('Access denied. Insufficient permissions.');
        }
    }

    /**
     * Require specific permission
     * 
     * @param string $permission Required permission
     */
    public static function requirePermission($permission) {
        self::requireAuth();
        
        $userRepo = new UserRepository();
        if (!$userRepo->hasPermission(getCurrentUserId(), $permission)) {
            http_response_code(403);
            die('Access denied. You do not have permission.');
        }
    }

    /**
     * Require guest (not authenticated)
     * Redirects to dashboard if already authenticated
     */
    public static function requireGuest() {
        if (self::isAuthenticated()) {
            $role = getCurrentUserRole();
            $dashboardUrl = $role === 'admin' || $role === 'super_admin' ? 
                           BASE_URL . 'admin/dashboard.php' : 
                           BASE_URL . 'student/dashboard.php';
            redirect($dashboardUrl);
        }
    }

    /**
     * Validate CSRF token
     * 
     * @param string $token Token to validate
     * @return bool
     */
    public static function validateCSRFToken($token) {
        return verifyCSRFToken($token);
    }
}

?>
