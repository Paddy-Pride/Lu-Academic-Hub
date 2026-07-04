-- Create Database
CREATE DATABASE IF NOT EXISTS lu_academic_hub;
USE lu_academic_hub;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    faculty VARCHAR(100),
    year_of_study INT,
    phone VARCHAR(20),
    avatar_url VARCHAR(255),
    role ENUM('student', 'admin', 'moderator') DEFAULT 'student',
    is_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    reset_token_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login DATETIME,
    is_active BOOLEAN DEFAULT TRUE
);

-- Past Papers Table
CREATE TABLE IF NOT EXISTS past_papers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    course_code VARCHAR(50) NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    faculty VARCHAR(100),
    year INT,
    semester VARCHAR(20),
    file_path VARCHAR(255) NOT NULL,
    file_size INT,
    file_type VARCHAR(50),
    uploaded_by INT NOT NULL,
    description TEXT,
    difficulty_level ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    downloads_count INT DEFAULT 0,
    views_count INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0,
    is_approved BOOLEAN DEFAULT FALSE,
    approved_by INT,
    approved_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_course (course_code),
    INDEX idx_faculty (faculty),
    INDEX idx_year (year),
    FULLTEXT INDEX ft_search (title, course_name, description)
);

-- Learning Materials Table
CREATE TABLE IF NOT EXISTS learning_materials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    course_code VARCHAR(50) NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    faculty VARCHAR(100),
    year INT,
    material_type ENUM('notes', 'textbook', 'video', 'tutorial', 'guide', 'other') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT,
    file_type VARCHAR(50),
    uploaded_by INT NOT NULL,
    description TEXT,
    tags VARCHAR(255),
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    downloads_count INT DEFAULT 0,
    views_count INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0,
    is_approved BOOLEAN DEFAULT FALSE,
    approved_by INT,
    approved_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_course (course_code),
    INDEX idx_type (material_type),
    INDEX idx_faculty (faculty),
    FULLTEXT INDEX ft_search (title, course_name, description, tags)
);

-- Ratings & Reviews Table
CREATE TABLE IF NOT EXISTS ratings_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    resource_id INT NOT NULL,
    resource_type ENUM('past_paper', 'learning_material') NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    is_helpful BOOLEAN DEFAULT FALSE,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rating (user_id, resource_id, resource_type),
    INDEX idx_resource (resource_id, resource_type)
);

-- Downloads History Table
CREATE TABLE IF NOT EXISTS download_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    resource_id INT NOT NULL,
    resource_type ENUM('past_paper', 'learning_material') NOT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_date (downloaded_at)
);

-- Search History Table
CREATE TABLE IF NOT EXISTS search_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    search_query VARCHAR(255) NOT NULL,
    results_count INT DEFAULT 0,
    search_type ENUM('papers', 'materials', 'all') DEFAULT 'all',
    ip_address VARCHAR(45),
    searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_date (searched_at),
    FULLTEXT INDEX ft_query (search_query)
);

-- Bookmarks Table
CREATE TABLE IF NOT EXISTS bookmarks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    resource_id INT NOT NULL,
    resource_type ENUM('past_paper', 'learning_material') NOT NULL,
    folder_name VARCHAR(100) DEFAULT 'default',
    bookmarked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_bookmark (user_id, resource_id, resource_type),
    INDEX idx_user (user_id),
    INDEX idx_folder (folder_name)
);

-- File Uploads Log Table
CREATE TABLE IF NOT EXISTS file_uploads_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    stored_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT,
    file_type VARCHAR(50),
    mime_type VARCHAR(50),
    upload_status ENUM('success', 'failed', 'quarantine') DEFAULT 'success',
    error_message TEXT,
    virus_scan_status ENUM('clean', 'infected', 'pending') DEFAULT 'pending',
    ip_address VARCHAR(45),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_date (uploaded_at)
);

-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    notification_type ENUM('upload_approved', 'new_material', 'rating', 'comment', 'system') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    action_url VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    related_resource_id INT,
    resource_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read)
);

-- Admin Logs Table
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    old_value JSON,
    new_value JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    log_message TEXT,
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_admin (admin_id),
    INDEX idx_date (logged_at)
);

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_papers_created ON past_papers(created_at);
CREATE INDEX idx_materials_created ON learning_materials(created_at);