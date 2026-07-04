<?php
/**
 * Homepage
 * 
 * Main landing page with hero section, search, and featured content
 * 
 * @category Frontend
 * @package LU Academic Hub
 */

require_once 'includes/config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/Database.php';

startSecureSession();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LU Academic Hub - LIRA University Past Papers & Learning Materials</title>
    <meta name="description" content="Access past papers, learning materials, and academic resources from LIRA University. Join our academic community.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo CSS_URL; ?>style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <i class="fas fa-graduation-cap"></i>
                <span>LU Academic Hub</span>
            </div>
            <ul class="navbar-menu">
                <li><a href="#" class="active">Home</a></li>
                <li><a href="#past-papers">Past Papers</a></li>
                <li><a href="#materials">Materials</a></li>
                <li><a href="#courses">Courses</a></li>
                <li><a href="#forum">Forum</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="student/dashboard.php">Dashboard</a></li>
                    <li><a href="auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="auth/login.php" class="btn btn-primary btn-sm">Sign In</a></li>
                    <li><a href="auth/register.php" class="btn btn-outline btn-sm">Sign Up</a></li>
                <?php endif; ?>
            </ul>
            <button class="btn btn-sm" id="themeToggle" style="background: none; border: none;">
                <i class="fas fa-moon"></i>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Your Academic Resource Hub</h1>
            <p>Access thousands of past papers, lecture notes, and learning materials from LIRA University</p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <a href="#search" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> Explore Resources
                </a>
                <?php if (!isLoggedIn()): ?>
                    <a href="auth/register.php" class="btn btn-outline btn-lg" style="color: white; border-color: white;">
                        <i class="fas fa-user-plus"></i> Join Now
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <section class="container" id="search" style="margin: var(--spacing-2xl) auto;">
        <div class="search-container">
            <form class="search-form">
                <div class="form-group" style="margin: 0;">
                    <input type="text" class="form-control" placeholder="Search by course or topic..." name="query">
                </div>
                <div class="form-group" style="margin: 0;">
                    <select class="form-select" name="faculty">
                        <option value="">All Faculties</option>
                        <option value="eng">Engineering</option>
                        <option value="bus">Business</option>
                        <option value="sci">Science</option>
                        <option value="tech">Technology</option>
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <select class="form-select" name="year">
                        <option value="">All Years</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                        <option value="2022">2022</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top: 0;">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="container" style="margin: var(--spacing-2xl) auto;">
        <div class="grid grid-4">
            <div class="card text-center" style="border: none;">
                <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: var(--spacing-md);">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <h3>12,000+</h3>
                <p style="margin: 0;">Past Papers</p>
            </div>
            <div class="card text-center" style="border: none;">
                <div style="font-size: 2.5rem; color: var(--secondary); margin-bottom: var(--spacing-md);">
                    <i class="fas fa-book"></i>
                </div>
                <h3>8</h3>
                <p style="margin: 0;">Faculties</p>
            </div>
            <div class="card text-center" style="border: none;">
                <div style="font-size: 2.5rem; color: var(--info); margin-bottom: var(--spacing-md);">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3>4,000+</h3>
                <p style="margin: 0;">Active Students</p>
            </div>
            <div class="card text-center" style="border: none;">
                <div style="font-size: 2.5rem; color: var(--warning); margin-bottom: var(--spacing-md);">
                    <i class="fas fa-users"></i>
                </div>
                <h3>500+</h3>
                <p style="margin: 0;">Lecturers</p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="container" style="margin: var(--spacing-2xl) auto;">
        <h2 style="text-align: center; margin-bottom: var(--spacing-2xl);">Why Choose LU Academic Hub?</h2>
        <div class="grid grid-3">
            <div class="card">
                <div style="font-size: 2rem; color: var(--primary); margin-bottom: var(--spacing-md);">
                    <i class="fas fa-lock"></i>
                </div>
                <h4>Secure & Private</h4>
                <p>Your data is protected with industry-standard encryption and security protocols.</p>
            </div>
            <div class="card">
                <div style="font-size: 2rem; color: var(--secondary); margin-bottom: var(--spacing-md);">
                    <i class="fas fa-bolt"></i>
                </div>
                <h4>Fast & Reliable</h4>
                <p>Lightning-fast search and download speeds with 99.9% uptime guarantee.</p>
            </div>
            <div class="card">
                <div style="font-size: 2rem; color: var(--info); margin-bottom: var(--spacing-md);">
                    <i class="fas fa-users"></i>
                </div>
                <h4>Community Driven</h4>
                <p>Connect with peers, share resources, and collaborate on your academic journey.</p>
            </div>
            <div class="card">
                <div style="font-size: 2rem; color: var(--warning); margin-bottom: var(--spacing-md);">
                    <i class="fas fa-search"></i>
                </div>
                <h4>Advanced Search</h4>
                <p>Find exactly what you need with powerful filtering and full-text search.</p>
            </div>
            <div class="card">
                <div style="font-size: 2rem; color: var(--success); margin-bottom: var(--spacing-md);">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h4>Mobile Friendly</h4>
                <p>Access your resources anytime, anywhere on any device.</p>
            </div>
            <div class="card">
                <div style="font-size: 2rem; color: var(--danger); margin-bottom: var(--spacing-md);">
                    <i class="fas fa-star"></i>
                </div>
                <h4>Ratings & Reviews</h4>
                <p>See ratings from other students to find the best study materials.</p>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; padding: var(--spacing-2xl); border-radius: var(--radius-xl); margin: var(--spacing-2xl) 0;" class="container">
        <div style="text-align: center;">
            <h2 style="color: white; margin-bottom: var(--spacing-lg);">Ready to Get Started?</h2>
            <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem; margin-bottom: var(--spacing-lg);">Join thousands of students already using LU Academic Hub to ace their studies.</p>
            <?php if (!isLoggedIn()): ?>
                <a href="auth/register.php" class="btn btn-outline btn-lg" style="color: white; border-color: white;">
                    <i class="fas fa-user-plus"></i> Create Your Account
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4><i class="fas fa-graduation-cap"></i> LU Academic Hub</h4>
                    <p>Empowering students and educators with access to quality academic resources.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="#">Home</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Resources</h4>
                    <ul class="footer-links">
                        <li><a href="#">Past Papers</a></li>
                        <li><a href="#">Learning Materials</a></li>
                        <li><a href="#">Courses</a></li>
                        <li><a href="#">Discussion Forum</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul class="footer-links">
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 LIRA University Academic Hub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dark mode toggle
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        
        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);
        
        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
        
        function updateThemeIcon(theme) {
            themeToggle.innerHTML = theme === 'light' ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
        }
    </script>
</body>
</html>
