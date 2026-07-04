# Setup Instructions

## Prerequisites

- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx with mod_rewrite enabled
- Composer (optional, for dependency management)

## Installation Steps

### 1. Clone Repository
```bash
git clone https://github.com/Paddy-Pride/Lu-Academic-Hub.git
cd Lu-Academic-Hub
```

### 2. Database Setup

#### Create Database
```bash
mysql -u root -p < config/setup.sql
```

Or manually:
1. Open phpMyAdmin
2. Create database: `lu_academic_hub`
3. Import `config/setup.sql`

### 3. Environment Configuration

Create `.env` file in root directory:

```env
# Database
DB_HOST=localhost
DB_NAME=lu_academic_hub
DB_USER=root
DB_PASSWORD=your_password

# Application
DEBUG=false
TIMEZONE=UTC

# Email
FROM_EMAIL=noreply@luacademic.edu
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASSWORD=your_app_password

# Security
CORS_ORIGIN=*
```

### 4. Update Configuration

Edit `config/Database.php` with your database credentials:

```php
private $host = 'localhost';
private $db_name = 'lu_academic_hub';
private $user = 'root';
private $pass = 'your_password';
```

### 5. Create Upload Directories

```bash
mkdir -p uploads/{papers,materials,temp,quarantine}
chmod -R 755 uploads/
mkdir -p logs
chmod 755 logs/
```

### 6. Set File Permissions

```bash
chmod 644 config/Database.php
chmod 644 config/config.php
chmod 755 config/
chmod 755 endpoints/
chmod 755 models/
chmod 755 api/
```

### 7. Verify Installation

Access in browser:
```
http://localhost/Lu-Academic-Hub/endpoints/auth.php?action=verify-session
```

## API Testing

### Using cURL

#### Register
```bash
curl -X POST http://localhost/Lu-Academic-Hub/endpoints/auth.php?action=register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@edu.com","password":"password123","first_name":"John","last_name":"Doe"}'
```

#### Login
```bash
curl -X POST http://localhost/Lu-Academic-Hub/endpoints/auth.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@edu.com","password":"password123"}' \
  -c cookies.txt
```

#### Search
```bash
curl -X GET 'http://localhost/Lu-Academic-Hub/endpoints/search.php?action=global&q=programming' \
  -b cookies.txt
```

### Using Postman

1. Import API endpoints
2. Set base URL: `http://localhost/Lu-Academic-Hub/endpoints/`
3. Set environment variables
4. Test each endpoint

## Troubleshooting

### Database Connection Error

1. Check MySQL is running
2. Verify credentials in `config/Database.php`
3. Check database exists: `SHOW DATABASES;`

### File Upload Issues

1. Check folder permissions: `chmod 755 uploads/`
2. Check PHP upload limits in `php.ini`:
   ```
   post_max_size = 50M
   upload_max_filesize = 50M
   ```

### Session Issues

1. Check `php.ini` session settings
2. Verify session save path exists and is writable
3. Clear session cookies in browser

### Permission Denied

1. Run: `chmod -R 755 /path/to/Lu-Academic-Hub`
2. Ensure web server user owns files: `chown -R www-data:www-data /path/to/Lu-Academic-Hub`

## Security Checklist

- [ ] Change default database password
- [ ] Set `DEBUG=false` in production
- [ ] Enable HTTPS (set `secure: true` in config)
- [ ] Implement rate limiting
- [ ] Set strong CORS origins
- [ ] Regular database backups
- [ ] Monitor upload directory for suspicious files
- [ ] Update dependencies regularly

## Deployment

### Production Checklist

1. Use HTTPS only
2. Set strong database password
3. Use environment variables for secrets
4. Enable caching headers
5. Set up automated backups
6. Configure error logging
7. Monitor server resources
8. Set up CDN for file serving

### Nginx Configuration

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    root /var/www/Lu-Academic-Hub;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /uploads/ {
        expires 7d;
        add_header Cache-Control "public, immutable";
    }
}
```

## Support

For issues or questions:
- Email: support@luacademic.edu
- GitHub Issues: https://github.com/Paddy-Pride/Lu-Academic-Hub/issues
