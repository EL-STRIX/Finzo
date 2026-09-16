# 🚀 Finzo Enterprise Banking — Complete Local Setup & Installation Guide

Welcome to the **Finzo Enterprise Banking Platform** repository! Whether you are a developer looking to contribute, a reviewer evaluating the architecture, or a student studying production-grade PHP systems, this guide walks you through setting up and running the entire Finzo banking ecosystem on your local machine from start to finish.

---

## 📑 Table of Contents
1. [System Requirements & Prerequisites](#1-system-requirements--prerequisites)
2. [Quick Start (5-Minute Setup)](#2-quick-start-5-minute-setup)
3. [Step-by-Step Installation Walkthrough](#3-step-by-step-installation-walkthrough)
   - [Step 1: Clone the Repository](#step-1-clone-the-repository)
   - [Step 2: Install Composer Dependencies](#step-2-install-composer-dependencies)
   - [Step 3: Configure Environment Variables (.env)](#step-3-configure-environment-variables-env)
   - [Step 4: Database Setup & Migration](#step-4-database-setup--migration)
   - [Step 5: Verify Storage & Directory Permissions](#step-5-verify-storage--directory-permissions)
4. [Default Seeded Login Credentials](#4-default-seeded-login-credentials)
5. [Running the Web Server](#5-running-the-web-server)
   - [Option A: Using XAMPP / WampServer / Laragon (Recommended)](#option-a-using-xampp--wampserver--laragon-recommended)
   - [Option B: Using PHP Built-in Server](#option-b-using-php-built-in-server)
   - [Option C: Using Nginx / Apache Virtual Host](#option-c-using-nginx--apache-virtual-host)
6. [Testing & Quality Assurance](#6-testing--quality-assurance)
7. [Finzo CLI Utility Reference (bin/finzo)](#7-finzo-cli-utility-reference-binfinzo)
8. [Troubleshooting & Frequently Asked Questions (FAQ)](#8-troubleshooting--frequently-asked-questions-faq)
9. [Project Architecture Overview](#9-project-architecture-overview)

---

## 1. System Requirements & Prerequisites

Before starting, ensure your local development machine has the following installed:

| Tool / Technology | Minimum Version | Recommended | Notes |
| :--- | :--- | :--- | :--- |
| **PHP** | `8.2.0+` | `PHP 8.4+` | Requires extensions: `pdo`, `pdo_mysql`, `openssl`, `mbstring`, `curl`, `fileinfo`, `zip` |
| **MySQL / MariaDB** | `8.0+` / `10.4+` | `MySQL 8.0` | UTF-8 (`utf8mb4_unicode_ci`) character set |
| **Composer** | `2.0+` | `Composer 2.7+` | Dependency manager for testing and CLI autoloading |
| **Web Server** | Apache 2.4+ / Nginx | Apache (with `mod_rewrite`) | Enabled in XAMPP / Laragon by default |
| **Git** | `2.x+` | Latest | For cloning and version control |

> [!TIP]
> If you are on **Windows**, using **[XAMPP](https://www.apachefriends.org/)** or **[Laragon](https://laragon.org/)** provides PHP, Apache, and MySQL in a single convenient installer.

---

## 2. Quick Start (5-Minute Setup)

For experienced PHP developers using XAMPP:

```bash
# 1. Navigate to your web server document root
cd C:\xampp\htdocs

# 2. Clone the repository
git clone https://github.com/EL-STRIX/Finzo.git
cd Finzo

# 3. Install dependencies
composer install

# 4. Copy environment configuration
cp .env.example .env

# 5. Run database migrations & seed default data
php bin/finzo migrate
php bin/finzo seed system
php bin/finzo seed dev

# 6. Start Apache and MySQL in XAMPP, then open:
# http://localhost/Finzo/public
```

---

## 3. Step-by-Step Installation Walkthrough

### Step 1: Clone the Repository

Clone the project directly into your web server's root folder:

- **Windows (XAMPP):** `C:\xampp\htdocs\Finzo`
- **Windows (Laragon):** `C:\laragon\www\Finzo`
- **macOS / Linux:** `/var/www/html/Finzo` or your local development directory

Open your terminal or PowerShell and run:

```bash
git clone https://github.com/EL-STRIX/Finzo.git
cd Finzo
```

---

### Step 2: Install Composer Dependencies

Finzo uses Composer for PHPUnit, PHPStan, PHP_CodeSniffer, and PSR-4 test autoloading:

```bash
composer install
```

> [!NOTE]
> Finzo's core runtime engine is built with clean native PHP and has zero mandatory runtime vendor bloat, making it exceptionally fast and lightweight.

---

### Step 3: Configure Environment Variables (`.env`)

Create your local environment file by copying the template:

```bash
# On Windows PowerShell / Command Prompt
copy .env.example .env

# On Linux / macOS
cp .env.example .env
```

Open `.env` in your text editor (e.g., VS Code) and customize your configuration:

```ini
APP_NAME=Finzo
APP_ENV=local
APP_KEY=vHrcbyozkC54pKQk+uHs+vxYDRuhAJeyubfwgO9taTc=
APP_DEBUG=true
APP_URL=http://localhost/Finzo

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=finzo_db
DB_USER=root
DB_PASS=

# Session & Cache Drivers (file or redis)
SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

# Mail Configuration (Mailtrap or standard SMTP)
SMTP_MAILER=smtp
SMTP_HOST=smtp.mailtrap.io
SMTP_PORT=2525
SMTP_USER=your_username
SMTP_PASS=your_password
SMTP_ENCRYPTION=tls
SMTP_FROM=noreply@finzo.com
SMTP_FROM_NAME="Finzo Banking"
```

> [!IMPORTANT]
> - If your local MySQL has a root password, set `DB_PASS=your_password`.
> - If using XAMPP default settings, leave `DB_PASS=` empty and `DB_USER=root`.
> - If running on a subpath, make sure `APP_URL=http://localhost/Finzo` matches your URL structure.

---

### Step 4: Database Setup & Migration

Ensure your MySQL server is running (e.g., click **Start** next to MySQL in the XAMPP Control Panel).

You can provision and seed the database using **Option A (CLI)** or **Option B (GUI)**:

#### Option A: Automated via Finzo CLI (Recommended)
Run the built-in database migrator and seeders:

```bash
# 1. Run all pending database migrations (creates all tables, indexes, constraints)
php bin/finzo migrate

# 2. Inject core system configuration and Super Admin
php bin/finzo seed system

# 3. (Optional) Inject full demo data (customers, accounts, branches, transactions, staff)
php bin/finzo seed dev
```

#### Option B: Manual via phpMyAdmin
1. Open `http://localhost/phpmyadmin` in your browser.
2. Click **New** and create a database named `finzo_db` with collation `utf8mb4_unicode_ci`.
3. Use the CLI `php bin/finzo migrate` to populate tables automatically.

---

### Step 5: Verify Storage & Directory Permissions

Finzo requires write permissions for session storage, logs, cached templates, and file uploads (such as KYC identity documents and profile pictures).

Make sure the following directories exist and are writable:

```bash
# On Linux / macOS:
chmod -R 775 storage public/uploads
chown -R www-data:www-data storage public/uploads
```

On Windows, directories inside `C:\xampp\htdocs\Finzo` are typically writable by default. If any folder is missing, Finzo automatically creates it upon initialization.

---

## 4. Default Seeded Login Credentials

Once seeded with `system` and `dev` seeds, the following accounts are ready to use immediately:

### 👑 1. Super Administrator Portal
- **Login URL:** `http://localhost/Finzo/public/views/auth/admin-login.html`
- **Email:** `admin@finzo.com`
- **Password:** `Admin@123`
- **Role:** Super Admin (Full access to system KPIs, branch creation, staff management, KYC approval queue, audit logs, and BCDR backup manager)

### 🏢 2. Branch Staff Operations Portal
- **Login URL:** `http://localhost/Finzo/public/views/auth/staff-login.html`
- **Staff Email:** `rahul.sharma1@finzo.com` (or `STF001`)
- **Password:** `Staff@123`
- **Role:** Branch Teller / Staff (Cash deposits, cash withdrawals, customer KYC verification, branch ledger)

### 👤 3. Customer Banking Dashboard
- **Login URL:** `http://localhost/Finzo/public/views/auth/login.html`
- **Customer Email:** `rahul.sharma1@example.com`
- **Password:** `User@123`
- **Default 4-Digit Transaction PIN:** `1234`
- **Role:** Active Banking Customer (Fund transfers, transaction history, balance unmasking, beneficiary management, profile KYC)

---

## 5. Running the Web Server

### Option A: Using XAMPP / WampServer / Laragon (Recommended)
1. Start **Apache** and **MySQL** from your control panel.
2. Ensure `mod_rewrite` is enabled in Apache's `httpd.conf` (enabled by default in XAMPP/Laragon).
3. Open your browser and navigate to:
   ```text
   http://localhost/Finzo/public/
   ```

### Option B: Using PHP Built-in Server
If you prefer running a quick development server without Apache:

```bash
# From the project root (Finzo/):
php -S localhost:8000 -t public
```
Then visit: `http://localhost:8000` in your browser.

> [!NOTE]
> When using `php -S`, update your `.env` to set: `APP_URL=http://localhost:8000`

### Option C: Using Nginx Virtual Host
If deploying on Nginx, point your `root` directive to the `/public` directory:

```nginx
server {
    listen 80;
    server_name finzo.local;
    root /var/www/html/Finzo/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

---

## 6. Testing & Quality Assurance

Finzo includes a robust automated testing suite with unit, integration, security, and banking atomicity test suites.

### Running PHPUnit Tests
Execute all 196+ automated tests:

```bash
# Windows
vendor\bin\phpunit

# Linux / macOS
./vendor/bin/phpunit
```

### Checking PSR-12 Code Standards
Run PHP_CodeSniffer to ensure all PHP code complies with PSR-12:

```bash
vendor/bin/phpcs --standard=phpcs.xml app/ tests/
```

### Running Static Analysis
Check types, methods, and parameters using PHPStan:

```bash
vendor/bin/phpstan analyse
```

### Checking Code Coverage
Run the automated coverage verification script:

```bash
php bin/check-coverage.php
```

---

## 7. Finzo CLI Utility Reference (`bin/finzo`)

Finzo comes with an interactive command-line interface for maintenance and database management:

| Command | Description |
| :--- | :--- |
| `php bin/finzo migrate` | Runs all pending database migrations in chronological order. |
| `php bin/finzo seed system` | Seeds system settings, base branches, and default Super Admin. |
| `php bin/finzo seed dev` | Seeds full mock data (30+ customers, accounts, transactions, staff). |
| `php bin/finzo bcdr:backup full` | Creates an AES-256 encrypted snapshot backup of the database in `storage/backups/`. |
| `php bin/finzo bcdr:backup schema` | Creates an encrypted schema-only backup. |
| `php bin/finzo bcdr:restore <file.zip>` | Restores the database from an encrypted backup archive. |
| `php bin/finzo bcdr:validate` | Validates double-entry ledger balance, audit trail integrity, and user states. |
| `php bin/finzo cache:clear` | Purges file and memory caches in `storage/cache/`. |

---

## 8. Troubleshooting & Frequently Asked Questions (FAQ)

### Q: "No connection could be made because the target machine actively refused it (SQLSTATE[HY000] [2002])"
- **Cause:** MySQL server is not running or is running on a non-standard port.
- **Solution:** Open your XAMPP/Laragon control panel and start MySQL. Confirm in `.env` that `DB_HOST=127.0.0.1` and `DB_PORT=3306`.

### Q: "404 Not Found" when clicking on links or calling API routes
- **Cause:** Apache URL rewriting (`mod_rewrite`) is disabled or `.htaccess` is ignored.
- **Solution:** 
  1. Open Apache configuration (`httpd.conf`).
  2. Ensure `LoadModule rewrite_module modules/mod_rewrite.so` is uncommented.
  3. Ensure `AllowOverride All` is set on your document root directory.
  4. Restart Apache.

### Q: "Invalid transaction PIN" during transfer
- **Cause:** The default transaction PIN is separate from the login password.
- **Solution:** The default seeded transaction PIN for all test users is **`1234`**. You can also change or reset it in the user security settings.

### Q: White screen or HTTP 500 Internal Server Error
- **Cause:** An uncaught exception occurred while `APP_DEBUG=false`.
- **Solution:** 
  1. Set `APP_DEBUG=true` in your `.env` file to view detailed stack traces.
  2. Inspect the latest error logs in `storage/logs/finzo.log`.

### Q: File upload fails (KYC Document or Profile Picture)
- **Cause:** PHP upload limits or folder permission restrictions.
- **Solution:** Verify in `php.ini` that `file_uploads = On`, `upload_max_filesize = 10M`, and `post_max_size = 12M`. Ensure `public/uploads/` is writable.

---

## 9. Project Architecture Overview

Finzo strictly follows the **Clean Architecture** pattern to isolate presentation, application business logic, and infrastructure:

```text
Finzo/
├── app/
│   ├── Application/          # Business logic (Services, Actions, DTOs, Jobs, Listeners)
│   ├── Domain/               # Core domain entities, Enums, Contracts/Interfaces
│   ├── Infrastructure/       # Database repositories, Query stores, Storage adapters
│   ├── Presentation/         # HTTP Controllers, Middlewares, Validators
│   └── Shared/               # Core framework (Router, Container, Security, RateLimiter, Cache)
├── bin/                      # CLI tools (bin/finzo, bin/check-coverage.php)
├── bootstrap/                # Application initialization and container binding
├── config/                   # Configuration files (config.php, mailer.php, routes)
├── database/                 # Migrations, seeders, and BCDR backup manager
├── docs/                     # Architectural documentation, API specs, and guides
├── public/                   # Web root (index.php, CSS, JavaScript, Views/Templates)
├── routes/                   # API endpoint route definitions (routes/api.php)
├── storage/                  # Logs, sessions, backups, and framework cache
├── tests/                    # Unit, Integration, Security, and Banking test suites
├── .env.example              # Environment variables template
├── composer.json             # Autoloading & dev dependencies
├── phpcs.xml                 # PSR-12 coding standard ruleset
├── phpstan.neon              # Static analysis configuration
└── phpunit.xml               # PHPUnit test runner configuration
```

---

## 💡 Need Help or Found an Issue?

If you encounter any issues during setup:
1. Double-check the [Troubleshooting](#8-troubleshooting--frequently-asked-questions-faq) section above.
2. Check existing documentation in the [`docs/`](docs/) directory.
3. Open an issue on GitHub or submit a pull request!

Happy coding! 🎉
