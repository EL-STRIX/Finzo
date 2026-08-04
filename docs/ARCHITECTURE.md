# Finzo Architecture Overview

## Design Philosophy
Finzo is built on a **Clean Architecture** (MVC-like) paradigm using Vanilla PHP 8.4 without heavy frameworks. 
The system enforces strict separation of concerns between HTTP logic (Controllers), Business Logic (Services), and Database operations (Repositories).

## Request Lifecycle
1. **Entry Point:** All requests route through `public/index.php`.
2. **Router:** `App\Shared\Core\Router` parses the URI and dispatches it to the appropriate Controller.
3. **Middleware:** Before hitting the Controller, Security Middleware (e.g., `AdminAuthMiddleware`) validates sessions and permissions.
4. **Controller:** Extracts HTTP inputs and delegates to the Application Service layer.
5. **Service:** Contains all business logic (e.g., transfers, approvals) and calls Repositories.
6. **Repository:** Executes PDO SQL queries using prepared statements against MySQL.
7. **Response:** Controllers return JSON for the frontend to consume.

## Database Design
The schema uses InnoDB with strict foreign key constraints. 
Key tables:
- `users`: Core identity table
- `accounts`: User banking accounts
- `transactions`: Immutable ledger of all monetary movements

## Security
- **Passwords:** Bcrypt hashed.
- **CSRF:** Token required for all non-GET requests.
- **Transactions:** Wrapped in MySQL `BEGIN` / `COMMIT` blocks to prevent race conditions during fund transfers.
