# Production Deployment Runbook

This guide provides step-by-step instructions for deploying the Finzo Enterprise Banking System into a secure, high-availability production environment.

---

## 1. System Requirements

### 1.1 Operating System & Runtime
- **Operating System:** Linux (Ubuntu 22.04 LTS / 24.04 LTS or Debian 12 recommended).
- **PHP Version:** PHP 8.4 or higher.
- **Required PHP Extensions:**
  - `pdo_mysql` (Database interaction)
  - `openssl` (AES-256 encryption & TLS)
  - `mbstring` (Multibyte string handling)
  - `fileinfo` (MIME-type verification on uploads)
  - `curl` (External service integrations)
  - `gd` or `imagick` (Profile photo processing)
  - `sodium` (High-grade cryptographic operations)
- **Database Engine:** MySQL 8.0+ (or MariaDB 10.11+) with InnoDB engine.
- **Package Manager:** Composer 2.6+.
- **Web Server:** Nginx 1.24+ or Apache 2.4+ with `mod_rewrite` and `mod_headers`.

---

## 2. Server Provisioning & Installation

### 2.1 Clone Repository & Install Dependencies
```bash
cd /var/www
git clone https://github.com/your-org/Finzo.git finzo
cd /var/www/finzo

# Install optimized production dependencies
composer install --no-dev --optimize-autoloader
```

### 2.2 Environment Configuration
Copy the sample environment file and generate a high-entropy application key:

```bash
cp .env.example .env
```

Edit `/var/www/finzo/.env` with production parameters:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_32_BYTE_HIGH_ENTROPY_KEY_HERE
APP_URL=https://banking.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finzo_production
DB_USERNAME=finzo_app_user
DB_PASSWORD=YOUR_STRONG_DB_PASSWORD

MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@yourdomain.com
MAIL_PASSWORD=YOUR_SMTP_PASSWORD
MAIL_FROM_ADDRESS=no-reply@yourdomain.com
MAIL_FROM_NAME="Finzo Enterprise Banking"
```

### 2.3 Database Initialization & Seeding
Ensure the MySQL user has table creation and trigger permissions, then execute:

```bash
# Run all database schema migrations
php bin/migrate

# Seed baseline roles, permissions, and initial chart of accounts
php bin/seed
```

### 2.4 Directory Permissions
Grant read/write permissions to the web server user (`www-data`):

```bash
sudo chown -R www-data:www-data /var/www/finzo
sudo chmod -R 755 /var/www/finzo
sudo chmod -R 775 /var/www/finzo/storage /var/www/finzo/public/uploads
```

---

## 3. Web Server Configurations

### 3.1 Nginx Production Virtual Host (`/etc/nginx/sites-available/finzo.conf`)

```nginx
server {
    listen 80;
    server_name banking.yourdomain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name banking.yourdomain.com;

    root /var/www/finzo/public;
    index index.php index.html;

    # SSL Certificates
    ssl_certificate /etc/letsencrypt/live/banking.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/banking.yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com;" always;

    # Prevent direct access to hidden files and sensitive storage
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Block PHP execution in upload directories
    location ~* /(uploads|storage)/.*\.php$ {
        deny all;
        return 404;
    }

    # API and Frontend Routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 3.2 Apache Configuration (`.htaccess`)
Ensure Apache's `mod_rewrite` and `mod_headers` are enabled. The repository includes an enterprise `.htaccess` in `public/`:

```bash
sudo a2enmod rewrite headers ssl
sudo systemctl restart apache2
```

---

## 4. PHP 8.4 Production Tuning (`php.ini`)

Ensure the following directives are active in `/etc/php/8.4/fpm/php.ini`:

```ini
expose_php = Off
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php/error.log

memory_limit = 256M
post_max_size = 10M
upload_max_filesize = 5M
max_execution_time = 30

# Session Hardening
session.use_strict_mode = 1
session.cookie_httponly = 1
session.cookie_secure = 1
session.cookie_samesite = Strict
session.gc_maxlifetime = 1800

# OPcache Production Acceleration
opcache.enable = 1
opcache.enable_cli = 0
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0
```

---

## 5. Maintenance & Cron Scheduling

Add a cron entry (`sudo crontab -u www-data -e`) to execute background maintenance tasks:

```cron
# Run scheduled system cleanups every hour
0 * * * * php /var/www/finzo/bin/cleanup >> /var/www/finzo/storage/logs/cron.log 2>&1
```

---

## 6. Scaling & High-Availability
For installations anticipating high transactional volume, refer to the [Redis Upgrade Guide](REDIS_UPGRADE.md) to decouple cache and queues into memory.
