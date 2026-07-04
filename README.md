# Lu-Academic-Hub

## For Student Academic Support

**Version:** 1.0.0  
**Author:** Paddy-Pride  
**License:** MIT  
**Repository:** https://github.com/Paddy-Pride/Lu-Academic-Hub

---

## 🚀 Quick Start

See [SETUP.md](SETUP.md) for detailed installation instructions.

### Quick Setup (5 minutes)

```bash
# 1. Clone repository
git clone https://github.com/Paddy-Pride/Lu-Academic-Hub.git
cd Lu-Academic-Hub

# 2. Setup database
mysql -u root -p < config/setup.sql

# 3. Configure database
cp .env.example .env
# Edit .env with your database credentials

# 4. Create upload directories
mkdir -p uploads/{papers,materials,temp,quarantine}
chmod -R 755 uploads/

# 5. Access the application
# Open: http://localhost/Lu-Academic-Hub
```

---

## 📋 Features

### Core Features
- ✅ **Past Papers Management** - Upload, search, and download past exam papers
- ✅ **Learning Materials** - Study notes, textbooks, videos, and tutorials
- ✅ **Advanced Search** - Full-text search with multiple filter options
- ✅ **User Ratings** - Rate and review resources with detailed feedback
- ✅ **Bookmarks** - Save favorite resources in custom folders
- ✅ **User Profiles** - Manage student profiles and preferences
- ✅ **Admin Panel** - Approve/reject submissions and manage users
- ✅ **Analytics** - Track downloads, views, and trending content

### Technical Features
- 🔒 **Secure Authentication** - Bcrypt password hashing
- 📁 **File Upload Security** - MIME type validation and malware scanning
- 🔍 **Full-Text Search** - MySQL FULLTEXT index support
- 📊 **Pagination** - Efficient data handling with pagination
- 🗄️ **Database Optimization** - Indexed queries for performance
- 📝 **Audit Logging** - Track all admin actions
- ⚡ **Error Handling** - Comprehensive error logging and reporting

---

## 🏗️ Architecture

```
Lu-Academic-Hub/
├── config/
│   ├── Database.php          # Database connection
│   ├── ErrorHandler.php      # Error handling
│   ├── headers.php           # Security headers
│   ├── config.php            # Configuration
│   └── setup.sql             # Database schema
├── models/
│   ├── User.php              # User model
│   ├── PastPaper.php         # Past papers model
│   ├── LearningMaterial.php  # Materials model
│   └── Search.php            # Search model
├── api/
│   ├── FileUploadHandler.php # File upload
│   ├── RatingReviewHandler.php # Ratings
│   ├── BookmarkHandler.php   # Bookmarks
│   └── AuthHandler.php       # Authentication
├── endpoints/
│   ├── papers.php            # Papers API
│   ├── materials.php         # Materials API
│   ├── search.php            # Search API
│   ├── auth.php              # Auth API
│   └── admin.php             # Admin API
├── helpers/
│   └── Pagination.php        # Pagination
├── bootstrap.php             # Application bootstrap
├── API_DOCUMENTATION.md      # API docs
├── SETUP.md                  # Setup guide
└── README.md                 # This file
```

---

## 🔌 API Endpoints

### Authentication
```
POST   /auth.php?action=register       Register new user
POST   /auth.php?action=login          Login user
POST   /auth.php?action=logout         Logout user
GET    /auth.php?action=profile        Get user profile
POST   /auth.php?action=update-profile Update profile
POST   /auth.php?action=change-password Change password
```

### Past Papers
```
GET    /papers.php?action=list         List all papers
GET    /papers.php?action=detail&id=1  Get paper details
POST   /papers.php?action=create       Upload paper
POST   /papers.php?action=rate         Rate paper
POST   /papers.php?action=bookmark     Bookmark paper
GET    /papers.php?action=trending     Get trending papers
DELETE /papers.php?action=delete&id=1  Delete paper
```

### Learning Materials
```
GET    /materials.php?action=list      List all materials
GET    /materials.php?action=detail&id=1 Get material details
POST   /materials.php?action=create    Upload material
GET    /materials.php?action=tags      Get all tags
GET    /materials.php?action=bytype    Get by type
```

### Search
```
GET    /search.php?action=global       Global search
GET    /search.php?action=advanced     Advanced search
GET    /search.php?action=suggestions  Search suggestions
GET    /search.php?action=trending     Trending searches
GET    /search.php?action=courses      Get courses
```

### Admin
```
GET    /admin.php?action=users         List users
GET    /admin.php?action=pending-papers Pending papers
POST   /admin.php?action=approve-paper Approve paper
GET    /admin.php?action=statistics    Get statistics
```

For complete API documentation, see [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

---

## 📦 Database Schema

**Tables:**
- `users` - User accounts
- `past_papers` - Exam papers
- `learning_materials` - Study materials
- `ratings_reviews` - User ratings
- `download_history` - Download logs
- `bookmarks` - User bookmarks
- `search_history` - Search logs
- `file_uploads_log` - Upload logs
- `notifications` - User notifications
- `admin_logs` - Admin action logs

---

## 🔒 Security Features

- ✅ CSRF protection
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS prevention (output encoding)
- ✅ File upload validation
- ✅ Secure password hashing (bcrypt)
- ✅ Session timeout
- ✅ CORS headers
- ✅ Security headers (X-Frame-Options, X-XSS-Protection, etc.)
- ✅ Rate limiting
- ✅ Audit logging

---

## 🧪 Testing

### Using cURL

**Register:**
```bash
curl -X POST http://localhost/Lu-Academic-Hub/endpoints/auth.php?action=register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@edu.com","password":"password123"}'
```

**Login:**
```bash
curl -X POST http://localhost/Lu-Academic-Hub/endpoints/auth.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@edu.com","password":"password123"}' \
  -c cookies.txt
```

**Search:**
```bash
curl -X GET 'http://localhost/Lu-Academic-Hub/endpoints/search.php?action=global&q=programming'
```

---

## 📊 Performance

- Database queries optimized with indexes
- Full-text search for instant results
- File caching headers
- Pagination for large datasets
- Connection pooling ready

---

## 🚀 Deployment

### Recommended Stack
- **Web Server:** Nginx or Apache
- **PHP:** 7.4+ with FPM
- **Database:** MySQL 5.7+ or MariaDB
- **Cache:** Redis (optional)
- **CDN:** For file serving

See [SETUP.md](SETUP.md) for production deployment guide.

---

## 📝 License

MIT License - See LICENSE file for details

---

## 🤝 Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

---

## 📧 Support

- Email: support@luacademic.edu
- GitHub Issues: https://github.com/Paddy-Pride/Lu-Academic-Hub/issues
- Documentation: [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

---

**Happy Learning! 📚**
