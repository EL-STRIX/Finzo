# 🏦 Finzo — Online Banking System

<div align="center">

![Finzo Banking](https://img.shields.io/badge/Finzo-Banking%20System-0891b2?style=for-the-badge&logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0id2hpdGUiIGQ9Ik0xMiAxTDMgNXYyaDE4VjVMMTIgMXptLTEgN3YxMEg3VjhIMTF6bTIgMHYxMGg0VjhIMTN6TTMgMTl2Mmgxc FTUTG0m0tMSAwVjE5SDN6Ii8+PC9zdmc+)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)

**"Banking Made Simple"**

A fully responsive, multi-role, front-end online banking system built with pure HTML, CSS and JavaScript — no backend or database required to run.

[View Demo](#demo-credentials) · [Run Locally](#how-to-run-locally) · [Project Structure](#project-architecture)

</div>

---

## 📋 Overview

Finzo is a comprehensive front-end online banking system featuring three distinct role-based portals — Customer, Staff and Admin — each with a unique design theme and complete set of banking features. Built entirely with HTML5, CSS3, JavaScript, Tailwind CDN and Google Material Symbols, it simulates a real banking workflow including account registration, KYC verification, fund transfers, transaction history, branch management and a full admin control panel — all powered by localStorage for demo state management, with every form and endpoint clearly marked for future PHP and MySQL backend integration.

---

## ✨ Features

### 🔐 Authentication & Security
| Feature | Description |
|---|---|
| Multi-role Login | Single login page with Customer, Staff and Admin tab switcher |
| Registration Flow | Quick 2-minute sign-up with name, email, phone and password |
| Forgot Password | Password reset flow with email verification step |
| Session Management | Role-based routing with localStorage state and PHP session hooks |
| Login Attempt Limiting | Locks account after 5 failed consecutive login attempts |
| CSRF Protection | All forms include hidden CSRF token fields ready for backend |
| Change Password | Secure password update with strength meter and confirm validation |

### 👤 Customer Features
| Feature | Description |
|---|---|
| KYC Profile Completion | 4-step guided form — Personal Info, Address, Identity Documents, Account Preference |
| Account Activation Flow | Status-driven journey: Pending → Under Review → Active or Rejected |
| Dashboard | Greeting, balance card, spending chart, quick actions and recent transactions |
| Fund Transfer | ACID-safe account-to-account transfer with live recipient lookup and PIN authorization |
| Transaction History | Full paginated history with search, date range, type and status filters |
| Account Statement | Filterable statement with browser print and PDF download support |
| Beneficiary Management | Save, manage and quick-select transfer recipients |
| Notifications | Real-time in-app alerts for account events with read/unread state |
| Profile Management | Edit personal info, upload avatar and view account details |
| Spending Analytics | Doughnut chart showing monthly spending categories (active accounts only) |
| Daily Spending Limit | Customer-configurable daily transfer and withdrawal cap |
| Transaction Receipt | Printable receipt generated automatically after every transaction |
| Soft Delete Recovery | Admin can fully restore a deleted customer account at any time |

### 🏧 Staff Features
| Feature | Description |
|---|---|
| Staff Dashboard | Shift overview with today's deposit total, withdrawal total and transaction count |
| Process Deposit | 4-step guided form to credit a customer account with staff ID recorded |
| Process Withdrawal | 4-step guided form with live balance validation before debiting |
| Transaction Receipt | Auto-generated printable receipt after every staff-processed transaction |
| Transaction History | Full history of all transactions processed by that staff member |
| End Shift | Confirm modal before logout that summarizes shift activity |

### 🛡️ Admin Features
| Feature | Description |
|---|---|
| Admin Dashboard | 15 live stat cards covering users, transactions, branches, staff and system events |
| Pending Approvals | Review submitted KYC applications and approve or reject with reason |
| User Management | Full customer list with search, filter, activate, deactivate and soft delete |
| User Detail View | Complete profile, documents, account number, balance and full transaction history |
| Staff Management | Add, edit, view and manage all bank staff accounts with branch assignment |
| Branch Management | Add custom branches with manager assignment, certificate upload and status toggle |
| Transaction Overview | All transactions across all accounts with charts, filters and CSV export |
| Audit Log | Real-time system integrity ledger of every admin and staff action |
| Interest Management | Configure and update monthly savings interest rate with history ledger |
| Deleted Users | Soft-deleted accounts with full data preserved and one-click restore |
| Admin Profile | Admin's own profile, security overview, last login IP and activity log |
| Dark Mode | System-wide dark/light mode toggle saved to localStorage per portal |
| PDF Statement | TCPDF-ready statement export with print CSS for clean output |
| Custom Error Pages | Branded 404 and 500 error pages with navigation links |
| Email Templates | 4 PHPMailer HTML email templates for welcome, review, approval and rejection |

---

## 👥 Three Role-Based Portals

### 🧑‍💼 Super Admin
The Admin Portal is the highest-privilege control centre of the Finzo system. A Super Admin has unrestricted access to every module — from reviewing and approving new customer KYC applications, managing all user and staff accounts, monitoring every transaction across the system, configuring interest rates, reviewing the complete audit log, managing bank branches and overseeing soft-deleted data. Every admin action is automatically recorded in the audit log with a timestamp, action type and entity ID for full accountability. The admin panel uses a dark navy sidebar with indigo accent colours and a comprehensive 15-card dashboard to surface system health at a glance.

### 🏧 Bank Staff
The Staff Portal is a focused, operations-only workspace designed for bank counter staff. A Staff member can process customer deposits and withdrawals on behalf of the customer — logging their Staff ID on every transaction — generate printable receipts, and review their own transaction history for the current shift or all time. Staff do not have access to user management, admin controls, interest settings or audit logs. The staff panel uses the same dark sidebar as the admin panel but with an amber colour theme to visually distinguish it. All staff actions feed directly into the admin's transaction overview and audit log.

### 👤 Customer
The Customer Portal is a full-featured personal banking dashboard where registered users manage their own accounts. The customer journey is carefully staged — a new user registers quickly, then completes a multi-step KYC profile which is submitted for admin review. While pending, the dashboard is restricted to profile and notification access only. Once approved by an admin, the customer receives a unique Finzo account number in the format `FNZ{YEAR}{4-digits}`, their initial deposit is credited, and all banking features unlock — fund transfers, transaction history, statements, beneficiary management and spending analytics. The customer portal uses a teal primary colour to distinguish it cleanly from the admin and staff portals.

---

## 🗺️ Project Architecture

```
finzo/                              ← Project root (serve from here)
│
├── index.html                      ← Landing / entry point
│
├── views/
│   ├── auth/
│   │   ├── login.html              ← Customer + Staff + Admin (tabbed)
│   │   ├── register.html           ← Customer registration
│   │   ├── forgot-password.html    ← Password reset request
│   │   └── reset-password.html     ← New password entry
│   │
│   ├── user/                       ← Customer portal (14 pages)
│   │   ├── dashboard.html
│   │   ├── complete-profile.html   ← 4-step KYC form
│   │   ├── account-pending.html    ← Under review state
│   │   ├── account-rejected.html   ← Rejected state + resubmit
│   │   ├── accounts.html
│   │   ├── account-detail.html
│   │   ├── transfer.html
│   │   ├── transactions.html
│   │   ├── beneficiary.html
│   │   ├── statement.html
│   │   ├── receipt.html
│   │   ├── notifications.html
│   │   ├── profile.html
│   │   └── change-password.html
│   │
│   ├── admin/                      ← Admin panel (13 pages)
│   │   ├── admin-dashboard.html
│   │   ├── admin-pending-approvals.html
│   │   ├── admin-review-application.html
│   │   ├── admin-users.html
│   │   ├── admin-user-detail.html
│   │   ├── admin-staff.html
│   │   ├── admin-staff-detail.html
│   │   ├── admin-branches.html
│   │   ├── admin-transactions.html
│   │   ├── admin-audit-log.html
│   │   ├── admin-deleted-users.html
│   │   ├── admin-interest.html
│   │   └── admin-profile.html
│   │
│   ├── errors/
│   │   ├── 404.html
│   │   └── 500.html
│   │
│   └── emails/                     ← PHPMailer HTML templates
│       ├── welcome-email.html
│       ├── under-review-email.html
│       ├── approved-email.html
│       └── rejected-email.html
│
├── staff/                          ← Staff portal (5 pages)
│   ├── staff-dashboard.html
│   ├── staff-deposit.html
│   ├── staff-withdraw.html
│   ├── staff-receipt.html
│   └── staff-history.html
│
└── assets/
    ├── css/
    │   ├── user.css                ← Customer portal design system
    │   ├── admin.css               ← Admin panel styles
    │   └── dark-mode.css           ← Dark mode overrides
    └── js/
        ├── user-shared.js          ← Customer shared logic + state
        ├── admin-dropdowns.js      ← Admin custom dropdowns
        ├── theme.js                ← Dark/light mode toggle
        └── main.js                 ← Shared utilities
```

### Account Number Format
```
FNZ + YEAR + 4-digit sequence
Example: FNZ20260001
```

### Staff ID Format
```
STF + 3-digit sequence
Example: STF001, STF002
```

### Transaction ID Format
```
TXN + YEAR + 4-digit sequence
Example: TXN20260001
```

### Receipt ID Format
```
RCP + YEAR + 4-digit sequence
Example: RCP20260001
```

### Account Status Flow
```
REGISTERED → UNDER REVIEW → ACTIVE
                           → REJECTED → UNDER REVIEW → ACTIVE

ACTIVE → DEACTIVATED (admin action)
       → FROZEN (daily limit breach)
       → DELETED (soft delete — recoverable)
```

---

## 🎨 Design System

| Portal | Primary Colour | Sidebar |
|---|---|---|
| Customer | Teal `#0891b2` | Dark Navy `#0f172a` |
| Staff | Amber `#f59e0b` | Dark Navy `#0f172a` |
| Admin | Navy `#1a3c5e` | Dark `#0d1117` |

**Fonts:** Plus Jakarta Sans (headings) · Inter (body)  
**Icons:** Google Material Symbols Outlined  
**UI Framework:** Tailwind CSS CDN  

**Dark Mode colours:**

| Token | Light | Dark |
|---|---|---|
| Page background | `#f1f5f9` | `#0d1117` |
| Card background | `#ffffff` | `#161b22` |
| Border | `#e2e8f0` | `rgba(255,255,255,0.06)` |
| Text primary | `#0f172a` | `#f0f6fc` |
| Text secondary | `#64748b` | `#8b949e` |

---

## 🔑 Demo Credentials

> All state is managed via `localStorage`. No server required.

### Customer Portal
```
URL:      /views/auth/login.html  →  CUSTOMER tab
Email:    rahul.kumar@gmail.com
Password: Demo@1234

To test different account states, open browser console and run:
  FinzoDemo.setPending()       → Profile incomplete state
  FinzoDemo.setUnderReview()   → Application submitted state
  FinzoDemo.setActive()        → Full access state
  FinzoDemo.setRejected('reason') → Rejected state
```

### Staff Portal
```
URL:       /views/auth/login.html  →  STAFF tab
Staff ID:  STF001
Password:  Staff@1234
```

### Admin Portal
```
URL:       /views/auth/login.html  →  ADMIN tab
Email:     arjun.sharma@finzobank.com
Password:  Admin@1234
```

---

## 🚀 How to Run Locally

This is a **pure front-end project** — no server, no database and no installation needed.

### Option 1 — Open Directly (Simplest)
```
1. Download or clone this repository
2. Open any .html file directly in your browser
   Example: double-click views/auth/login.html
```

### Option 2 — Live Server (Recommended)
If you have VS Code installed:
```
1. Install the "Live Server" extension by Ritwick Dey
2. Right-click any .html file in VS Code
3. Select "Open with Live Server"
4. Browser opens at http://127.0.0.1:5500
```

### Option 3 — Node.js HTTP Server
If you have Node.js installed:
```bash
# Install serve globally (one time only)
npm install -g serve

# Navigate to project folder
cd finzo

# Start server
serve .

# Open browser at http://localhost:3000
```

### Option 4 — Python HTTP Server
If you have Python installed:
```bash
# Navigate to project folder
cd finzo

# Python 3
python -m http.server 3000

# Open browser at http://localhost:3000
```

### Option 5 — XAMPP (For Future PHP Integration)
```
1. Install XAMPP from https://www.apachefriends.org
2. Copy the project folder to:
   Windows: C:/xampp/htdocs/finzo
   Mac:     /Applications/XAMPP/htdocs/finzo
3. Start Apache in XAMPP Control Panel
4. Open http://localhost/finzo in browser
```

> **Note:** All links between pages use relative paths so the project works correctly from any of the above methods.

---

## 🗄️ Future Backend Integration (PHP + MySQL)

This front-end is architected for a smooth PHP backend connection. Every form, button and API call is pre-commented with the exact endpoint and method:

```javascript
// PHP_BACKEND: POST /api/transfer.php
// Wrapped in MySQL BEGIN / COMMIT / ROLLBACK (ACID)
```

### Planned Backend Stack
```
Language:   PHP 8.x
Database:   MySQL 8.x
Server:     Apache (XAMPP / cPanel)
Email:      PHPMailer + SMTP
PDF:        TCPDF
Auth:       PHP Sessions + bcrypt
CSRF:       Token-based validation
```

### Planned Database Tables
```
users               → Customer accounts and KYC data
accounts            → Bank account numbers and balances
staff               → Staff accounts and branch assignment
transactions        → All financial transaction records
beneficiaries       → Saved transfer recipients
notifications       → In-app notification records
audit_log           → Every admin and staff action
branches            → Branch details and status
interest_rates      → Interest rate history
```

---

## 📄 Page Count Summary

| Portal | Pages |
|---|---|
| Auth (Login, Register, Forgot, Reset) | 4 |
| Customer Portal | 14 |
| Admin Panel | 13 |
| Staff Portal | 5 |
| Error Pages (404, 500) | 2 |
| Email Templates (PHPMailer) | 4 |
| **Total** | **42** |

---

## 📦 Tech Stack

| Technology | Purpose |
|---|---|
| HTML5 | Page structure and semantic markup |
| CSS3 | Custom design system and animations |
| JavaScript (ES6+) | UI logic, state management and interactivity |
| Tailwind CSS CDN | Utility-first styling |
| Google Material Symbols | Icon library throughout all portals |
| Google Fonts | Plus Jakarta Sans and Inter typefaces |
| Chart.js CDN | Dashboard charts and spending analytics |
| localStorage | Demo state management (replaces PHP sessions) |
| jQuery 3.7.1 | DOM helpers and event handling |

---

## 📁 Browser Support

| Browser | Support |
|---|---|
| Google Chrome 100+ | ✅ Full support |
| Microsoft Edge 100+ | ✅ Full support |
| Mozilla Firefox 100+ | ✅ Full support |
| Safari 15+ | ✅ Full support |
| Opera 85+ | ✅ Full support |
| Internet Explorer | ❌ Not supported |

---

## 🔒 Security Notes

> This project is a **front-end demonstration only**. The following security features are stubbed and will be fully implemented in the PHP backend:

- Passwords are **not hashed** in localStorage — bcrypt will be used in PHP
- Admin password verification on branch creation is UI-only in demo mode
- CSRF tokens are present in all forms but not validated without a server
- Session expiry and login attempt limiting require a PHP backend
- File uploads (ID documents, certificates) are preview-only — no actual storage

---

## 👤 Author

**Sujay Paul**  
📧 [GitHub: Sujay Paul](https://github.com/EL-STRIX)  
📍 Kolkata, West Bengal, India  
🎓 Student Project — Online Banking System Frontend

---

## 📜 License

```
MIT License

Copyright (c) 2026 Sujay Paul

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the "Software"),
to deal in the Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish, distribute, sublicense,
and/or sell copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```

---

## 🙏 Acknowledgements

- [Google Fonts](https://fonts.google.com) — Plus Jakarta Sans & Inter typefaces
- [Google Material Symbols](https://fonts.google.com/icons) — Icon library
- [Tailwind CSS](https://tailwindcss.com) — Utility-first CSS framework
- [Chart.js](https://www.chartjs.org) — Dashboard data visualisation
- [jQuery](https://jquery.com) — DOM manipulation library

---

<div align="center">

**⭐ Star this repo if you found it useful**

Made with ❤️ by Sujay Paul · Kolkata, India · 2026

</div>
