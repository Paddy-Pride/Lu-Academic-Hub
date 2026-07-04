<?php
/**
 * Logout Handler
 * 
 * @category Authentication
 * @package LU Academic Hub
 */

require_once '../includes/config/constants.php';
require_once '../includes/functions.php';
require_once '../includes/Database.php';
require_once './AuthController.php';
require_once './AuthMiddleware.php';

// Require authentication
AuthMiddleware::requireAuth();

$authController = new AuthController();
$result = $authController->logout();

// Redirect to login page
redirect(BASE_URL . 'auth/login.php?message=logged_out');

?>
