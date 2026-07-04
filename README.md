# LIRA University Academic Hub (LU Academic Hub)

## Overview

LU Academic Hub is a modern, production-ready web application designed for LIRA University. It serves as a centralized repository for past papers, learning materials, research documents, and academic collaboration.

The platform is comparable to modern educational platforms such as Moodle, StudoCU, CourseHero, and Google Drive, but specifically tailored for the needs of LIRA University.

## Features

### Core Modules

- **Past Papers Repository**: Upload, manage, and access past examination papers
- **Learning Materials**: Lecture notes, assignments, tutorials, and reference materials
- **Research Repository**: Final year projects, dissertations, and research papers
- **Discussion Forum**: Q&A and course-specific discussions with voting and best answer selection
- **Course Management**: Faculty, department, and course information
- **User Dashboard**: Student and lecturer dashboards with personalized content
- **Admin Panel**: Comprehensive administrative tools and analytics
- **Notifications System**: Real-time notifications for uploads, comments, and announcements
- **Messaging System**: Direct communication between students, lecturers, and admins
- **Bookmarks & Favorites**: Save and organize important materials
- **Analytics**: Charts and statistics on usage, downloads, and user activity

### Technical Features

- **Responsive Design**: Mobile-first approach with full desktop support
- **Dark & Light Mode**: User preference-based theme switching
- **Security**: Prepared statements, CSRF protection, XSS prevention, rate limiting
- **Performance**: Pagination, lazy loading, caching, optimized queries
- **Accessibility**: WCAG compliant with ARIA labels and keyboard navigation
- **Modern UI**: Material Design inspiration, glassmorphism, smooth animations

## Technology Stack

### Frontend
- HTML5
- CSS3 (with custom properties and modern layouts)
- JavaScript (Vanilla ES6+)
- Bootstrap 5
- Font Awesome Icons
- Google Fonts
- Chart.js for analytics

### Backend
- PHP 8+
- RESTful APIs with JSON

### Database
- MySQL 5.7+

## System Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher (MySQL 8.0 recommended)
- Web server: Apache or Nginx
- Minimum 500MB disk space
- 256MB RAM minimum

## Installation

See [INSTALLATION.md](./docs/INSTALLATION.md) for detailed setup instructions.

## Project Structure

```
lu-academic-hub/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── includes/
│   ├── config/
│   ├── database/
│   ├── functions/
│   └── templates/
├── uploads/
│   ├── papers/
│   ├── research/
│   └── profile/
├── admin/
├── student/
├── lecturer/
├── api/
├── auth/
├── forum/
├── research/
├── courses/
├── logs/
├── backups/
├── docs/
└── index.php
```

## Documentation

- [Installation Guide](./docs/INSTALLATION.md)
- [Configuration Guide](./docs/CONFIGURATION.md)
- [Database Schema](./docs/DATABASE_SCHEMA.md)
- [API Documentation](./docs/API.md)
- [Security Guidelines](./docs/SECURITY.md)
- [Deployment Guide](./docs/DEPLOYMENT.md)

## User Roles

- **Student**: Can upload, download, and interact with materials
- **Lecturer**: Can upload materials and moderate discussions
- **Administrator**: Can manage users, courses, and content
- **Super Administrator**: Full system access and control

## Security

This application implements industry-standard security practices:

- Password hashing using bcrypt
- CSRF token protection
- XSS prevention through output encoding
- SQL injection prevention with prepared statements
- File upload validation
- Rate limiting on sensitive endpoints
- Secure session management
- Audit logging for administrative actions

See [Security Guidelines](./docs/SECURITY.md) for more details.

## Performance

- Database query optimization
- Pagination and lazy loading
- Asset minification and compression
- Response caching
- Indexed database columns

## Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## License

Copyright © 2024 LIRA University. All rights reserved.

## Support

For support and inquiries, contact the LU Academic Hub administration team.

## Contributors

- Paddy Pride (Lead Developer)

---

**Status**: Production Ready
**Version**: 1.0.0
**Last Updated**: July 4, 2024
