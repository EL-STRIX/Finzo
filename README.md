# Finzo Banking Management System

Modern secure banking management platform built with PHP and MySQL.

---

## 2. Project Overview

Finzo is a comprehensive banking management system designed to digitize and streamline financial operations. Built for modern enterprise needs, it bridges the gap between banking administrators, branch staff, and end-users through a unified, secure platform. 

The system solves the complexities of manual banking operations by providing digital account management, ACID-compliant fund transfers, role-based access control, and transaction monitoring. It is designed to be used by banking institutions seeking a lightweight, scalable, and maintainable core banking solution without the overhead of heavy enterprise frameworks.

## 3. Core Features

- **Role-Based Authentication:** Distinct access levels for Administrators, Staff, and Customers.
- **ACID-Compliant Transactions:** Secure, consistent, and isolated fund transfers ensuring financial integrity.
- **API-Driven Backend:** Headless PHP architecture communicating via JSON for modularity and speed.
- **Security Middleware:** Strict session management, route protection, and CSRF validation.
- **Dynamic Dashboards:** Real-time analytics, account summaries, and transaction ledgers.
- **Beneficiary Management:** Secure intra-bank payee registry for quick transfers.
- **Responsive UI/UX:** Clean, modern, and accessible interface built with raw HTML/CSS/JS.

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

## 6. Project Structure

```text
Finzo/
├── api/          # Backend API endpoints (JSON responses)
│   ├── admin/    # Administrator-specific endpoints
│   ├── auth/     # Login, registration, and session logic
│   ├── staff/    # Branch staff operations
│   └── user/     # Customer actions (transfers, profile)
├── assets/       # Static frontend resources
│   ├── css/      # Global styles and themes
│   ├── img/      # Images and icons
│   └── js/       # Core JavaScript and API wrappers
├── config/       # Global configuration and database connection
├── database/     # SQL schemas and migration scripts
├── middleware/   # Security and role-based access checks
├── uploads/      # User-uploaded files (avatars, KYC docs)
└── views/        # HTML presentation layer (Admin, Staff, User)
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

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/your-username/Finzo.git
   ```

2. **Environment Setup:**
   Move the `Finzo` directory into your local server's web root (e.g., `htdocs` for XAMPP or `www` for WAMP).

3. **Database Configuration:**
   - Open PHPMyAdmin (or your preferred SQL client).
   - Create a new database named `finzo_db`.
   - Import the provided SQL schema from `database/schema.sql`.

4. **Connect the App:**
   - Navigate to `config/config.php` and `config/db.php`.
   - Verify the database credentials (usually `root` with no password for local environments).

5. **Run the Application:**
   Start Apache and MySQL. Access the platform via your browser at `http://localhost/Finzo`.

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
