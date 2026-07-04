<?php
/**
 * Global Constants Configuration
 * 
 * Centralized constants used throughout the application
 * 
 * @category Configuration
 * @package LU Academic Hub
 * @author Paddy Pride
 */

// ============================================
// Application Constants
// ============================================

define('APP_NAME', 'LU Academic Hub');
define('APP_TITLE', 'LIRA University Academic Hub');
define('APP_VERSION', '1.0.0');
define('APP_YEAR', date('Y'));
define('ENVIRONMENT', 'production'); // production, staging, development

// ============================================
// Directory Constants
// ============================================

define('ROOT_DIR', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define('INCLUDES_DIR', ROOT_DIR . 'includes' . DIRECTORY_SEPARATOR);
define('CONFIG_DIR', ROOT_DIR . 'config' . DIRECTORY_SEPARATOR);
define('ASSETS_DIR', ROOT_DIR . 'assets' . DIRECTORY_SEPARATOR);
define('UPLOADS_DIR', ROOT_DIR . 'uploads' . DIRECTORY_SEPARATOR);
define('LOGS_DIR', ROOT_DIR . 'logs' . DIRECTORY_SEPARATOR);
define('CACHE_DIR', ROOT_DIR . 'cache' . DIRECTORY_SEPARATOR);
define('BACKUPS_DIR', ROOT_DIR . 'backups' . DIRECTORY_SEPARATOR);

// Upload subdirectories
define('UPLOADS_PAPERS_DIR', UPLOADS_DIR . 'papers' . DIRECTORY_SEPARATOR);
define('UPLOADS_RESEARCH_DIR', UPLOADS_DIR . 'research' . DIRECTORY_SEPARATOR);
define('UPLOADS_PROFILE_DIR', UPLOADS_DIR . 'profile' . DIRECTORY_SEPARATOR);

// ============================================
// URL Constants
// ============================================

define('BASE_URL', 'http://localhost/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMAGES_URL', ASSETS_URL . 'images/');
define('UPLOADS_URL', BASE_URL . 'uploads/');

// ============================================
// Security Constants
// ============================================

define('SESSION_TIMEOUT', 1800); // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCK_TIMEOUT', 900); // 15 minutes
define('CSRF_TOKEN_LENGTH', 32);
define('BCRYPT_COST', 10);
define('SESSION_COOKIE_SECURE', false); // Set to true in production with HTTPS
define('SESSION_COOKIE_HTTPONLY', true);

// ============================================
// File Upload Constants
// ============================================

define('MAX_FILE_SIZE', 52428800); // 50 MB in bytes
define('MAX_FILE_SIZE_MB', 50);
define('ALLOWED_EXTENSIONS', ['pdf', 'docx', 'pptx', 'xlsx', 'zip', 'jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOCUMENT_EXTENSIONS', ['pdf', 'docx', 'pptx', 'xlsx', 'doc', 'xls', 'txt']);

// ============================================
// Pagination Constants
// ============================================

define('PAGINATION_LIMIT', 20);
define('PAGINATION_RANGE', 5);

// ============================================
// User Roles
// ============================================

define('ROLE_STUDENT', 'student');
define('ROLE_LECTURER', 'lecturer');
define('ROLE_ADMIN', 'admin');
define('ROLE_SUPER_ADMIN', 'super_admin');

const USER_ROLES = [
    'student' => 'Student',
    'lecturer' => 'Lecturer',
    'admin' => 'Administrator',
    'super_admin' => 'Super Administrator'
];

// ============================================
// Document Types
// ============================================

const DOCUMENT_TYPES = [
    'past_paper' => 'Past Paper',
    'lecture_note' => 'Lecture Note',
    'assignment' => 'Assignment',
    'research_paper' => 'Research Paper',
    'project' => 'Project',
    'tutorial' => 'Tutorial',
    'guide' => 'Practical Guide',
    'book' => 'Book',
    'reference' => 'Reference Material',
    'video' => 'Video',
    'link' => 'Link'
];

// ============================================
// Semesters
// ============================================

const SEMESTERS = [
    '1' => 'Semester 1',
    '2' => 'Semester 2',
    'summer' => 'Summer School'
];

// ============================================
// Status Constants
// ============================================

const PAPER_STATUS = [
    'pending' => 'Pending Review',
    'approved' => 'Approved',
    'rejected' => 'Rejected',
    'archived' => 'Archived'
];

const DISCUSSION_STATUS = [
    'active' => 'Active',
    'locked' => 'Locked',
    'archived' => 'Archived'
];

const USER_STATUS = [
    'active' => 'Active',
    'inactive' => 'Inactive',
    'suspended' => 'Suspended',
    'deleted' => 'Deleted'
];

// ============================================
// Message Types
// ============================================

const MESSAGE_TYPES = [
    'info' => 'Information',
    'success' => 'Success',
    'warning' => 'Warning',
    'error' => 'Error'
];

// ============================================
// Activity Log Actions
// ============================================

const LOG_ACTIONS = [
    'login' => 'User Login',
    'logout' => 'User Logout',
    'upload' => 'File Upload',
    'download' => 'File Download',
    'create' => 'Record Created',
    'update' => 'Record Updated',
    'delete' => 'Record Deleted',
    'approve' => 'Content Approved',
    'reject' => 'Content Rejected',
    'comment' => 'Comment Added',
    'rating' => 'Rating Submitted'
];

// ============================================
// Email Templates
// ============================================

const EMAIL_TEMPLATES = [
    'welcome' => 'Welcome Email',
    'verification' => 'Email Verification',
    'password_reset' => 'Password Reset',
    'notification' => 'Notification',
    'announcement' => 'Announcement'
];

// ============================================
// Date/Time Formats
// ============================================

define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd M Y');
define('DISPLAY_DATETIME_FORMAT', 'd M Y, H:i');

// ============================================
// Default Values
// ============================================

define('DEFAULT_AVATAR', IMAGES_URL . 'default-avatar.png');
define('DEFAULT_FACULTY_ICON', IMAGES_URL . 'faculty-default.png');
define('DEFAULT_COURSES_PER_PAGE', 12);
define('DEFAULT_PAPERS_PER_PAGE', 20);
define('DEFAULT_FORUM_POSTS_PER_PAGE', 15);

?>
