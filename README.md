# 🏦 Finzo — Enterprise Banking & Financial Management Platform

<div align="center">

[![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![Database](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Architecture](https://img.shields.io/badge/Architecture-Clean%20Architecture%20%2F%20DDD-00C7B7?style=for-the-badge)](docs/ARCHITECTURE.md)
[![Tests](https://img.shields.io/badge/Tests-196%20Passed%20(100%25)-success?style=for-the-badge&logo=phpunit&logoColor=white)](tests/)
[![Coding Standard](https://img.shields.io/badge/PSR--12-Compliant-blue?style=for-the-badge)](phpcs.xml)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

<p align="center">
  <strong>A modern, high-performance, and security-first enterprise banking platform.</strong><br>
  Engineered with native PHP 8.4, MySQL 8.0, and vanilla JavaScript following Clean Architecture principles — delivering full-scale core banking capabilities with zero framework bloat.
</p>

[**🚀 Setup & Installation Guide**](SETUP_GUIDE.md) • [**📚 Documentation Hub**](docs/INDEX.md) • [**🏛️ Architecture Overview**](docs/ARCHITECTURE.md) • [**🔐 Security Standards**](docs/security/README.md) • [**🧪 Testing Suite**](tests/)

</div>

---

## 🌟 About Finzo

**Finzo** is an enterprise-grade core banking and financial operations management platform designed to streamline banking processes for retail customers, branch tellers, and administrative executives alike.

Rather than relying on heavy, opinionated frameworks, Finzo is built on **pure PHP 8.4 and Vanilla ES6+ JavaScript**, strictly adhering to **Clean Architecture** and **Domain-Driven Design (DDD)** concepts. This design yields sub-millisecond API response times, transparent business logic, rock-solid ACID financial atomicity, and uncompromising data security.

Whether managing high-volume inter-account transfers, onboarding users through digital KYC workflows, or executing encrypted disaster recovery snapshots, Finzo provides an enterprise-ready foundation out of the box.

---

## 🏛️ System Architecture

Finzo enforces a strict separation of concerns across four decoupled architectural layers:

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
                                         │ Prepared Statements
                                         ▼
                     ┌────────────────────────────────────────┐
                     │          MySQL 8.0 Database            │
                     │ (Normalized Schema, Transactions, FKs) │
                     └────────────────────────────────────────┘
```

---

## ⚡ Complete Feature Directory

Finzo delivers an extensive, production-ready feature matrix organized by portal and domain:

### 👤 1. Retail Customer Banking Portal
- **Real-Time Account Dashboard:** Live multi-account balance overview with localized currency formatting and metadata.
- **One-Click Privacy Balance Mask:** Instantly obfuscates balances on screen for privacy in public spaces.
- **4-Digit PIN Security Gate:** Protects balance reveals and financial transactions with dedicated PIN authentication.
- **Instant Intra-Bank Transfers:** Real-time money routing between accounts with automated sender and receiver ledger updates.
- **Beneficiary Resolution Preview:** Automatically resolves and verifies the recipient's full name before payment confirmation.
- **Beneficiary Speed-Dial & Address Book:** Save, star-favorite, and manage frequently used payees for rapid fund transfers.
- **Duplicate Transfer Protection:** 2-minute idempotency window preventing accidental double-clicks and repeated transfers.
- **Daily Transfer Limit Engine:** Configurable daily spending limits with live tracking of remaining daily transfer quota.
- **Digital KYC Onboarding Pipeline:** Structured onboarding workflow capturing personal, employment, and income details.
- **Government ID Document Upload:** Secure file upload for identity cards (front and back) with server-side validation.
- **Avatar & Profile Photo Manager:** Dynamic avatar management with real-time profile picture previews and cropping.
- **Application Status Tracker:** Real-time visual tracking for *Pending*, *Under Review*, *Approved*, and *Rejected* KYC states.
- **Interactive Mini-Ledger:** Searchable, paginated transaction history table with debit/credit visual indicators.
- **Transaction Filtering:** Filter transaction records by date range, transaction type, status, or keyword.
- **Downloadable Account Statements:** Instant export of paginated banking statements to CSV and printable view.
- **Adaptive Dark & Light Mode:** Seamless theme toggling with client-side localStorage preference persistence.
- **In-App Notification Center:** Dropdown alert center with live unread badge counters and a "Mark all as read" button.
- **Two-Factor Authentication (2FA):** TOTP authenticator integration with dynamic secret generation and QR setup.
- **Active Session Security Manager:** View all active login sessions with IP/device info and revoke unauthorized devices.
- **Password Reset & Recovery:** Secure forgotten password recovery workflows powered by time-limited email tokens.
- **Interactive Toast Notifications:** Micro-animated toast alerts delivering instant visual feedback for all operations.

---

### 🏢 2. Branch Staff & Teller Workstation
- **Dedicated Teller Portal:** Segregated, role-restricted login station built exclusively for branch bank officers.
- **Customer Quick-Lookup Engine:** Instant customer discovery via Customer ID, Account Number, Phone, or Name.
- **Live Account Status Verification:** Real-time verification of customer account eligibility, KYC status, and balance limits.
- **Over-the-Counter Cash Deposits:** Teller desk module for processing physical cash deposits with immediate balance credit.
- **Over-the-Counter Cash Withdrawals:** Physical cash withdrawal processing requiring customer 4-digit PIN verification.
- **Branch Operational Journal:** Live shift ledger tracking all cash movements processed by the logged-in teller.
- **Digital Transaction Receipts:** Automated transaction receipt generation complete with unique reference tracking IDs.
- **Branch Customer KYC Intake:** Teller assistance module for reviewing customer documentation and verifying physical IDs.
- **Staff Security & Profile Desk:** Dedicated staff credential management and localized security settings.
- **Branch Data Isolation:** Enforces strict branch-level scoping so staff can only service accounts within their jurisdiction.

---

### 👑 3. Administrator & Executive Governance
- **Executive KPI Analytics Dashboard:** Real-time metrics on total liquidity, active accounts, daily volume, and system health.
- **Geographic Branch Manager:** Complete branch management module to create, edit, locate, and toggle branch statuses.
- **Staff Account Provisioning:** Provision teller and manager accounts and assign them to specific branch locations.
- **Staff Access Suspension & Revocation:** One-click suspension or permanent revocation of compromised staff credentials.
- **Central KYC Verification Queue:** Review submitted KYC identity applications, inspect ID documents, and approve or reject.
- **KYC Custom Rejection Notes:** Send structured rejection feedback with clear instructions for document resubmission.
- **Immutable Audit Trail Engine:** Comprehensive, tamper-proof logging of all administrative actions, logins, and transfers.
- **Automated BCDR Backup Manager:** One-click creation of AES-256 encrypted database snapshots stored safely at rest.
- **Point-in-Time Database Restore:** Restore database state directly from encrypted backups with automatic integrity checks.
- **Double-Entry Balance Validator:** Diagnostic tool validating that total debits match total credits across all journal lines.
- **Anti-Bruteforce Lockout Engine:** Automatically locks accounts after repeated failed login attempts to prevent attacks.
- **Security Question Recovery:** Super Admin emergency unlock flow using cryptographic security question answers.
- **Global Settings Configuration:** Live adjustment of interest rates, minimum deposits, transfer limits, and maintenance modes.
- **Financial & Demographic CSV Exports:** Generate and download high-level analytical CSV reports for compliance audits.
- **Customer Support Ticket Queue:** Centralized ticketing console to prioritize, assign, and reply to user inquiries.

---

### 🛡️ 4. Security, Integrity & Core Architecture
- **Headless API-First Architecture:** Clean RESTful backend responding with standardized JSON response envelopes.
- **IoC Dependency Injection Container:** Lightweight reflection container supporting automatic constructor dependency resolution.
- **Centralized Declarative Router:** High-speed regex router handling GET, POST, PUT, PATCH, and DELETE verbs.
- **Double-Entry Ledger Bookkeeping:** Accounting engine enforcing strict double-entry debits and credits for all transactions.
- **ACID-Compliant Transaction Wrapping:** Guarantees database atomicity and auto-rollback on any mid-operation failure.
- **Bcrypt Password Cryptography:** Password hashing using PHP's native algorithms with adaptive cost factors.
- **AES-256 PII Encryption at Rest:** Transparent OpenSSL encryption for sensitive government ID and tax numbers.
- **CSRF Token Middleware:** Session-bound cryptographic token verification on all state-altering requests.
- **Granular RBAC Authorization:** Middleware gating Customer, Staff, Admin, and Super Admin endpoints.
- **IP-Aware Rate Limiter:** Token-bucket sliding window rate limiter preventing API spam and bruteforce attempts.
- **Strict Input Validation & Sanitization:** Centralized Validator service ensuring robust payload verification.
- **XSS & Output Escaping:** Context-aware output escaping preventing cross-site scripting attacks.
- **Zero-Framework Runtime:** Native PHP 8.4 and Vanilla ES6+ JS delivering lightning speed and minimal resource usage.
- **Modular Design System:** CSS custom properties, utility classes, and accessible WCAG 2.2 AA compliant UI components.
- **100% Passing Test Suite:** 196+ automated unit, integration, and security tests verified via PHPUnit.

---

## 🛠️ Technology Stack

| Component | Technology | Description |
| :--- | :--- | :--- |
| **Backend Language** | **PHP 8.4+** | Strict types, native OOP, Clean Architecture, zero runtime vendor bloat |
| **Database Engine** | **MySQL 8.0 / MariaDB** | InnoDB engine, ACID transactions, foreign keys, UTF-8 unicode collation |
| **Frontend UI** | **Vanilla ES6+ JavaScript** | Decoupled client-side components, Fetch API, DOM manipulation |
| **Styling & Theme** | **Custom Design System + Tailwind CSS** | Fluid typography, CSS variables, Dark/Light modes, responsive design |
| **Security & Cryptography**| **Bcrypt & OpenSSL (AES-256)** | Password hashing, sensitive PII encryption at rest, secure session cookies |
| **Testing Suite** | **PHPUnit 13.x** | Automated unit, integration, security, and banking atomicity test suites |
| **Linting & Quality** | **PHP_CodeSniffer (PSR-12) & PHPStan** | Static analysis, strict typing validation, and PSR-12 code style compliance |
| **CLI & Migrations** | **Finzo CLI (`bin/finzo`)** | Custom CLI for schema migrations, data seeding, and BCDR backup operations |

---

## 🚀 Getting Started & Installation

For full step-by-step installation instructions, environment configuration details, and troubleshooting, please refer to our dedicated guide:

👉 [**Read the Full Local Setup & Installation Guide (SETUP_GUIDE.md)**](SETUP_GUIDE.md)

### ⚡ Quick Summary
1. Clone the repository into your local server root: `git clone https://github.com/EL-STRIX/Finzo.git`
2. Install dependencies: `composer install`
3. Configure `.env` from `.env.example` with your MySQL credentials.
4. Run migrations and seeds: `php bin/finzo migrate && php bin/finzo seed system && php bin/finzo seed dev`
5. Open your browser: `http://localhost/Finzo/public`

### 🔑 Default Demo Accounts
| Role | Portal URL | Login Email | Password | Default PIN |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | `/public/views/auth/admin-login.html` | `admin@finzo.com` | `Admin@123` | N/A |
| **Branch Staff** | `/public/views/auth/staff-login.html` | `rahul.sharma1@finzo.com` | `Staff@123` | N/A |
| **Customer User** | `/public/views/auth/login.html` | `rahul.sharma1@example.com` | `User@123` | **`1234`** |

---

## 📚 Documentation Index

Extensive technical runbooks, architecture reports, and API specifications are documented in the [`docs/`](docs/) directory:

- [**📘 Master Documentation Hub (docs/INDEX.md)**](docs/INDEX.md) — Main index for all technical documentation
- [**🏛️ Clean Architecture Guide (docs/ARCHITECTURE.md)**](docs/ARCHITECTURE.md) — Layered design principles and request lifecycles
- [**🗄️ Database Schema Reference (docs/database/SCHEMA_REFERENCE.md)**](docs/database/SCHEMA_REFERENCE.md) — Detailed table dictionary & relations
- [**🔄 DBA Migration & BCDR Guide (docs/database/MIGRATION_GUIDE.md)**](docs/database/MIGRATION_GUIDE.md) — Migrations, seeders, and backup recovery
- [**📡 API Reference Specification (docs/api/README.md)**](docs/api/README.md) — RESTful endpoints, payloads, and envelopes
- [**🔐 Security & OWASP Audit (docs/security/README.md)**](docs/security/README.md) — Threat models and OWASP ASVS compliance
- [**🧪 Testing Strategy (docs/testing/README.md)**](docs/testing/README.md) — Test architecture, assertions, and CI gating

---

## 🤝 Contributing & Code Quality

Contributions are welcome! Please ensure all modifications follow these standards:
- **PSR-12 Compliance:** Verify code formatting with `vendor/bin/phpcs --standard=phpcs.xml`.
- **Clean Architecture:** Maintain separation of concerns (thin controllers, fat services, repository-only database access).
- **Test Coverage:** Ensure all new business logic is accompanied by passing PHPUnit tests (`vendor/bin/phpunit`).
- **Atomic Commits:** Make single-line, descriptive git commits following standard conventions.

---

## 📄 License

This project is open-sourced under the [**MIT License**](LICENSE).

---

## 👨‍💻 Author & Developer

**Sujay Paul**  
*Full Stack Developer & Software Engineering Student*  
- **GitHub:** [@EL-STRIX](https://github.com/EL-STRIX)  
- **Project Repository:** [https://github.com/EL-STRIX/Finzo](https://github.com/EL-STRIX/Finzo)

*Crafted with precision, clean engineering principles, and a passion for financial technology.*
