# Finzo Banking Management System (Enterprise Edition)

Modern secure banking management platform built with PHP and MySQL.

## Enterprise Architecture & Deployment
This repository has been fully modernized to follow Clean Architecture (MVC).
- **Core Engine:** Procedural logic has been extracted into `Controllers`, `Services`, and `Repositories`.
- **Production Configuration:** 
  1. Copy `.env.example` to `.env`.
  2. Configure your `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS`.
  3. Ensure `APP_DEBUG=false` for production to suppress errors.
- **CI/CD & Testing:** 
  - Automated CI pipelines are configured via GitHub Actions (`.github/workflows/ci.yml`).
  - To run tests locally, install dependencies via Composer (if required) and run: `phpunit --configuration phpunit.xml`

---

## 2. Project Overview

Finzo is a comprehensive banking management system designed to digitize and streamline financial operations. Built for modern enterprise needs, it bridges the gap between banking administrators, branch staff, and end-users through a unified, secure platform. 

The system solves the complexities of manual banking operations by providing digital account management, ACID-compliant fund transfers, role-based access control, and transaction monitoring. It is designed to be used by banking institutions seeking a lightweight, scalable, and maintainable core banking solution without the overhead of heavy enterprise frameworks.

## 3. Core Features (100+ Capabilities)

**Customer Features (Portal & Dashboard)**
1. Real-time total balance dashboard
2. Interactive transaction history with filtering
3. Pagination for transaction ledger
4. Persistent session management
5. Profile completion and KYC document upload workflow
6. Real-time application status tracking (Pending, Under Review, Rejected)
7. Profile picture and avatar management
8. Dynamic dark and light mode UI toggling
9. In-browser local storage caching for themes
10. Balance obfuscation/masking for privacy in public
11. 4-Digit Security PIN validation for unmasking balance
12. Interactive mini-ledger for quick transaction insights
13. Secure intra-bank fund transfers with recipient validation
14. Real-time beneficiary name resolution during transfer setup
15. Transaction memo and description tagging
16. Transaction reversal protection (ACID safe)
17. Comprehensive transfer confirmation overlays
18. Saved beneficiary list management
19. Star-marked quick-select beneficiary pills
20. Beneficiary deletion capability
21. Detailed account metadata display (Account Number, Type, Interest Rate, Limit)
22. Dynamic sidebar navigation with active state highlighting
23. Dropdown notification center with unread badge counter
24. Mark-all-as-read notification functionality
25. Granular transaction types (Deposit, Withdrawal, Transfer In/Out, Interest)
26. Visual color-coding for credit vs debit transactions
27. Responsive mobile-friendly sidebar and navbar
28. Downloadable paginated account statements
29. Auto-logout on session expiration
30. Password change interface with strict validation
31. Secure forgotten password recovery via email
32. In-app toast notification system for instant feedback
33. Micro-interactions and CSS animations on buttons and modals
34. Session-backed user state persistence
35. Custom daily transfer limit tracking and enforcement
36. Personalized daily limit configuration API
37. Transaction rollback on insufficient balance
38. Transaction rollback on limit exceeded
39. Automated interest credit visibility
40. Date and time formatting localized to user region
41. Number-to-words currency translation in UI
42. Form-level error highlighting and validation
43. Dynamic CSRF token fetching and attachment
44. AJAX-powered form submissions (no page reloads)
45. Modal-based confirmation dialogs

**Branch Staff Features (Operations Portal)**
46. Branch-specific secure login portal
47. Customer account lookup via unique ID
48. Instant validation of customer account status (Active/Inactive)
49. Display of customer full name, type, and current balance (for staff)
50. Interface for manual cash deposits
51. Real-time database update for deposits
52. Interface for over-the-counter cash withdrawals
53. Customer 4-digit PIN verification for withdrawals
54. Branch-level transaction logging
55. Staff personal profile management
56. Staff password reset and security management
57. Daily branch deposit/withdrawal summaries
58. CSRF-protected operational actions
59. Headless API integration for staff duties
60. Automatic transaction receipt generation logic
61. Strict authorization checks (staff-only routes)
62. Dynamic DOM updates for transaction success/failure
63. Activity tracking for branch operations

**Administrator Features (Management Console)**
64. Segregated administrative login portal
65. Comprehensive global metrics dashboard
66. Total user base tracking
67. Active versus pending account analytics
68. Total system liquidity and transaction volume monitoring
69. Branch creation and geographic management
70. Branch deletion and status toggling
71. Staff account provisioning and assignment to branches
72. Staff account suspension/revocation capabilities
73. KYC approval interface for new customers
74. KYC rejection workflow with custom reason input
75. Interactive customer application queue
76. Administrative profile update interface
77. Password reset and strong credential enforcement
78. Security question configuration for admin recovery
79. Automated account locking after failed admin logins
80. Admin account unlock via security answer verification
81. Detailed system logs and activity tracking
82. Secure global configuration management
83. Advanced API route protection
84. Cross-site request forgery protection across all admin forms
85. Responsive administrative data tables
86. Modular admin sidebar navigation
87. Secure API endpoints for data aggregation

**Core System & Architecture Features**
88. Headless PHP API architecture
89. Strict JSON-only API responses
90. Centralized Database configuration (PDO)
91. Prepared SQL statements for SQLi prevention
92. Global input sanitization and XSS protection
93. Custom JSON response helper functions
94. Password hashing using Bcrypt
95. Robust user session creation and destruction mechanics
96. Session timeout after inactivity
97. Centralized authentication middleware
98. Role-based routing (Customer vs Staff vs Admin)
99. CSRF token generation and session storage
100. CSRF token validation on all POST requests
101. ACID-compliant database transactions for all financial movements
102. Auto-rollback on any mid-transaction failure
103. Standardized error handling and API status codes
104. Dynamic account number generation algorithm (e.g., FNZ2026...)
105. Scalable relational database schema
106. Foreign key constraints for data integrity
107. Immutable transaction ledger design
108. Environment-agnostic base URL configuration
109. Modular CSS structure with CSS Variables (Custom Properties)
110. Reusable JavaScript utility classes and fetch wrappers
111. No-framework vanilla JS implementation for maximum performance
112. Cross-origin resource sharing (CORS) prevention
113. No-cache headers for sensitive API endpoints
114. Strict HTTP method validation (GET/POST enforcement)

## 4. Tech Stack

**Frontend:**
- HTML5 (Semantic Structure)
- CSS3 (Custom Design System, Variables, Flexbox/Grid)
- Vanilla JavaScript (ES6+, DOM Manipulation)

**Backend:**
- PHP 8+ (Core Logic, API Layer, Data Processing)

**Database:**
- MySQL (Relational Data, Foreign Keys, Transactions)

**Other Technologies:**
- AJAX / Fetch API (Asynchronous Communication)
- JSON (Data Serialization)
- Custom PHP Middleware (Security & Routing)

## 5. System Architecture

Finzo follows a decoupled, API-first architecture pattern:

- **Frontend Layer:** Static HTML files serve as the presentation layer. UI state is managed client-side using Vanilla JS, pulling data asynchronously.
- **API Layer:** PHP scripts act as REST-like endpoints. They receive requests, validate payloads, and enforce business rules before interacting with the database.
- **Middleware:** A centralized authentication layer intercepts API and page requests to verify sessions, CSRF tokens, and user roles.
- **Database Layer:** MySQL handles persistent storage using PDO (PHP Data Objects). All financial operations utilize database transactions to guarantee atomicity.

## 6. Project Structure (Clean Architecture)

```text
Finzo/
├── app/                  # Core application logic
│   ├── Application/      # Application services, commands, queries
│   ├── Domain/           # Domain models, enums, interface contracts
│   ├── Infrastructure/   # Database repositories and external services
│   ├── Presentation/     # Controllers and Middleware
│   └── Shared/           # Core utilities (Router, Logging, Config)
├── bin/                  # CLI scripts and workers
├── bootstrap/            # Application bootstrap and DI setup
├── config/               # Global configuration (Cache, Mail, Redis)
├── database/             # SQL migrations and schemas
├── docker-compose.yml    # Docker services definition
├── Dockerfile            # Production PHP-FPM image
├── ops/                  # CI/CD deployment scripts and Nginx config
├── public/               # Public document root (index.php, CSS, JS, Views)
├── routes/               # API route definitions
└── storage/              # File uploads, logs, and cache
```
## 7. Authentication & Security

- **Password Hashing:** Passwords are mathematically hashed using PHP's native `password_hash()` (Bcrypt).
- **Session Handling:** Secure, server-side session management with automatic expiration and activity tracking.
- **Prepared Statements:** All database queries utilize PDO prepared statements to prevent SQL injection.
- **Middleware Protection:** Unauthorized access is blocked at the routing level before execution.
- **CSRF Protection:** Critical POST requests require a valid, session-tied CSRF token to prevent cross-site request forgery.
- **Input Validation:** Strict server-side validation and sanitization of all incoming payloads.

## 8. Database Design

The relational database is normalized and optimized for financial data:

- **users:** Central identity table containing credentials, roles, and profile metadata.
- **accounts:** Holds banking data (balances, limits, account types) linked to users.
- **transactions:** Immutable ledger recording all financial movements (deposits, withdrawals, transfers).
- **beneficiaries:** Maps saved payees for quick and safe fund routing.
- **notifications:** System alerts and communication logs.

All monetary updates are wrapped in `BEGIN`, `COMMIT`, and `ROLLBACK` SQL transactions.

## 9. API Overview

The backend is entirely API-driven, responding strictly in JSON.

- **Auth APIs:** Handle login, logout, and token generation.
- **User APIs:** Retrieve account details, process transfers, and manage profiles.
- **Admin APIs:** Manage system-wide users, oversee branches, and analyze platform metrics.
- **Staff APIs:** Handle localized branch operations like cash deposits and withdrawals.

Standardized responses follow a uniform `{ success: boolean, message: string, data: object }` schema.

## 10. Installation & Setup

### Option 1: Docker (Recommended)
The easiest way to run Finzo locally is using Docker.

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/your-username/Finzo.git
   cd Finzo
   ```

2. **Environment Setup:**
   ```bash
   cp .env.example .env
   # Update DB passwords if necessary, default is 'secret' for Docker
   ```

3. **Start Services:**
   ```bash
   docker-compose up -d
   ```
   The application will be available at `http://localhost:8080`.

### Option 2: Local Server (XAMPP/WAMP)

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/your-username/Finzo.git
   ```

2. **Environment Setup:**
   - Move the `Finzo` directory into your local server's web root (e.g., `htdocs`).
   - Copy `.env.example` to `.env` and fill in your DB credentials.

3. **Database Configuration:**
   - Create a database named `finzo_db`.
   - Import the schema from `database/schema.sql` (if available, otherwise run migrations).

4. **Run the Application:**
   Start Apache and MySQL. Access the platform at `http://localhost/Finzo/public`.

## 11. Environment Configuration

Configuration variables are centralized in the `config/` directory.

- **Base URL:** Defined dynamically or statically in `config.php` to ensure correct asset loading across environments.
- **Database Credentials:** Isolated in `db.php` for easy rotation and secure environment-specific adjustments (Dev/Staging/Prod).

## 12. Usage Flow

1. **Registration/Login:** Users authenticate via the secure `/views/auth` portal.
2. **Dashboard Routing:** Middleware redirects users to their specific dashboard based on their assigned role (Admin, Staff, or Customer).
3. **Banking Operations:** Customers can initiate transfers; Staff can process manual deposits; Admins monitor global platform health.
4. **Session Termination:** Secure logout destroys session data and CSRF tokens.

## 13. Screens / Modules

- **Admin Dashboard:** Platform oversight, user management, and system logs.
- **Staff Dashboard:** Branch-level operations, cash handling, and customer lookup.
- **User Dashboard:** Personal financial overview, mini-ledger, and notifications.
- **Transaction Module:** ACID-safe fund transfer interface with PIN validation.
- **Profile Management:** KYC handling, password updates, and avatar uploads.
- **Authentication Pages:** Login, registration, and secure password resets.

## 14. Future Improvements

- Implementation of Two-Factor Authentication (2FA) via email or SMS.
- Development of a companion mobile application (React Native/Flutter).
- Real-time websocket-based transaction notifications.
- Advanced administrative analytics and report generation.

## 15. Developer Notes

Finzo is built with maintainability and modularity in mind. By keeping the frontend completely decoupled from the backend logic (no PHP mixed with HTML), the platform remains highly scalable. Developers can easily swap the HTML views for a modern JavaScript framework (like React or Vue) in the future without altering the core PHP API logic.

## 16. License

This project is licensed under the MIT License - see the LICENSE file for details.

## 17. Author

**Sujay Paul**  
Full Stack Developer | BCA Student | Project Developer  
Passionate about building scalable, secure, and production-ready software solutions.
