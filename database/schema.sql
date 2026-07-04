<?php
/**
 * LU Academic Hub - Database Schema SQL
 * 
 * Complete normalized MySQL database schema for the LU Academic Hub application
 * Includes all tables with proper relationships, indexes, and constraints
 * 
 * @category Database
 * @package LU Academic Hub
 * @author Paddy Pride
 * @version 1.0.0
 */

// ============================================
// USERS AND AUTHENTICATION
// ============================================

/**
 * CREATE TABLE: users
 * Purpose: Store user account information
 * Indexes: email (UNIQUE), status, created_at
 */
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Unique user identifier',
    first_name VARCHAR(100) NOT NULL COMMENT 'User first name',
    last_name VARCHAR(100) NOT NULL COMMENT 'User last name',
    email VARCHAR(255) UNIQUE NOT NULL COMMENT 'User email address (unique)',
    phone VARCHAR(20) COMMENT 'User phone number',
    password_hash VARCHAR(255) NOT NULL COMMENT 'Bcrypt hashed password',
    role_id INT NOT NULL COMMENT 'Foreign key to roles table',
    avatar_url VARCHAR(500) COMMENT 'Profile picture URL',
    bio TEXT COMMENT 'User biography',
    status ENUM('active', 'inactive', 'suspended', 'deleted') DEFAULT 'active' COMMENT 'Account status',
    email_verified BOOLEAN DEFAULT FALSE COMMENT 'Email verification status',
    email_verified_at TIMESTAMP NULL COMMENT 'Email verification timestamp',
    two_factor_enabled BOOLEAN DEFAULT FALSE COMMENT 'Two-factor authentication status',
    last_login TIMESTAMP NULL COMMENT 'Last login timestamp',
    login_attempts INT DEFAULT 0 COMMENT 'Failed login attempts counter',
    locked_until TIMESTAMP NULL COMMENT 'Account lock expiration time',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Account creation timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
    
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_role_id (role_id),
    INDEX idx_created_at (created_at),
    FULLTEXT INDEX ft_user_search (first_name, last_name, email),
    
    CONSTRAINT fk_users_roles FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User accounts and authentication';

/**
 * CREATE TABLE: roles
 * Purpose: Define user roles and permissions
 * Roles: student, lecturer, admin, super_admin
 */
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Role identifier',
    name VARCHAR(50) UNIQUE NOT NULL COMMENT 'Role name (student, lecturer, admin, super_admin)',
    display_name VARCHAR(100) NOT NULL COMMENT 'Display name for role',
    description TEXT COMMENT 'Role description and permissions',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Role creation timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
    
    UNIQUE INDEX idx_role_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User roles';

/**
 * CREATE TABLE: permissions
 * Purpose: Define granular permissions
 */
CREATE TABLE IF NOT EXISTS permissions (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Permission identifier',
    name VARCHAR(100) UNIQUE NOT NULL COMMENT 'Permission name',
    description TEXT COMMENT 'Permission description',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation timestamp',
    
    UNIQUE INDEX idx_permission_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Application permissions';

/**
 * CREATE TABLE: role_permissions
 * Purpose: Map roles to permissions (many-to-many)
 */
CREATE TABLE IF NOT EXISTS role_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT NOT NULL COMMENT 'Foreign key to roles',
    permission_id INT NOT NULL COMMENT 'Foreign key to permissions',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_role_permission (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Role-Permission mapping';

/**
 * CREATE TABLE: password_reset_tokens
 * Purpose: Store password reset tokens
 */
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Token identifier',
    user_id INT NOT NULL COMMENT 'Foreign key to users',
    token VARCHAR(255) NOT NULL UNIQUE COMMENT 'Reset token',
    expires_at TIMESTAMP NOT NULL COMMENT 'Token expiration time',
    used_at TIMESTAMP NULL COMMENT 'Token usage timestamp',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Token creation timestamp',
    
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at),
    CONSTRAINT fk_password_reset_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Password reset tokens';

/**
 * CREATE TABLE: sessions
 * Purpose: Store user sessions for security tracking
 */
CREATE TABLE IF NOT EXISTS sessions (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Session identifier',
    user_id INT NOT NULL COMMENT 'Foreign key to users',
    session_token VARCHAR(255) UNIQUE NOT NULL COMMENT 'Session token',
    ip_address VARCHAR(45) COMMENT 'User IP address',
    user_agent VARCHAR(500) COMMENT 'Browser user agent',
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last activity timestamp',
    expires_at TIMESTAMP NOT NULL COMMENT 'Session expiration time',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Session creation timestamp',
    
    INDEX idx_user_id (user_id),
    INDEX idx_session_token (session_token),
    INDEX idx_expires_at (expires_at),
    CONSTRAINT fk_sessions_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User sessions';

// ============================================
// ACADEMIC STRUCTURE
// ============================================

/**
 * CREATE TABLE: faculties
 * Purpose: Store university faculties
 */
CREATE TABLE IF NOT EXISTS faculties (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Faculty identifier',
    name VARCHAR(200) NOT NULL COMMENT 'Faculty name',
    code VARCHAR(20) UNIQUE NOT NULL COMMENT 'Faculty code',
    description TEXT COMMENT 'Faculty description',
    icon_url VARCHAR(500) COMMENT 'Faculty icon URL',
    status ENUM('active', 'inactive') DEFAULT 'active' COMMENT 'Faculty status',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='University faculties';

/**
 * CREATE TABLE: departments
 * Purpose: Store academic departments
 */
CREATE TABLE IF NOT EXISTS departments (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Department identifier',
    faculty_id INT NOT NULL COMMENT 'Foreign key to faculties',
    name VARCHAR(200) NOT NULL COMMENT 'Department name',
    code VARCHAR(20) UNIQUE NOT NULL COMMENT 'Department code',
    description TEXT COMMENT 'Department description',
    head_of_department_id INT COMMENT 'Foreign key to users (department head)',
    status ENUM('active', 'inactive') DEFAULT 'active' COMMENT 'Department status',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_faculty_id (faculty_id),
    INDEX idx_status (status),
    CONSTRAINT fk_departments_faculties FOREIGN KEY (faculty_id) REFERENCES faculties(id) ON DELETE RESTRICT,
    CONSTRAINT fk_departments_head FOREIGN KEY (head_of_department_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Academic departments';

/**
 * CREATE TABLE: courses
 * Purpose: Store course information
 */
CREATE TABLE IF NOT EXISTS courses (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Course identifier',
    department_id INT NOT NULL COMMENT 'Foreign key to departments',
    name VARCHAR(255) NOT NULL COMMENT 'Course name',
    code VARCHAR(20) UNIQUE NOT NULL COMMENT 'Course code (e.g., CS101)',
    description TEXT COMMENT 'Course description',
    credits INT COMMENT 'Course credit units',
    level INT COMMENT 'Course level (100, 200, 300, 400)',
    semester ENUM('1', '2', 'summer') COMMENT 'Typical semester offered',
    status ENUM('active', 'inactive', 'archived') DEFAULT 'active' COMMENT 'Course status',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_department_id (department_id),
    INDEX idx_code (code),
    INDEX idx_level (level),
    INDEX idx_status (status),
    FULLTEXT INDEX ft_course_search (name, code),
    CONSTRAINT fk_courses_departments FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Course information';

/**
 * CREATE TABLE: course_lecturers
 * Purpose: Map courses to lecturers (many-to-many)
 */
CREATE TABLE IF NOT EXISTS course_lecturers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL COMMENT 'Foreign key to courses',
    lecturer_id INT NOT NULL COMMENT 'Foreign key to users (lecturer)',
    is_primary BOOLEAN DEFAULT FALSE COMMENT 'Primary lecturer flag',
    academic_year VARCHAR(10) COMMENT 'Academic year (e.g., 2023/2024)',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_course_id (course_id),
    INDEX idx_lecturer_id (lecturer_id),
    CONSTRAINT fk_course_lecturers_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_course_lecturers_lecturer FOREIGN KEY (lecturer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Course-Lecturer mapping';

// ============================================
// PAST PAPERS AND MATERIALS
// ============================================

/**
 * CREATE TABLE: past_papers
 * Purpose: Store past examination papers
 */
CREATE TABLE IF NOT EXISTS past_papers (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Paper identifier',
    course_id INT NOT NULL COMMENT 'Foreign key to courses',
    uploader_id INT NOT NULL COMMENT 'Foreign key to users (uploader)',
    title VARCHAR(255) NOT NULL COMMENT 'Paper title',
    description TEXT COMMENT 'Paper description',
    academic_year VARCHAR(10) COMMENT 'Academic year',
    semester ENUM('1', '2', 'summer') COMMENT 'Semester',
    exam_date DATE COMMENT 'Examination date',
    file_path VARCHAR(500) NOT NULL COMMENT 'File storage path',
    file_name VARCHAR(255) NOT NULL COMMENT 'Original file name',
    file_size INT COMMENT 'File size in bytes',
    file_type VARCHAR(50) COMMENT 'File MIME type',
    thumbnail_url VARCHAR(500) COMMENT 'Thumbnail image URL',
    duration INT COMMENT 'Exam duration in minutes',
    total_marks INT COMMENT 'Total marks',
    status ENUM('pending', 'approved', 'rejected', 'archived') DEFAULT 'pending' COMMENT 'Review status',
    rejection_reason TEXT COMMENT 'Reason if rejected',
    download_count INT DEFAULT 0 COMMENT 'Number of downloads',
    view_count INT DEFAULT 0 COMMENT 'Number of views',
    average_rating DECIMAL(3, 2) DEFAULT 0 COMMENT 'Average rating score',
    rating_count INT DEFAULT 0 COMMENT 'Number of ratings',
    keywords VARCHAR(500) COMMENT 'Search keywords',
    tags VARCHAR(500) COMMENT 'Related tags',
    is_featured BOOLEAN DEFAULT FALSE COMMENT 'Featured paper flag',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_course_id (course_id),
    INDEX idx_uploader_id (uploader_id),
    INDEX idx_status (status),
    INDEX idx_academic_year (academic_year),
    INDEX idx_semester (semester),
    INDEX idx_created_at (created_at),
    INDEX idx_download_count (download_count),
    INDEX idx_is_featured (is_featured),
    FULLTEXT INDEX ft_paper_search (title, description, keywords, tags),
    CONSTRAINT fk_past_papers_courses FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_past_papers_users FOREIGN KEY (uploader_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Past examination papers';

/**
 * CREATE TABLE: learning_materials
 * Purpose: Store learning materials (notes, assignments, etc.)
 */
CREATE TABLE IF NOT EXISTS learning_materials (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Material identifier',
    course_id INT NOT NULL COMMENT 'Foreign key to courses',
    uploader_id INT NOT NULL COMMENT 'Foreign key to users',
    title VARCHAR(255) NOT NULL COMMENT 'Material title',
    description TEXT COMMENT 'Material description',
    material_type ENUM('lecture_note', 'assignment', 'tutorial', 'guide', 'book', 'reference', 'video', 'link', 'other') DEFAULT 'lecture_note' COMMENT 'Type of material',
    file_path VARCHAR(500) COMMENT 'File storage path',
    file_name VARCHAR(255) COMMENT 'Original file name',
    file_size INT COMMENT 'File size in bytes',
    external_url VARCHAR(500) COMMENT 'External resource URL (for links/videos)',
    thumbnail_url VARCHAR(500) COMMENT 'Thumbnail image URL',
    status ENUM('pending', 'approved', 'rejected', 'archived') DEFAULT 'pending',
    rejection_reason TEXT COMMENT 'Reason if rejected',
    download_count INT DEFAULT 0,
    view_count INT DEFAULT 0,
    average_rating DECIMAL(3, 2) DEFAULT 0,
    rating_count INT DEFAULT 0,
    keywords VARCHAR(500),
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_course_id (course_id),
    INDEX idx_uploader_id (uploader_id),
    INDEX idx_material_type (material_type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    FULLTEXT INDEX ft_material_search (title, description, keywords),
    CONSTRAINT fk_learning_materials_courses FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_learning_materials_users FOREIGN KEY (uploader_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Learning materials (notes, assignments, etc.)';

/**
 * CREATE TABLE: research_papers
 * Purpose: Store research papers and dissertations
 */
CREATE TABLE IF NOT EXISTS research_papers (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Research paper identifier',
    course_id INT COMMENT 'Associated course (optional)',
    uploader_id INT NOT NULL COMMENT 'Foreign key to users (student)',
    supervisor_id INT COMMENT 'Foreign key to users (supervisor)',
    title VARCHAR(500) NOT NULL COMMENT 'Research title',
    abstract TEXT COMMENT 'Research abstract',
    description TEXT COMMENT 'Full description',
    research_type ENUM('final_year_project', 'dissertation', 'thesis', 'research_paper', 'other') DEFAULT 'final_year_project' COMMENT 'Type of research',
    academic_year VARCHAR(10) COMMENT 'Academic year',
    submission_date DATE COMMENT 'Submission date',
    file_path VARCHAR(500) NOT NULL COMMENT 'File storage path',
    file_name VARCHAR(255) NOT NULL COMMENT 'Original file name',
    file_size INT COMMENT 'File size in bytes',
    thumbnail_url VARCHAR(500) COMMENT 'Thumbnail image URL',
    status ENUM('draft', 'submitted', 'under_review', 'approved', 'rejected', 'published', 'archived') DEFAULT 'draft',
    rejection_reason TEXT COMMENT 'Reason if rejected',
    download_count INT DEFAULT 0,
    view_count INT DEFAULT 0,
    average_rating DECIMAL(3, 2) DEFAULT 0,
    rating_count INT DEFAULT 0,
    keywords VARCHAR(500),
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_course_id (course_id),
    INDEX idx_uploader_id (uploader_id),
    INDEX idx_supervisor_id (supervisor_id),
    INDEX idx_research_type (research_type),
    INDEX idx_status (status),
    INDEX idx_academic_year (academic_year),
    FULLTEXT INDEX ft_research_search (title, abstract, keywords),
    CONSTRAINT fk_research_papers_courses FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    CONSTRAINT fk_research_papers_uploader FOREIGN KEY (uploader_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_research_papers_supervisor FOREIGN KEY (supervisor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Research papers, dissertations, and theses';

// ============================================
// DOWNLOADS AND INTERACTIONS
// ============================================

/**
 * CREATE TABLE: downloads
 * Purpose: Track paper/material downloads
 */
CREATE TABLE IF NOT EXISTS downloads (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Download record identifier',
    user_id INT NOT NULL COMMENT 'Foreign key to users',
    paper_id INT COMMENT 'Foreign key to past_papers',
    material_id INT COMMENT 'Foreign key to learning_materials',
    research_id INT COMMENT 'Foreign key to research_papers',
    ip_address VARCHAR(45) COMMENT 'User IP address',
    user_agent VARCHAR(500) COMMENT 'Browser user agent',
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_paper_id (paper_id),
    INDEX idx_material_id (material_id),
    INDEX idx_research_id (research_id),
    INDEX idx_downloaded_at (downloaded_at),
    CONSTRAINT fk_downloads_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_downloads_papers FOREIGN KEY (paper_id) REFERENCES past_papers(id) ON DELETE CASCADE,
    CONSTRAINT fk_downloads_materials FOREIGN KEY (material_id) REFERENCES learning_materials(id) ON DELETE CASCADE,
    CONSTRAINT fk_downloads_research FOREIGN KEY (research_id) REFERENCES research_papers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Download tracking';

/**
 * CREATE TABLE: ratings
 * Purpose: Store user ratings for papers and materials
 */
CREATE TABLE IF NOT EXISTS ratings (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Rating identifier',
    user_id INT NOT NULL COMMENT 'Foreign key to users',
    paper_id INT COMMENT 'Foreign key to past_papers',
    material_id INT COMMENT 'Foreign key to learning_materials',
    research_id INT COMMENT 'Foreign key to research_papers',
    rating INT NOT NULL COMMENT 'Rating value (1-5)',
    review TEXT COMMENT 'Review text',
    helpful_count INT DEFAULT 0 COMMENT 'Number of helpful votes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_paper_id (paper_id),
    INDEX idx_material_id (material_id),
    INDEX idx_research_id (research_id),
    INDEX idx_rating (rating),
    CONSTRAINT fk_ratings_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ratings_papers FOREIGN KEY (paper_id) REFERENCES past_papers(id) ON DELETE CASCADE,
    CONSTRAINT fk_ratings_materials FOREIGN KEY (material_id) REFERENCES learning_materials(id) ON DELETE CASCADE,
    CONSTRAINT fk_ratings_research FOREIGN KEY (research_id) REFERENCES research_papers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User ratings and reviews';

/**
 * CREATE TABLE: bookmarks
 * Purpose: Store user bookmarked materials
 */
CREATE TABLE IF NOT EXISTS bookmarks (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Bookmark identifier',
    user_id INT NOT NULL COMMENT 'Foreign key to users',
    paper_id INT COMMENT 'Foreign key to past_papers',
    material_id INT COMMENT 'Foreign key to learning_materials',
    research_id INT COMMENT 'Foreign key to research_papers',
    course_id INT COMMENT 'Foreign key to courses',
    bookmark_type ENUM('paper', 'material', 'research', 'course') COMMENT 'Type of bookmarked item',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_paper_id (paper_id),
    INDEX idx_material_id (material_id),
    INDEX idx_research_id (research_id),
    INDEX idx_course_id (course_id),
    UNIQUE INDEX idx_user_paper (user_id, paper_id, paper_id IS NOT NULL),
    CONSTRAINT fk_bookmarks_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_bookmarks_papers FOREIGN KEY (paper_id) REFERENCES past_papers(id) ON DELETE CASCADE,
    CONSTRAINT fk_bookmarks_materials FOREIGN KEY (material_id) REFERENCES learning_materials(id) ON DELETE CASCADE,
    CONSTRAINT fk_bookmarks_research FOREIGN KEY (research_id) REFERENCES research_papers(id) ON DELETE CASCADE,
    CONSTRAINT fk_bookmarks_courses FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User bookmarks';

// ============================================
// FORUM AND DISCUSSIONS
// ============================================

/**
 * CREATE TABLE: discussions
 * Purpose: Store forum discussions
 */
CREATE TABLE IF NOT EXISTS discussions (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Discussion identifier',
    course_id INT COMMENT 'Associated course',
    creator_id INT NOT NULL COMMENT 'Foreign key to users (creator)',
    title VARCHAR(500) NOT NULL COMMENT 'Discussion title',
    description TEXT COMMENT 'Discussion description',
    discussion_type ENUM('question', 'topic', 'announcement', 'poll', 'general') DEFAULT 'topic' COMMENT 'Type of discussion',
    status ENUM('active', 'locked', 'archived', 'deleted') DEFAULT 'active' COMMENT 'Discussion status',
    is_pinned BOOLEAN DEFAULT FALSE COMMENT 'Pinned discussion flag',
    reply_count INT DEFAULT 0 COMMENT 'Number of replies',
    view_count INT DEFAULT 0 COMMENT 'Number of views',
    solved BOOLEAN DEFAULT FALSE COMMENT 'Marked as solved flag',
    solved_by_id INT COMMENT 'User who marked as solved',
    best_answer_id INT COMMENT 'Best answer reply ID',
    keywords VARCHAR(500) COMMENT 'Search keywords',
    tags VARCHAR(500) COMMENT 'Discussion tags',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_course_id (course_id),
    INDEX idx_creator_id (creator_id),
    INDEX idx_status (status),
    INDEX idx_is_pinned (is_pinned),
    INDEX idx_solved (solved),
    INDEX idx_created_at (created_at),
    FULLTEXT INDEX ft_discussion_search (title, description, keywords, tags),
    CONSTRAINT fk_discussions_courses FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    CONSTRAINT fk_discussions_creator FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Forum discussions and topics';

/**
 * CREATE TABLE: discussion_replies
 * Purpose: Store replies to discussions
 */
CREATE TABLE IF NOT EXISTS discussion_replies (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Reply identifier',
    discussion_id INT NOT NULL COMMENT 'Foreign key to discussions',
    author_id INT NOT NULL COMMENT 'Foreign key to users (reply author)',
    parent_reply_id INT COMMENT 'Foreign key to parent reply (nested)',
    content TEXT NOT NULL COMMENT 'Reply content',
    is_best_answer BOOLEAN DEFAULT FALSE COMMENT 'Best answer flag',
    helpful_count INT DEFAULT 0 COMMENT 'Number of helpful votes',
    status ENUM('active', 'deleted') DEFAULT 'active' COMMENT 'Reply status',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_discussion_id (discussion_id),
    INDEX idx_author_id (author_id),
    INDEX idx_parent_reply_id (parent_reply_id),
    INDEX idx_is_best_answer (is_best_answer),
    INDEX idx_created_at (created_at),
    FULLTEXT INDEX ft_reply_search (content),
    CONSTRAINT fk_discussion_replies_discussion FOREIGN KEY (discussion_id) REFERENCES discussions(id) ON DELETE CASCADE,
    CONSTRAINT fk_discussion_replies_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_discussion_replies_parent FOREIGN KEY (parent_reply_id) REFERENCES discussion_replies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Replies to forum discussions';

/**
 * CREATE TABLE: discussion_votes
 * Purpose: Track votes on discussions and replies
 */
CREATE TABLE IF NOT EXISTS discussion_votes (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Vote identifier',
    user_id INT NOT NULL COMMENT 'Foreign key to users (voter)',
    discussion_id INT COMMENT 'Foreign key to discussions',
    reply_id INT COMMENT 'Foreign key to discussion_replies',
    vote_type ENUM('upvote', 'downvote') COMMENT 'Vote direction',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_discussion_id (discussion_id),
    INDEX idx_reply_id (reply_id),
    UNIQUE INDEX idx_user_discussion_vote (user_id, discussion_id, discussion_id IS NOT NULL),
    UNIQUE INDEX idx_user_reply_vote (user_id, reply_id, reply_id IS NOT NULL),
    CONSTRAINT fk_discussion_votes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_discussion_votes_discussion FOREIGN KEY (discussion_id) REFERENCES discussions(id) ON DELETE CASCADE,
    CONSTRAINT fk_discussion_votes_reply FOREIGN KEY (reply_id) REFERENCES discussion_replies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Voting on discussions and replies';

// ============================================
// MESSAGING
// ============================================

/**
 * CREATE TABLE: messages
 * Purpose: Store private messages between users
 */
CREATE TABLE IF NOT EXISTS messages (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Message identifier',
    sender_id INT NOT NULL COMMENT 'Foreign key to users (sender)',
    recipient_id INT NOT NULL COMMENT 'Foreign key to users (recipient)',
    subject VARCHAR(255) COMMENT 'Message subject',
    content TEXT NOT NULL COMMENT 'Message content',
    is_read BOOLEAN DEFAULT FALSE COMMENT 'Read status',
    read_at TIMESTAMP NULL COMMENT 'Read timestamp',
    attachment_path VARCHAR(500) COMMENT 'Attachment file path',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_sender_id (sender_id),
    INDEX idx_recipient_id (recipient_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_recipient FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Private messages between users';

// ============================================
// NOTIFICATIONS
// ============================================

/**
 * CREATE TABLE: notifications
 * Purpose: Store user notifications
 */
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Notification identifier',
    user_id INT NOT NULL COMMENT 'Foreign key to users (recipient)',
    sender_id INT COMMENT 'Foreign key to users (who triggered)',
    notification_type ENUM('upload_approved', 'upload_rejected', 'comment', 'reply', 'rating', 'mention', 'announcement', 'message', 'download', 'other') COMMENT 'Notification type',
    title VARCHAR(255) NOT NULL COMMENT 'Notification title',
    description TEXT COMMENT 'Notification description',
    related_paper_id INT COMMENT 'Related past paper ID',
    related_material_id INT COMMENT 'Related material ID',
    related_discussion_id INT COMMENT 'Related discussion ID',
    is_read BOOLEAN DEFAULT FALSE COMMENT 'Read status',
    read_at TIMESTAMP NULL COMMENT 'Read timestamp',
    action_url VARCHAR(500) COMMENT 'URL to related content',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_sender_id (sender_id),
    INDEX idx_notification_type (notification_type),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_notifications_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User notifications';

// ============================================
// ANNOUNCEMENTS
// ============================================

/**
 * CREATE TABLE: announcements
 * Purpose: Store university announcements
 */
CREATE TABLE IF NOT EXISTS announcements (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Announcement identifier',
    creator_id INT NOT NULL COMMENT 'Foreign key to users (admin)',
    title VARCHAR(500) NOT NULL COMMENT 'Announcement title',
    content TEXT NOT NULL COMMENT 'Announcement content',
    announcement_type ENUM('general', 'academic', 'exam', 'event', 'urgent', 'other') DEFAULT 'general' COMMENT 'Type of announcement',
    target_audience ENUM('all', 'students', 'lecturers', 'admin') DEFAULT 'all' COMMENT 'Target audience',
    is_pinned BOOLEAN DEFAULT FALSE COMMENT 'Pinned announcement flag',
    is_featured BOOLEAN DEFAULT FALSE COMMENT 'Featured announcement flag',
    published_from TIMESTAMP COMMENT 'Publication start time',
    published_until TIMESTAMP NULL COMMENT 'Publication end time',
    status ENUM('draft', 'published', 'archived', 'deleted') DEFAULT 'draft' COMMENT 'Announcement status',
    view_count INT DEFAULT 0 COMMENT 'Number of views',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_creator_id (creator_id),
    INDEX idx_announcement_type (announcement_type),
    INDEX idx_is_pinned (is_pinned),
    INDEX idx_status (status),
    INDEX idx_published_from (published_from),
    FULLTEXT INDEX ft_announcement_search (title, content),
    CONSTRAINT fk_announcements_creator FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='University announcements';

// ============================================
// ACTIVITY LOGGING & AUDITING
// ============================================

/**
 * CREATE TABLE: activity_logs
 * Purpose: Track user activity for analytics and security
 */
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Log entry identifier',
    user_id INT COMMENT 'Foreign key to users',
    action VARCHAR(100) NOT NULL COMMENT 'Action performed',
    resource_type VARCHAR(50) COMMENT 'Type of resource affected',
    resource_id INT COMMENT 'ID of resource affected',
    details JSON COMMENT 'Additional action details',
    ip_address VARCHAR(45) COMMENT 'User IP address',
    user_agent VARCHAR(500) COMMENT 'Browser user agent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_resource_type (resource_type),
    INDEX idx_created_at (created_at),
    CONSTRAINT fk_activity_logs_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Activity logs for tracking and auditing';

/**
 * CREATE TABLE: audit_logs
 * Purpose: Store audit trails for sensitive operations
 */
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Audit log identifier',
    user_id INT COMMENT 'Foreign key to users (who performed)',
    table_name VARCHAR(100) NOT NULL COMMENT 'Affected table name',
    record_id INT COMMENT 'ID of affected record',
    operation ENUM('CREATE', 'UPDATE', 'DELETE', 'APPROVE', 'REJECT') COMMENT 'Operation type',
    old_values JSON COMMENT 'Previous values',
    new_values JSON COMMENT 'New values',
    reason TEXT COMMENT 'Reason for change',
    ip_address VARCHAR(45) COMMENT 'User IP address',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_table_name (table_name),
    INDEX idx_operation (operation),
    INDEX idx_created_at (created_at),
    CONSTRAINT fk_audit_logs_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Audit trail for critical operations';

// ============================================
// SETTINGS & CONFIGURATION
// ============================================

/**
 * CREATE TABLE: system_settings
 * Purpose: Store system configuration and settings
 */
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Setting identifier',
    setting_key VARCHAR(100) UNIQUE NOT NULL COMMENT 'Setting key',
    setting_value LONGTEXT COMMENT 'Setting value (can be JSON)',
    setting_type ENUM('string', 'boolean', 'integer', 'json', 'email') DEFAULT 'string' COMMENT 'Value type',
    description TEXT COMMENT 'Setting description',
    is_public BOOLEAN DEFAULT FALSE COMMENT 'Public visibility flag',
    updated_by INT COMMENT 'Foreign key to users (who updated)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_setting_key (setting_key),
    INDEX idx_updated_by (updated_by),
    CONSTRAINT fk_system_settings_users FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='System settings and configuration';

/**
 * CREATE TABLE: user_preferences
 * Purpose: Store user preferences and settings
 */
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Preference identifier',
    user_id INT NOT NULL UNIQUE COMMENT 'Foreign key to users',
    theme ENUM('light', 'dark', 'auto') DEFAULT 'auto' COMMENT 'UI theme preference',
    language VARCHAR(10) DEFAULT 'en' COMMENT 'Language preference',
    email_notifications BOOLEAN DEFAULT TRUE COMMENT 'Email notification flag',
    in_app_notifications BOOLEAN DEFAULT TRUE COMMENT 'In-app notification flag',
    private_profile BOOLEAN DEFAULT FALSE COMMENT 'Private profile flag',
    show_email BOOLEAN DEFAULT FALSE COMMENT 'Show email publicly',
    newsletter_subscription BOOLEAN DEFAULT TRUE COMMENT 'Newsletter subscription flag',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_user_preferences_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User preferences and settings';

// ============================================
// REPORTS
// ============================================

/**
 * CREATE TABLE: reports
 * Purpose: Store user reports for content moderation
 */
CREATE TABLE IF NOT EXISTS reports (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Report identifier',
    reporter_id INT NOT NULL COMMENT 'Foreign key to users (reporter)',
    reported_paper_id INT COMMENT 'Foreign key to past_papers',
    reported_material_id INT COMMENT 'Foreign key to learning_materials',
    reported_discussion_id INT COMMENT 'Foreign key to discussions',
    reported_reply_id INT COMMENT 'Foreign key to discussion_replies',
    reported_user_id INT COMMENT 'Foreign key to users (reported)',
    report_type ENUM('spam', 'inappropriate', 'copyright', 'broken_link', 'incorrect_content', 'other') COMMENT 'Report type',
    reason TEXT NOT NULL COMMENT 'Report reason',
    status ENUM('pending', 'under_review', 'resolved', 'dismissed') DEFAULT 'pending' COMMENT 'Report status',
    resolution TEXT COMMENT 'Resolution details',
    resolved_by INT COMMENT 'Foreign key to users (admin)',
    resolved_at TIMESTAMP NULL COMMENT 'Resolution timestamp',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_reporter_id (reporter_id),
    INDEX idx_status (status),
    INDEX idx_report_type (report_type),
    INDEX idx_created_at (created_at),
    CONSTRAINT fk_reports_reporter FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_reports_paper FOREIGN KEY (reported_paper_id) REFERENCES past_papers(id) ON DELETE CASCADE,
    CONSTRAINT fk_reports_material FOREIGN KEY (reported_material_id) REFERENCES learning_materials(id) ON DELETE CASCADE,
    CONSTRAINT fk_reports_discussion FOREIGN KEY (reported_discussion_id) REFERENCES discussions(id) ON DELETE CASCADE,
    CONSTRAINT fk_reports_reply FOREIGN KEY (reported_reply_id) REFERENCES discussion_replies(id) ON DELETE CASCADE,
    CONSTRAINT fk_reports_reported_user FOREIGN KEY (reported_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_reports_resolver FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Content reports for moderation';

?>
