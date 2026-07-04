<?php
/**
 * User Registration Page
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
$errors = [];

// Handle registration submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['csrf'] = 'Invalid request. Please try again.';
    } else {
        $authController = new AuthController();
        $result = $authController->register([
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
            'role' => $_POST['role'] ?? 'student'
        ]);

        if ($result['success']) {
            $message = $result['message'];
            // Redirect to login after 2 seconds
            header('Refresh: 2; url=' . BASE_URL . 'auth/login.php');
        } else {
            if (isset($result['errors'])) {
                $errors = $result['errors'];
            } else {
                $errors['general'] = $result['message'];
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LU Academic Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo CSS_URL; ?>auth.css" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header text-center mb-4">
                <h1><i class="fas fa-graduation-cap"></i> LU Academic Hub</h1>
                <p class="text-muted">Create Account</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo escape($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors['general'] ?? '')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo escape($errors['general']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo escape($csrfToken); ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">First Name *</label>
                        <input type="text" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" 
                               id="first_name" name="first_name" value="<?php echo escape($_POST['first_name'] ?? ''); ?>" required>
                        <?php if (isset($errors['first_name'])): ?>
                            <div class="invalid-feedback"><?php echo escape($errors['first_name']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Last Name *</label>
                        <input type="text" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" 
                               id="last_name" name="last_name" value="<?php echo escape($_POST['last_name'] ?? ''); ?>" required>
                        <?php if (isset($errors['last_name'])): ?>
                            <div class="invalid-feedback"><?php echo escape($errors['last_name']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                           id="email" name="email" value="<?php echo escape($_POST['email'] ?? ''); ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?php echo escape($errors['email']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">User Type *</label>
                    <select class="form-select <?php echo isset($errors['role']) ? 'is-invalid' : ''; ?>" 
                            id="role" name="role" required>
                        <option value="">-- Select Role --</option>
                        <option value="student" <?php echo ($_POST['role'] ?? '') === 'student' ? 'selected' : ''; ?>>Student</option>
                        <option value="lecturer" <?php echo ($_POST['role'] ?? '') === 'lecturer' ? 'selected' : ''; ?>>Lecturer</option>
                    </select>
                    <?php if (isset($errors['role'])): ?>
                        <div class="invalid-feedback"><?php echo escape($errors['role']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password *</label>
                    <div class="input-group">
                        <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                               id="password" name="password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small class="form-text text-muted">At least 8 characters</small>
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback d-block"><?php echo escape($errors['password']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="password_confirm" class="form-label">Confirm Password *</label>
                    <input type="password" class="form-control <?php echo isset($errors['password_confirm']) ? 'is-invalid' : ''; ?>" 
                           id="password_confirm" name="password_confirm" required>
                    <?php if (isset($errors['password_confirm'])): ?>
                        <div class="invalid-feedback"><?php echo escape($errors['password_confirm']); ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-footer text-center">
                <p class="mb-0">Already have an account? <a href="<?php echo BASE_URL; ?>auth/login.php">Sign In</a></p>
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
