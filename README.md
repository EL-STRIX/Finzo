# 🏦 Finzo — Enterprise Banking & Financial Management Platform

<div align="center">

[![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![Database](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Architecture](https://img.shields.io/badge/Architecture-Clean%20Architecture%20%2F%20DDD-00C7B7?style=for-the-badge)](docs/ARCHITECTURE.md)
[![Tests](https://img.shields.io/badge/Tests-196%20Passed%20(100%25)-success?style=for-the-badge&logo=phpunit&logoColor=white)](tests/)
[![Coding Standard](https://img.shields.io/badge/PSR--12-Compliant-blue?style=for-the-badge)](phpcs.xml)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

<p align="center">
  <strong>A modern, high-performance, and security-first enterprise banking management platform.</strong><br>
  Built with native PHP 8.4, MySQL 8.0, and vanilla JavaScript following Clean Architecture principles — delivering full core-banking workflows without the overhead of heavy enterprise frameworks.
</p>

[**🚀 Quick Setup Guide**](SETUP_GUIDE.md) • [**📚 Documentation Hub**](docs/INDEX.md) • [**🏛️ Architecture Overview**](docs/ARCHITECTURE.md) • [**🔐 Security & Compliance**](docs/security/README.md) • [**🧪 Testing Suite**](tests/)

</div>

---

## 🌟 Executive Summary

**Finzo** is a full-featured core banking and financial operations platform engineered to digitize and unify banking management. From day-to-day retail customer transactions to branch teller operations and executive compliance oversight, Finzo provides an end-to-end, ACID-compliant financial ecosystem.

Designed from the ground up on **Clean Architecture (Domain-Driven Design concepts)**, Finzo completely decouples presentation, business services, domain models, and infrastructure repositories. This ensures that every ledger transaction, KYC application, and role-based action is strictly validated, secure by default, and covered by automated regression tests.

---

## 💎 Core Highlights

- **🏛️ Clean Architecture & DDD:** Strict separation of concerns across Presentation (Controllers & Middleware), Application (Actions & Services), Domain (Entities & Enums), and Infrastructure (Repositories & Adapters).
- **🔒 Enterprise Security Posture:** OWASP ASVS Top-10 defensive mitigations, Bcrypt password hashing, AES-256 encrypted PII storage, session hijacking protection, CSRF token validation, and IP-aware rate limiting.
- **⚡ Zero Framework Lock-In:** Built on lightweight, native PHP 8.4 and Vanilla ES6+ JavaScript. Ultra-fast page loads, sub-millisecond API responses, and minimal server overhead.
- **💼 Double-Entry Ledger & ACID Safety:** Every monetary transfer, deposit, and withdrawal is wrapped in atomic database transactions (`BEGIN`, `COMMIT`, `ROLLBACK`) with idempotency locks.
- **🛡️ Automated BCDR & Encryption at Rest:** Integrated Disaster Recovery manager featuring AES-256 encrypted database backup creation, point-in-time restoration, and ledger balance integrity audits.
- **🧪 100% Automated Test Coverage:** 196+ unit, integration, and security tests verified via PHPUnit with zero failures.

---

## 👥 Multi-Role Portal Experiences

Finzo delivers purpose-built, dedicated portals for the three core stakeholders of a financial institution:

### 1. 👤 Retail Customer Banking Portal
*Designed for seamless, self-service personal wealth management.*
- **Live Account Overview:** Real-time balance tracking, account details, and localized currency formatting.
- **Privacy-First Balance Obfuscation:** One-click balance masking in public spaces, unlocked only via 4-digit Security PIN.
- **Instant Intra-Bank Transfers:** ACID-safe money transfers with instant beneficiary name resolution and duplicate-prevention locks.
- **Beneficiary Address Book:** Speed-dial favorite payees, manage beneficiary lists, and save account numbers.
- **KYC Application Workflow:** Tier-1 onboarding pipeline with photo upload, government ID verification (front/back), and review state tracking.
- **Interactive Mini-Ledger:** Granular transaction filters, debit/credit color indicators, and paginated statement exports.
- **Security Command Center:** Password resets, active session revocation, 2FA authenticator toggle, and 4-digit transaction PIN management.
- **Adaptive Visual Themes:** Instant dark and light mode UI toggling with browser-cached preferences.

### 2. 🏢 Branch Staff & Teller Operations Desk
*Empowering branch officers with real-time customer servicing tools.*
- **Over-the-Counter Cash Operations:** Instant manual cash deposits and teller withdrawals with customer PIN verification.
- **Customer Lookup Engine:** Rapid customer identity lookup, KYC compliance status check, and active account confirmation.
- **Branch Operational Ledger:** Shift-based deposit/withdrawal summaries and automated audit receipt logging.
- **Staff Security & Profile:** Dedicated staff credential management and branch assignment isolation.

### 3. 👑 Executive & Administrator Management Console
*Complete visibility, branch governance, and platform-wide control.*
- **Global Liquidity & KPI Analytics:** Total customer base, active vs. pending accounts, global deposits, and system liquidity metrics.
- **Branch Governance:** Geographic branch creation, state management, and status toggling.
- **Staff Provisioning:** Creation, assignment, and revocation of branch teller and manager accounts.
- **KYC Verification Queue:** Review pending customer applications, inspect uploaded documents, and approve or reject with custom feedback.
- **Audit Trails & Security Logs:** Immutable chronological log of all administrative and financial actions.
- **BCDR Backup & Recovery Station:** Execute full or schema-only AES-256 encrypted database snapshots and verify ledger integrity directly from the CLI/UI.

---

## 🏗️ System Architecture

Finzo follows a decoupled, API-first Clean Architecture pattern:

```text
                     ┌────────────────────────────────────────┐
                     │          Web Browser / Client          │
                     │  (Vanilla JS, Tailwind CSS, HTML Views)│
                     └───────────────────┬────────────────────┘
                                         │ JSON Requests / AJAX
                                         ▼
                     ┌────────────────────────────────────────┐
                     │          Presentation Layer            │
                     │   (Router, Middleware, Controllers)    │
                     └───────────────────┬────────────────────┘
                                         │ DTOs
                                         ▼
                     ┌────────────────────────────────────────┐
                     │           Application Layer            │
                     │  (Services, Actions, Event Listeners)  │
                     └───────────────────┬────────────────────┘
                                         │ Domain Contracts
                                         ▼
                     ┌────────────────────────────────────────┐
                     │              Domain Layer              │
                     │   (Entities, Value Objects, Enums)     │
                     └───────────────────┬────────────────────┘
                                         │ Repositories
                                         ▼
                     ┌────────────────────────────────────────┐
                     │         Infrastructure Layer           │
                     │   (PDO Adapters, QueryStore, Storage)  │
                     └───────────────────┬────────────────────┘
                                         │ SQL / Prepared Statements
                                         ▼
                     ┌────────────────────────────────────────┐
                     │          MySQL 8.0 Database            │
                     │ (Normalized Schema, Transactions, FKs) │
                     └────────────────────────────────────────┘
```

### Folder Structure
```text
Finzo/
├── app/
│   ├── Application/          # Application business logic (Services, Actions, DTOs, Jobs)
│   ├── Domain/               # Domain models, Enums, Contracts, and Domain Exceptions
│   ├── Infrastructure/       # Database Repositories, Query Stores, and File Storage
│   ├── Presentation/         # HTTP Controllers, Security Middleware, and Validators
│   └── Shared/               # Core Framework (Router, IoC Container, Security, Cache, Queue)
├── bin/                      # CLI tools (bin/finzo migration/seed runner, check-coverage.php)
├── bootstrap/                # Application initialization and dependency injection setup
├── config/                   # Centralized configuration (db.php, mailer.php, config.php)
├── database/                 # Migrations, seeders, and BcdrManager backup engine
├── docs/                     # Comprehensive engineering reports, API specs, and audits
├── public/                   # Public web root (index.php, CSS design system, JS components, Views)
├── routes/                   # Route registry mapping all HTTP verbs to controllers (routes/api.php)
├── storage/                  # Logs, encrypted backups, file cache, and runtime sessions
├── tests/                    # PHPUnit Unit, Integration, Banking Atomicity, and Security tests
├── SETUP_GUIDE.md            # Complete step-by-step local setup & installation guide
├── composer.json             # Autoloading configuration & developer tooling dependencies
├── phpcs.xml                 # PSR-12 code style ruleset definition
├── phpstan.neon              # Static analysis configuration
└── phpunit.xml               # Automated test suite runner configuration
```

---

## 🛠️ Technology Stack

| Domain | Technology | Purpose |
| :--- | :--- | :--- |
| **Backend Engine** | **PHP 8.4+** | High-performance core API, OOP Clean Architecture, Strict Typing |
| **Database** | **MySQL 8.0 / MariaDB** | ACID relational storage, foreign key constraints, prepared statements |
| **Frontend UI** | **Vanilla ES6+ JS** | Lightweight, reactive UI without client framework overhead |
| **Styling** | **Custom Design Tokens + Tailwind CSS** | Fluid typography, responsive layouts, dynamic Dark/Light theme |
| **Security** | **Bcrypt & OpenSSL (AES-256)** | State-of-the-art credential and PII data encryption at rest |
| **Testing** | **PHPUnit 13.x** | Automated Unit, Integration, Regression, and Security testing |
| **Quality Assurance**| **PHP_CodeSniffer (PSR-12) & PHPStan** | Strict linting, static type analysis, and coding style validation |
| **Dependency Mgmt** | **Composer 2.x** | PSR-4 autoloading and developer tooling management |

---

## 🚀 Quick Setup & Installation

> 📖 **Need a complete beginner-friendly walkthrough?** See the full [**Local Setup & Installation Guide (SETUP_GUIDE.md)**](SETUP_GUIDE.md) for OS-specific instructions, virtual host setups, and troubleshooting.

### 1. Clone the Repository
```bash
git clone https://github.com/EL-STRIX/Finzo.git
cd Finzo
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Configure Environment
```bash
# Copy the environment template
cp .env.example .env
```
Open `.env` and set your MySQL credentials:
```ini
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=finzo_db
DB_USER=root
DB_PASS=
```

### 4. Migrate & Seed Database
```bash
php bin/finzo migrate
php bin/finzo seed system
php bin/finzo seed dev
```

### 5. Start Apache / Server
Ensure Apache and MySQL are running in **XAMPP**, **Laragon**, or start the built-in server:
```bash
php -S localhost:8000 -t public
```
Visit your application in the browser at: `http://localhost/Finzo/public` (or `http://localhost:8000`).

---

## 🔑 Default Seeded Credentials

When seeded with `php bin/finzo seed dev`, use the following pre-configured credentials:

| Role | Portal URL | Login Email / ID | Password | Notes |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | `/public/views/auth/admin-login.html` | `admin@finzo.com` | `Admin@123` | Full administrative control & platform KPIs |
| **Branch Staff** | `/public/views/auth/staff-login.html` | `rahul.sharma1@finzo.com` | `Staff@123` | Teller operations (Deposits, Withdrawals, KYC) |
| **Customer User** | `/public/views/auth/login.html` | `rahul.sharma1@example.com` | `User@123` | Retail banking dashboard (PIN: `1234`) |

---

## 🧪 Testing & Code Quality

Finzo comes with an automated testing and verification suite to ensure rock-solid stability before deployment.

```bash
# Run the complete PHPUnit test suite (196+ tests, 620+ assertions)
vendor/bin/phpunit

# Verify strict PSR-12 coding standard compliance
vendor/bin/phpcs --standard=phpcs.xml app/ tests/

# Execute static analysis
vendor/bin/phpstan analyse

# Check code coverage metrics
php bin/check-coverage.php
```

---

## 📚 Documentation Sitemap

Comprehensive architectural runbooks, security audits, and API dictionaries are available in the [`docs/`](docs/) directory:

- [**📘 Documentation Index (docs/INDEX.md)**](docs/INDEX.md) — Master table of contents
- [**🏛️ Clean Architecture Guide (docs/ARCHITECTURE.md)**](docs/ARCHITECTURE.md) — Architectural principles and request lifecycle
- [**🗄️ Database Schema Reference (docs/database/SCHEMA_REFERENCE.md)**](docs/database/SCHEMA_REFERENCE.md) — Table schemas and relationships
- [**🔄 DBA Migration Runbook (docs/database/MIGRATION_GUIDE.md)**](docs/database/MIGRATION_GUIDE.md) — Migrations, seeding, and BCDR
- [**📡 API Reference (docs/api/README.md)**](docs/api/README.md) — RESTful endpoints, payloads, and response standards
- [**🔐 Security & Compliance Audit (docs/security/README.md)**](docs/security/README.md) — Threat models and OWASP mitigations
- [**🧪 Testing Strategy (docs/testing/README.md)**](docs/testing/README.md) — Testing philosophy, isolation, and CI gating

---

## 🤝 Contributing & Engineering Standards

We welcome contributions from the community! To maintain the highest engineering quality:
1. Follow **PSR-12** standards strictly (`vendor/bin/phpcs`).
2. Adhere to **Clean Architecture** (Keep Controllers thin, Services rich, and DB access in Repositories).
3. Cover new business logic with unit/integration tests (`vendor/bin/phpunit`).
4. Commit atomically with concise, descriptive commit messages.

---

## 📄 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Project Developer

**Sujay Paul**  
*Full Stack Developer & Software Engineering Student*  
- **GitHub:** [@EL-STRIX](https://github.com/EL-STRIX)  
- **Project Repository:** [https://github.com/EL-STRIX/Finzo](https://github.com/EL-STRIX/Finzo)

*Crafted with passion for clean code, robust software architecture, and financial reliability.*
