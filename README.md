# Finzo Banking System - Project Architecture

Welcome to the newly structured, production-ready Finzo Banking System. 
This document outlines the professional directory layout, the load order rules for frontend assets, and instructions for running the system.

## 📂 Senior Folder Structure

*   **`api/`** — All backend PHP endpoints, strictly separated by portal access level:
    *   `auth/` (Login, registration, password resets)
    *   `admin/` (Administrative functions & management)
    *   `staff/` (Branch staff operations)
    *   `user/` (Customer dashboard and transaction operations)
*   **`assets/`** — All frontend assets including CSS, JavaScript, and images.
    *   `css/` — Vanilla CSS stylesheets (e.g., `index.css`).
    *   `js/` — JavaScript files strictly organized by responsibility:
        *   `core/` — Utilities and core wrappers (`api.js`, `utils.js`).
        *   `public/` — Logic shared exclusively on public-facing pages (`public-shared.js`).
        *   `admin/` — Admin portal logic (includes `admin-shared.js`).
        *   `staff/` — Staff portal logic (includes `staff-shared.js`).
        *   `user/` — Customer portal logic (includes `user-shared.js`).
        *   `ui/` — Generic UI scripts (e.g., charts).
*   **`config/`** — Backend environment configurations, database connection (`db.php`), and shared functions. Protected from direct HTTP access.
*   **`database/`** — Contains all SQL schema and migration files. Protected from direct HTTP access.
*   **`middleware/`** — PHP session validators and authentication guards. Protected from direct HTTP access.
*   **`scripts/`** — CLI utility scripts (e.g., static analysis). Protected from direct HTTP access.
*   **`uploads/`** — User-uploaded files (like KYC documents). Secured via `.htaccess` to block any PHP execution and restrict file types.
*   **`views/`** — All frontend HTML pages, organized by portal role (`admin`, `staff`, `user`, `auth`, `errors`, `emails`).

*(Note: The `components/` directory and JS-based component loaders have been safely removed to prevent flickering and unnecessary JS overhead. All portal structures have been unified.)*

## 📜 JavaScript Load Order

To prevent race conditions, errors, and ensure UI scripts have access to core utilities, the JavaScript files must be loaded in the following strict order inside HTML pages:

1.  **Core API Wrapper:** `<script src="../../assets/js/core/api.js"></script>`
2.  **Core Utilities:** `<script src="../../assets/js/core/utils.js"></script>`
3.  **Portal Shared Logic:** 
    *   `<script src="../../assets/js/user/user-shared.js"></script>` (For user pages)
    *   *or* `<script src="../../assets/js/admin/admin-shared.js"></script>` (For admin pages)
    *   *or* `<script src="../../assets/js/staff/staff-shared.js"></script>` (For staff pages)
    *   *or* `<script src="../../assets/js/public/public-shared.js"></script>` (For public/auth pages)
4.  **Page-Specific Logic:** `<script src="../../assets/js/{portal}/{page-name}.js"></script>` (e.g., `dashboard.js`)

## 🚀 Setup Instructions

1.  Place this folder inside your web server directory (e.g., `c:\xampp\htdocs\Finzo`).
2.  Import the SQL schemas from the `database/` folder in the following order:
    1.  `finzo_database.sql`
    2.  `update_staff_schema.sql`
    3.  `update_admin_schema.sql`
3.  Ensure your server allows `.htaccess` overrides to secure protected directories.
4.  Access the landing page via your browser at `http://localhost/Finzo/index.html`.
