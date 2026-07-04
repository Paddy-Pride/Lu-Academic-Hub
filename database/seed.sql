<?php
/**
 * Database Seed Data
 * 
 * Initial data for roles, permissions, faculties, and sample data
 * Run after schema.sql
 * 
 * @category Database
 * @package LU Academic Hub
 * @author Paddy Pride
 */

// ============================================
// INSERT ROLES
// ============================================

INSERT INTO roles (name, display_name, description) VALUES
('student', 'Student', 'Student role - can upload, download, and participate'),
('lecturer', 'Lecturer', 'Lecturer role - can manage course materials and moderate'),
('admin', 'Administrator', 'Administrator role - can manage users and content'),
('super_admin', 'Super Administrator', 'Super admin role - full system access');

// ============================================
// INSERT PERMISSIONS
// ============================================

INSERT INTO permissions (name, description) VALUES
('view_papers', 'Can view past papers'),
('download_papers', 'Can download past papers'),
('upload_papers', 'Can upload past papers'),
('approve_papers', 'Can approve uploaded papers'),
('delete_papers', 'Can delete papers'),
('view_materials', 'Can view learning materials'),
('download_materials', 'Can download materials'),
('upload_materials', 'Can upload materials'),
('approve_materials', 'Can approve materials'),
('manage_courses', 'Can manage courses'),
('manage_faculties', 'Can manage faculties'),
('manage_departments', 'Can manage departments'),
('manage_users', 'Can manage users'),
('manage_forum', 'Can manage forum discussions'),
('view_analytics', 'Can view analytics'),
('manage_announcements', 'Can manage announcements'),
('moderate_content', 'Can moderate user content'),
('system_settings', 'Can change system settings'),
('view_audit_logs', 'Can view audit logs'),
('manage_permissions', 'Can manage permissions');

// ============================================
// INSERT ROLE PERMISSIONS
// ============================================

-- Student permissions
INSERT INTO role_permissions (role_id, permission_id) 
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.name = 'student' AND p.name IN ('view_papers', 'download_papers', 'upload_papers', 'view_materials', 'download_materials', 'upload_materials');

-- Lecturer permissions
INSERT INTO role_permissions (role_id, permission_id) 
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.name = 'lecturer' AND p.name IN ('view_papers', 'download_papers', 'upload_papers', 'view_materials', 'upload_materials', 'manage_courses', 'manage_forum', 'view_analytics');

-- Administrator permissions
INSERT INTO role_permissions (role_id, permission_id) 
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.name = 'admin' AND p.name IN ('manage_users', 'approve_papers', 'approve_materials', 'manage_courses', 'manage_faculties', 'manage_departments', 'manage_announcements', 'moderate_content', 'manage_forum', 'view_analytics');

-- Super Admin - all permissions
INSERT INTO role_permissions (role_id, permission_id) 
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.name = 'super_admin';

// ============================================
// INSERT SAMPLE FACULTIES
// ============================================

INSERT INTO faculties (name, code, description, status) VALUES
('Faculty of Engineering', 'ENG', 'Engineering and Technical Sciences', 'active'),
('Faculty of Business and Economics', 'BUS', 'Business Administration and Economics', 'active'),
('Faculty of Arts and Social Sciences', 'ARTS', 'Liberal Arts and Social Studies', 'active'),
('Faculty of Science', 'SCI', 'Pure and Applied Sciences', 'active'),
('Faculty of Education', 'EDU', 'Education and Teacher Training', 'active'),
('Faculty of Law', 'LAW', 'Law and Legal Studies', 'active'),
('Faculty of Medicine and Health Sciences', 'MED', 'Medicine, Nursing, and Health', 'active'),
('Faculty of Technology', 'TECH', 'Information and Communication Technology', 'active');

// ============================================
// INSERT SAMPLE DEPARTMENTS
// ============================================

INSERT INTO departments (faculty_id, name, code, description, status) VALUES
((SELECT id FROM faculties WHERE code='ENG'), 'Civil Engineering', 'CENG', 'Civil Engineering Department', 'active'),
((SELECT id FROM faculties WHERE code='ENG'), 'Mechanical Engineering', 'MENG', 'Mechanical Engineering Department', 'active'),
((SELECT id FROM faculties WHERE code='ENG'), 'Electrical Engineering', 'EENG', 'Electrical Engineering Department', 'active'),
((SELECT id FROM faculties WHERE code='BUS'), 'Business Administration', 'BA', 'Business Administration Department', 'active'),
((SELECT id FROM faculties WHERE code='BUS'), 'Accounting', 'ACC', 'Accounting Department', 'active'),
((SELECT id FROM faculties WHERE code='SCI'), 'Computer Science', 'CS', 'Computer Science Department', 'active'),
((SELECT id FROM faculties WHERE code='SCI'), 'Mathematics', 'MATH', 'Mathematics Department', 'active'),
((SELECT id FROM faculties WHERE code='TECH'), 'Information Technology', 'IT', 'Information Technology Department', 'active');

// ============================================
// INSERT SAMPLE COURSES
// ============================================

INSERT INTO courses (department_id, name, code, description, credits, level, semester, status) VALUES
((SELECT id FROM departments WHERE code='CS'), 'Introduction to Programming', 'CS101', 'Fundamentals of programming concepts using modern languages', 3, 100, '1', 'active'),
((SELECT id FROM departments WHERE code='CS'), 'Data Structures', 'CS102', 'Advanced data structures and algorithms', 4, 100, '2', 'active'),
((SELECT id FROM departments WHERE code='CS'), 'Database Systems', 'CS201', 'Relational databases and SQL', 4, 200, '1', 'active'),
((SELECT id FROM departments WHERE code='CS'), 'Web Development', 'CS301', 'Full-stack web development', 4, 300, '1', 'active'),
((SELECT id FROM departments WHERE code='MATH'), 'Calculus I', 'MATH101', 'Differential calculus fundamentals', 4, 100, '1', 'active'),
((SELECT id FROM departments WHERE code='MATH'), 'Linear Algebra', 'MATH102', 'Linear systems and matrices', 4, 100, '2', 'active'),
((SELECT id FROM departments WHERE code='BA'), 'Business Management', 'BUS101', 'Principles of business management', 3, 100, '1', 'active'),
((SELECT id FROM departments WHERE code='ACC'), 'Financial Accounting', 'ACC101', 'Introduction to financial accounting', 4, 100, '1', 'active');

// ============================================
// INSERT SYSTEM SETTINGS
// ============================================

INSERT INTO system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('app_name', 'LU Academic Hub', 'string', 'Application name', 1),
('app_title', 'LIRA University Academic Hub', 'string', 'Application title', 1),
('app_description', 'Centralized repository for academic materials', 'string', 'Application description', 1),
('app_logo', '/assets/images/logo.png', 'string', 'Application logo URL', 1),
('max_upload_size', '52428800', 'integer', 'Maximum file upload size in bytes (50MB)', 0),
('allowed_extensions', '["pdf","docx","pptx","xlsx","zip","jpg","jpeg","png","gif"]', 'json', 'Allowed file extensions', 0),
('pagination_limit', '20', 'integer', 'Default pagination limit', 0),
('session_timeout', '1800', 'integer', 'Session timeout in seconds (30 minutes)', 0),
('require_email_verification', 'true', 'boolean', 'Require email verification on signup', 0),
('enable_registration', 'true', 'boolean', 'Enable user registration', 1),
('contact_email', 'support@lu-academic-hub.com', 'email', 'Support contact email', 1),
('smtp_host', 'smtp.gmail.com', 'string', 'SMTP server host', 0),
('smtp_port', '587', 'integer', 'SMTP server port', 0),
('analytics_enabled', 'true', 'boolean', 'Enable analytics tracking', 0),
('dark_mode_enabled', 'true', 'boolean', 'Enable dark mode feature', 1);

// ============================================
// INSERT SAMPLE SUPER ADMIN USER
// ============================================

INSERT INTO users (first_name, last_name, email, phone, password_hash, role_id, status, email_verified, email_verified_at, created_at) VALUES
('System', 'Administrator', 'admin@lirauni.ac.ug', '+256-700-000000', '$2y$10$YourHashedPasswordHere', (SELECT id FROM roles WHERE name='super_admin'), 'active', 1, NOW(), NOW());

// ============================================
// INSERT USER PREFERENCES FOR SAMPLE ADMIN
// ============================================

INSERT INTO user_preferences (user_id, theme, language, email_notifications, in_app_notifications) 
VALUES ((SELECT id FROM users WHERE email='admin@lirauni.ac.ug'), 'auto', 'en', 1, 1);

?>
