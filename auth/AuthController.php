<?php
/**
 * Authentication Controller
 * 
 * Handles user registration, login, password reset, and session management
 * 
 * @category Authentication
 * @package LU Academic Hub
 * @author Paddy Pride
 */

class AuthController {
    private $db;
    private $maxLoginAttempts = MAX_LOGIN_ATTEMPTS;
    private $lockTimeout = LOCK_TIMEOUT;

    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Register new user
     * 
     * @param array $data User registration data
     * @return array Success/error response
     */
    public function register($data) {
        // Validate input
        $errors = validateFormData($data, [
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'password' => 'required|min:8|max:255',
            'password_confirm' => 'required',
            'role' => 'required'
        ]);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Check if passwords match
        if ($data['password'] !== $data['password_confirm']) {
            return ['success' => false, 'message' => 'Passwords do not match'];
        }

        // Check if email already exists
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        // Get role ID
        $stmt = $this->db->prepare('SELECT id FROM roles WHERE name = ?');
        $stmt->execute([$data['role']]);
        $role = $stmt->fetch();
        if (!$role) {
            return ['success' => false, 'message' => 'Invalid role selected'];
        }

        try {
            // Hash password
            $passwordHash = hashPassword($data['password']);
            $verificationToken = generateRandomString(32);

            // Insert user
            $stmt = $this->db->prepare(
                'INSERT INTO users (first_name, last_name, email, password_hash, role_id, status, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, NOW())'
            );
            $stmt->execute([
                sanitize($data['first_name']),
                sanitize($data['last_name']),
                $data['email'],
                $passwordHash,
                $role['id'],
                'active'
            ]);

            $userId = $this->db->lastInsertId();

            // Create user preferences
            $stmt = $this->db->prepare(
                'INSERT INTO user_preferences (user_id, theme, language) VALUES (?, ?, ?)'
            );
            $stmt->execute([$userId, 'auto', 'en']);

            // Log activity
            logActivity('USER_REGISTERED', 'New user registered: ' . $data['email'], $userId);

            return [
                'success' => true,
                'message' => 'Registration successful. Please verify your email.',
                'user_id' => $userId
            ];
        } catch (PDOException $e) {
            error_log('Registration error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }

    /**
     * Login user
     * 
     * @param string $email User email
     * @param string $password User password
     * @return array Success/error response
     */
    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password required'];
        }

        if (!isValidEmail($email)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        try {
            // Get user
            $stmt = $this->db->prepare(
                'SELECT u.id, u.first_name, u.last_name, u.password_hash, u.status, 
                        u.login_attempts, u.locked_until, r.name as role 
                 FROM users u 
                 JOIN roles r ON u.role_id = r.id 
                 WHERE u.email = ?'
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Check if user exists
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            // Check if account is active
            if ($user['status'] !== 'active') {
                return ['success' => false, 'message' => 'Account is not active'];
            }

            // Check if account is locked
            if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                $remainingTime = (strtotime($user['locked_until']) - time()) / 60;
                return [
                    'success' => false,
                    'message' => sprintf('Account locked. Try again in %.0f minutes.', $remainingTime)
                ];
            }

            // Verify password
            if (!verifyPassword($password, $user['password_hash'])) {
                $this->incrementLoginAttempts($user['id']);
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            // Reset login attempts
            $stmt = $this->db->prepare(
                'UPDATE users SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?'
            );
            $stmt->execute([$user['id']]);

            // Create session
            $sessionToken = generateRandomString(32);
            $expiresAt = date('Y-m-d H:i:s', time() + SESSION_TIMEOUT);

            $stmt = $this->db->prepare(
                'INSERT INTO sessions (user_id, session_token, ip_address, user_agent, expires_at, created_at) 
                 VALUES (?, ?, ?, ?, ?, NOW())'
            );
            $stmt->execute([
                $user['id'],
                $sessionToken,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                $expiresAt
            ]);

            // Set session variables
            startSecureSession();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['session_token'] = $sessionToken;

            logActivity('USER_LOGIN', 'User logged in', $user['id']);

            return [
                'success' => true,
                'message' => 'Login successful',
                'user_id' => $user['id'],
                'user_role' => $user['role']
            ];
        } catch (PDOException $e) {
            error_log('Login error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }

    /**
     * Increment failed login attempts
     * 
     * @param int $userId User ID
     */
    private function incrementLoginAttempts($userId) {
        try {
            $stmt = $this->db->prepare('SELECT login_attempts FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            $attempts = $user['login_attempts'] + 1;

            if ($attempts >= $this->maxLoginAttempts) {
                $lockedUntil = date('Y-m-d H:i:s', time() + $this->lockTimeout);
                $stmt = $this->db->prepare(
                    'UPDATE users SET login_attempts = ?, locked_until = ? WHERE id = ?'
                );
                $stmt->execute([$attempts, $lockedUntil, $userId]);
            } else {
                $stmt = $this->db->prepare('UPDATE users SET login_attempts = ? WHERE id = ?');
                $stmt->execute([$attempts, $userId]);
            }
        } catch (PDOException $e) {
            error_log('Increment login attempts error: ' . $e->getMessage());
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        try {
            if (isLoggedIn()) {
                $userId = getCurrentUserId();
                
                // Invalidate session
                $stmt = $this->db->prepare('DELETE FROM sessions WHERE user_id = ?');
                $stmt->execute([$userId]);

                logActivity('USER_LOGOUT', 'User logged out', $userId);
            }

            // Destroy session
            startSecureSession();
            session_destroy();

            return ['success' => true, 'message' => 'Logged out successfully'];
        } catch (PDOException $e) {
            error_log('Logout error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Logout failed'];
        }
    }

    /**
     * Request password reset
     * 
     * @param string $email User email
     * @return array Success/error response
     */
    public function requestPasswordReset($email) {
        if (empty($email) || !isValidEmail($email)) {
            return ['success' => false, 'message' => 'Valid email required'];
        }

        try {
            // Get user
            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                // Don't reveal if email exists
                return ['success' => true, 'message' => 'If email exists, reset link will be sent'];
            }

            // Generate reset token
            $token = generateRandomString(32);
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            // Store reset token
            $stmt = $this->db->prepare(
                'INSERT INTO password_reset_tokens (user_id, token, expires_at, created_at) 
                 VALUES (?, ?, ?, NOW())'
            );
            $stmt->execute([$user['id'], $token, $expiresAt]);

            // TODO: Send email with reset link
            logActivity('PASSWORD_RESET_REQUESTED', 'Password reset requested', $user['id']);

            return [
                'success' => true,
                'message' => 'If email exists, reset link will be sent',
                'token' => $token // For testing - remove in production
            ];
        } catch (PDOException $e) {
            error_log('Password reset request error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Reset request failed'];
        }
    }

    /**
     * Reset password with token
     * 
     * @param string $token Reset token
     * @param string $newPassword New password
     * @return array Success/error response
     */
    public function resetPassword($token, $newPassword) {
        if (empty($token) || empty($newPassword)) {
            return ['success' => false, 'message' => 'Token and password required'];
        }

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }

        try {
            // Get reset token
            $stmt = $this->db->prepare(
                'SELECT user_id FROM password_reset_tokens 
                 WHERE token = ? AND expires_at > NOW() AND used_at IS NULL'
            );
            $stmt->execute([$token]);
            $resetToken = $stmt->fetch();

            if (!$resetToken) {
                return ['success' => false, 'message' => 'Invalid or expired reset token'];
            }

            $userId = $resetToken['user_id'];

            // Update password
            $newPasswordHash = hashPassword($newPassword);
            $stmt = $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->execute([$newPasswordHash, $userId]);

            // Mark token as used
            $stmt = $this->db->prepare(
                'UPDATE password_reset_tokens SET used_at = NOW() WHERE token = ?'
            );
            $stmt->execute([$token]);

            logActivity('PASSWORD_RESET', 'Password reset successful', $userId);

            return ['success' => true, 'message' => 'Password reset successful'];
        } catch (PDOException $e) {
            error_log('Password reset error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Password reset failed'];
        }
    }

    /**
     * Verify user session
     * 
     * @return bool Session is valid
     */
    public function verifySession() {
        if (!isLoggedIn()) {
            return false;
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT id FROM sessions 
                 WHERE user_id = ? AND session_token = ? AND expires_at > NOW()'
            );
            $stmt->execute([getCurrentUserId(), $_SESSION['session_token'] ?? '']);
            return $stmt->fetch() ? true : false;
        } catch (PDOException $e) {
            error_log('Session verification error: ' . $e->getMessage());
            return false;
        }
    }
}

?>
