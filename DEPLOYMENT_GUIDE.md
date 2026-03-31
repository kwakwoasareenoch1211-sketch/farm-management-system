# Farm Management System - Deployment Guide

## System Status: ✓ READY FOR DEPLOYMENT

All systems have been verified and are operational. This guide will help you deploy the application to production.

---

## Pre-Deployment Checklist

### ✓ System Verification Complete
- [x] Database connectivity working
- [x] All 17 critical tables exist
- [x] Expense-Liability integration functional
- [x] Expense totals accurate (GHS 3,283.00)
- [x] Financial calculations working
- [x] Batch tracking operational
- [x] All critical files present
- [x] All model methods functional

### Current System State
```
Capital: GHS 3,000.00
Revenue: GHS 0.00
Expenses: GHS 3,283.00
Profit/Loss: GHS -3,283.00
Liabilities: GHS 578.00
Net Worth: GHS -861.00
```

---

## Deployment Steps

### 1. Prepare Production Environment

#### Server Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.3+
- Apache/Nginx web server
- mod_rewrite enabled (Apache)
- PDO MySQL extension
- Minimum 512MB RAM
- 100MB disk space

#### Recommended Server Setup
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install LAMP stack
sudo apt install apache2 mysql-server php php-mysql php-pdo -y

# Enable mod_rewrite
sudo a2enmod rewrite

# Restart Apache
sudo systemctl restart apache2
```

### 2. Database Setup

#### Create Production Database
```sql
CREATE DATABASE farmapp_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'farmapp_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON farmapp_production.* TO 'farmapp_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Import Database Schema
```bash
mysql -u farmapp_user -p farmapp_production < database/rebuild_complete.sql
```

### 3. Upload Application Files

#### Files to Upload
```
farmapp/
├── app/
│   ├── config/
│   ├── controllers/
│   ├── core/
│   ├── models/
│   └── views/
├── public/ (or assets/)
├── .htaccess
└── index.php
```

#### Files to EXCLUDE (do not upload)
```
- *.md (documentation files)
- *.php (test/verification scripts in root)
- .git/
- .kiro/
- check_*.php
- test_*.php
- verify_*.php
- diagnose_*.php
- fix_*.php
- deploy.php
```

### 4. Configure Application

#### Update app/config/Config.php
```php
<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'farmapp_production');
define('DB_USER', 'farmapp_user');
define('DB_PASS', 'YOUR_STRONG_PASSWORD');

// Application Configuration
define('BASE_URL', 'https://yourdomain.com');  // Update this!
define('BASE_PATH', __DIR__ . '/../../');

// Environment
define('APP_ENV', 'production');  // Change from 'development'
define('DEBUG_MODE', false);       // Disable debug mode
```

### 5. Set File Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/html/farmapp

# Set directory permissions
find /var/www/html/farmapp -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/html/farmapp -type f -exec chmod 644 {} \;

# Protect config files
chmod 640 /var/www/html/farmapp/app/config/*.php
```

### 6. Configure Web Server

#### Apache (.htaccess already included)
Ensure your VirtualHost has:
```apache
<Directory /var/www/html/farmapp>
    AllowOverride All
    Require all granted
</Directory>
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html/farmapp;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 7. SSL Certificate (Recommended)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Get SSL certificate
sudo certbot --apache -d yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

### 8. Create Admin User

Access the application and create your first admin user:
```
URL: https://yourdomain.com/users/create
```

Or via SQL:
```sql
INSERT INTO users (username, email, password, role, status, created_at)
VALUES (
    'admin',
    'admin@yourdomain.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- password: password
    'admin',
    'active',
    NOW()
);
```

**IMPORTANT:** Change the password immediately after first login!

### 9. Post-Deployment Verification

Run these checks after deployment:

#### Check Database Connection
```bash
php -r "require 'app/config/Config.php'; require 'app/config/Database.php'; \$db = (new Database())->connect(); echo 'Connected successfully';"
```

#### Verify Application Access
- Visit: https://yourdomain.com
- Login page should load
- No PHP errors displayed

#### Test Core Functions
1. Login with admin account
2. Navigate to Financial Dashboard
3. Check Expenses page
4. Check Liabilities page
5. Create a test expense
6. Verify it appears in dashboard

---

## Security Hardening

### 1. Disable Directory Listing
Add to .htaccess:
```apache
Options -Indexes
```

### 2. Hide PHP Version
In php.ini:
```ini
expose_php = Off
```

### 3. Secure Session Configuration
In php.ini:
```ini
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

### 4. Enable HTTPS Only
In .htaccess:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 5. Protect Sensitive Files
```apache
<FilesMatch "^(Config\.php|Database\.php)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

## Backup Strategy

### Database Backup (Daily)
```bash
#!/bin/bash
# /usr/local/bin/backup-farmapp.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/farmapp"
DB_NAME="farmapp_production"
DB_USER="farmapp_user"
DB_PASS="YOUR_PASSWORD"

mkdir -p $BACKUP_DIR

mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/farmapp_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -name "farmapp_*.sql.gz" -mtime +30 -delete
```

Add to crontab:
```bash
0 2 * * * /usr/local/bin/backup-farmapp.sh
```

### File Backup (Weekly)
```bash
tar -czf /backups/farmapp_files_$(date +%Y%m%d).tar.gz /var/www/html/farmapp
```

---

## Monitoring & Maintenance

### Log Files to Monitor
```
/var/log/apache2/error.log
/var/log/apache2/access.log
/var/log/mysql/error.log
```

### Regular Maintenance Tasks
- [ ] Weekly: Review error logs
- [ ] Weekly: Check disk space
- [ ] Monthly: Update PHP/MySQL
- [ ] Monthly: Review user accounts
- [ ] Quarterly: Security audit
- [ ] Quarterly: Performance optimization

---

## Troubleshooting

### Issue: White Screen / 500 Error
**Solution:**
1. Check Apache error log
2. Verify file permissions
3. Check PHP error_log
4. Enable display_errors temporarily

### Issue: Database Connection Failed
**Solution:**
1. Verify credentials in Config.php
2. Check MySQL service status
3. Verify user permissions
4. Check firewall rules

### Issue: Routes Not Working
**Solution:**
1. Verify mod_rewrite enabled
2. Check .htaccess file exists
3. Verify AllowOverride All in VirtualHost
4. Clear browser cache

---

## Performance Optimization

### Enable OPcache
In php.ini:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### MySQL Optimization
```sql
-- Add indexes for frequently queried columns
ALTER TABLE expenses ADD INDEX idx_payment_status (payment_status);
ALTER TABLE expenses ADD INDEX idx_expense_date (expense_date);
ALTER TABLE liabilities ADD INDEX idx_status (status);
ALTER TABLE animal_batches ADD INDEX idx_status (status);
```

### Enable Gzip Compression
In .htaccess:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

---

## Support & Documentation

### System Documentation
- Financial calculations: See `FINANCIAL_QUICK_REFERENCE.md`
- Expense tracking: See `EXPENSE_TOTALS_FIXED.md`
- Liability management: See `LIABILITY_COMPUTATION_FIXED.md`

### Key Features
1. **Expense Tracking**: Manual, Feed, Medication, Vaccination, Livestock Purchase, Mortality Loss
2. **Liability Management**: Automatic creation from unpaid expenses
3. **Financial Dashboard**: Real-time calculations with full traceability
4. **Batch Management**: Track poultry batches with mortality recording
5. **Capital & Investment Tracking**: Monitor owner equity and investments

### Contact
For technical support or questions about the system, refer to the documentation files or contact your system administrator.

---

## Deployment Completion Checklist

- [ ] Production server configured
- [ ] Database created and imported
- [ ] Application files uploaded
- [ ] Config.php updated with production settings
- [ ] File permissions set correctly
- [ ] Web server configured
- [ ] SSL certificate installed
- [ ] Admin user created
- [ ] Post-deployment verification completed
- [ ] Backup system configured
- [ ] Monitoring setup
- [ ] Security hardening applied
- [ ] Performance optimization enabled
- [ ] Documentation reviewed

---

**System Version:** 1.0.0  
**Last Updated:** March 31, 2026  
**Status:** Production Ready ✓
