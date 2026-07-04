# Installation Guide - LU Academic Hub

## Prerequisites

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server with mod_rewrite enabled
- Composer (optional, for dependency management)
- Git (for version control)

## Step 1: Download/Clone the Repository

```bash
git clone https://github.com/Paddy-Pride/lu-academic-hub.git
cd lu-academic-hub
```

## Step 2: Set Up Directory Permissions

Ensure proper permissions for upload and log directories:

```bash
chmod 755 uploads/
chmod 755 uploads/papers/
chmod 755 uploads/research/
chmod 755 uploads/profile/
chmod 755 logs/
chmod 755 backups/
chmod 755 cache/
```

## Step 3: Configure Database

### Create Database

```sql
CREATE DATABASE lu_academic_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Create Database User

```sql
CREATE USER 'lu_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON lu_academic_hub.* TO 'lu_user'@'localhost';
FLUSH PRIVILEGES;
```

### Import Database Schema

```bash
mysql -u lu_user -p lu_academic_hub < database/schema.sql
mysql -u lu_user -p lu_academic_hub < database/seed.sql
```

## Step 4: Configure Application

### Copy Environment File

```bash
cp config/.env.example config/.env
```

### Edit Configuration

Update `config/.env` with your settings:

```env
# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=lu_academic_hub
DB_USER=lu_user
DB_PASS=strong_password_here

# Application
APP_NAME="LU Academic Hub"
APP_URL=http://localhost
APP_ENV=production
APP_DEBUG=false

# Email/SMTP
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password
SMTP_FROM=noreply@lu-academic-hub.com

# Security
SESSION_TIMEOUT=1800
MAX_LOGIN_ATTEMPTS=5
LOCK_TIMEOUT=900
FILE_UPLOAD_MAX=52428800
```

## Step 5: Configure Web Server

### Apache (.htaccess)

Ensure `.htaccess` in root directory:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
</IfModule>
```

### Nginx

Update server block:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/lu-academic-hub;

    location / {
        try_files $uri $uri/ /index.php?url=$uri&$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## Step 6: Set File Permissions

```bash
chown -R www-data:www-data /path/to/lu-academic-hub
chmod -R 755 /path/to/lu-academic-hub
chmod -R 775 uploads/ logs/ backups/ cache/
```

## Step 7: Create Super Admin User

Run the initialization script:

```bash
php cli/setup.php
```

Follow the prompts to create the first super admin account.

## Step 8: Verify Installation

1. Open your browser and navigate to `http://localhost` (or your domain)
2. You should see the LU Academic Hub homepage
3. Log in with your super admin credentials
4. Navigate to Admin Dashboard to verify all systems are working

## Step 9: Post-Installation

### Enable HTTPS

- Obtain SSL certificate (Let's Encrypt recommended)
- Update `config/.env` with HTTPS URL
- Redirect HTTP to HTTPS in web server config

### Configure Cron Jobs

Add to crontab for automatic maintenance:

```bash
# Backup database daily at 2 AM
0 2 * * * php /path/to/cli/backup.php

# Clean old logs weekly
0 3 * * 0 php /path/to/cli/clean-logs.php

# Send pending notifications every 5 minutes
*/5 * * * * php /path/to/cli/send-notifications.php
```

### Disable Debug Mode in Production

Ensure `config/.env` has:

```env
APP_ENV=production
APP_DEBUG=false
```

## Troubleshooting

### Database Connection Error

- Verify MySQL is running
- Check database credentials in `config/.env`
- Ensure user has proper privileges

### File Upload Issues

- Check directory permissions (must be 775)
- Verify `upload_max_filesize` and `post_max_size` in php.ini
- Check available disk space

### Session Issues

- Verify `/tmp` directory is writable
- Check PHP session configuration
- Clear browser cookies and try again

## Next Steps

1. Review [Configuration Guide](./CONFIGURATION.md)
2. Read [Security Guidelines](./SECURITY.md)
3. Set up SSL/HTTPS
4. Configure email notifications
5. Import course and faculty data
6. Begin user onboarding

## Support

For installation issues, contact: support@lu-academic-hub.com
