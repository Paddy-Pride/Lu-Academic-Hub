<?php
/**
 * User Login Page
 * 
 * @category Authentication
 * @package LU Academic Hub
 */

require_once '../includes/config/constants.php';
require_once '../includes/functions.php';
require_once '../includes/Database.php';
require_once './AuthController.php';
require_once './AuthMiddleware.php';

// Require guest access
AuthMiddleware::requireGuest();

$message = '';
$error = '';
$email = '';

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $authController = new AuthController();
        $result = $authController->login($email, $password);

        if ($result['success']) {
            // Determine redirect URL based on role
            if ($result['user_role'] === 'admin' || $result['user_role'] === 'super_admin') {
                redirect(BASE_URL . 'admin/dashboard.php');
            } else {
                redirect(BASE_URL . 'student/dashboard.php');
            }
        } else {
            $error = $result['message'];
        }
    }
}

startSecureSession();
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LU Academic Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo CSS_URL; ?>auth.css" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header text-center mb-4">
                <h1><i class="fas fa-graduation-cap"></i> LU Academic Hub</h1>
                <p class="text-muted">Sign In to Your Account</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo escape($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo escape($csrfToken); ?>">

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo escape($email); ?>" placeholder="Enter your email" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter your password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="auth-links text-center mb-3">
                <a href="<?php echo BASE_URL; ?>auth/forgot-password.php" class="text-decoration-none">
                    <i class="fas fa-key"></i> Forgot Password?
                </a>
            </div>

            <div class="auth-footer text-center border-top pt-3">
                <p class="mb-0">Don't have an account? <a href="<?php echo BASE_URL; ?>auth/register.php">Create One</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const pwd = document.getElementById('password');
            const type = pwd.getAttribute('type') === 'password' ? 'text' : 'password';
            pwd.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    </script>
</body>
</html>
