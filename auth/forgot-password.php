<?php
/**
 * Forgot Password Page
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
$step = 1; // 1: Enter email, 2: Reset password

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else if (isset($_POST['request_reset'])) {
        $authController = new AuthController();
        $result = $authController->requestPasswordReset($_POST['email'] ?? '');
        
        if ($result['success']) {
            $message = 'If the email exists, a reset link will be sent shortly.';
            $step = 1;
        } else {
            $error = $result['message'];
        }
    } else if (isset($_POST['reset_password'])) {
        if ($_POST['password'] !== $_POST['password_confirm']) {
            $error = 'Passwords do not match';
        } else {
            $authController = new AuthController();
            $result = $authController->resetPassword(
                $_POST['token'] ?? '',
                $_POST['password'] ?? ''
            );
            
            if ($result['success']) {
                $message = $result['message'];
                header('Refresh: 2; url=' . BASE_URL . 'auth/login.php');
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Check if reset token is in URL
if (isset($_GET['token'])) {
    $step = 2;
}

startSecureSession();
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - LU Academic Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo CSS_URL; ?>auth.css" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header text-center mb-4">
                <h1><i class="fas fa-graduation-cap"></i> LU Academic Hub</h1>
                <p class="text-muted"><?php echo $step === 1 ? 'Reset Password' : 'Create New Password'; ?></p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo escape($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo escape($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
                <!-- Step 1: Enter Email -->
                <form method="POST" action="" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?php echo escape($csrfToken); ?>">
                    <input type="hidden" name="request_reset" value="1">

                    <p class="text-muted mb-4">Enter your email address and we'll send you a link to reset your password.</p>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required autofocus>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-envelope"></i> Send Reset Link
                    </button>
                </form>
            <?php else: ?>
                <!-- Step 2: Reset Password -->
                <form method="POST" action="" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?php echo escape($csrfToken); ?>">
                    <input type="hidden" name="token" value="<?php echo escape($_GET['token'] ?? ''); ?>">
                    <input type="hidden" name="reset_password" value="1">

                    <p class="text-muted mb-4">Create a new password for your account.</p>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">At least 8 characters</small>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-lock"></i> Reset Password
                    </button>
                </form>
            <?php endif; ?>

            <div class="auth-footer text-center border-top pt-3">
                <p class="mb-0"><a href="<?php echo BASE_URL; ?>auth/login.php" class="text-decoration-none">Back to Login</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggleBtn = document.getElementById('togglePassword');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const pwd = document.getElementById('password');
                const type = pwd.getAttribute('type') === 'password' ? 'text' : 'password';
                pwd.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
        }
    </script>
</body>
</html>
