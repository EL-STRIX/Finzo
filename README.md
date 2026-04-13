# Finzo — Premium Institutional Banking Ecosystem

![Finzo Version](https://img.shields.io/badge/Version-1.0.0--beta-0891b2?style=for-the-badge)
![Tech Stack](https://img.shields.io/badge/Stack-HTML5%20%7C%20CSS3%20%7C%20JS-blue?style=for-the-badge)
![UI Design](https://img.shields.io/badge/Design-Tokenized%20Architecture-purple?style=for-the-badge)
![Status](https://img.shields.io/badge/Status-Beta%20Demo-f59e0b?style=for-the-badge)

**Finzo** is a sophisticated, institutional-grade banking platform designed to simulate a high-fidelity wealth management experience. Distinguished by its multi-role architecture and data-dense interactive interfaces, Finzo provides a seamless operational blueprint for customers, bank staff, and administrative oversight teams.

---

## 🏛️ Institutional Pillars

Finzo is architected around a **Triple-Tier Role Hierarchy**, ensuring segregated operational environments for different personas:

- **Admin Strategic Console**: Empowering oversight teams with global analytics, user governance, and monetary policy controls.
- **Staff Operational Suite**: A streamlined workflow for processing physical liquidity (Deposits/Withdrawals) and auditing histories.
- **Customer Wealth Portal**: A data-dense personal dashboard for real-time asset tracking, fund settlements, and KYC management.

---

## ✨ Unified Feature Matrix

### 👤 Customer Terminal
- **Wealth Dossier**: Consolidated view of all assets with predictive portfolio visualization.
- **Settlement Engine**: ACID-protected fund transfers with instant recipient identity verification.
- **Digital Registry**: Centralized beneficiary management for rapid liquidity movement.
- **Institutional Ledger**: Detailed transaction history with drill-down receipt generation.
- **Signal Feed**: Categorized notification system for transactions, security, and alerts.
- **Profile Hub**: Self-service KYC resubmission and personal security management.
- **Authorization PIN**: Secondary 4-digit security layer for high-fidelity transactions.

### 🛡️ Administrative Oversight
- **Strategic Analytics**: Real-time tracking of institutional liquidity, growth, and system health.
- **Governance Workflow**: Multi-stage review system for new user applications and KYC updates.
- **Audit Ledger**: Comprehensive tracking of all system-wide actions for regulatory compliance.
- **Policy Controls**: Dynamic management of global interest rates and transaction thresholds.
- **Institutional Directory**: Advanced management of all system users and operational staff.

### 💼 Operational Staff Portal
- **Liquidity Desk**: Dedicated modules for physical deposit and withdrawal processing.
- **Fidelity Receipts**: Instant generation of print-optimized transaction receipts.
- **Performance Tracking**: Real-time logging of staff-initiated actions and fulfillment history.

### 🌐 Universal Systems
- **Dual-Engine Theming**: Seamless transition between High-Contrast Light and Glassmorphic Dark modes.
- **Asset Identity**: Premium card-based account visualization with trust score metadata.
- **Transactional Emails**: Suite of responsive templates (Welcome, Under-Review, Approved, Rejected).
- **Graceful Failures**: Fully customized 404 and 500 institutional error pages.

---

## ⚙️ Technical Architecture

### 🎨 Tokenized Design System
Finzo utilizes a **Modular Design Token** architecture built on Vanilla CSS variables. This ensures 100% visual consistency across 20+ pages while maintaining ultra-low latency.
- **Typography**: Optimized using `Plus Jakarta Sans` for headers and `Inter` for data readability.
- **Elevation**: A hierarchical shadow system for depth perception and modal priority.
- **Micro-animations**: Staggered entrance sequences (`animate-in`) for an interactive, "living" interface feel.

### 🧠 Client-Side State Engine
The ecosystem is powered by a centralized **State Engine** (`UserState`) that simulates server-side persistence using `LocalStorage`.
- **Role-Based Access Control (RBAC)**: Intelligent logic-tunneling that redirects or locks features based on account status.
- **ACID Simulation**: Logical implementation of Atomicity and Consistency for fund transfers to ensure balance integrity.
- **Print Fidelity**: Integrated `@media print` CSS engine for generating physical banking receipts.

---

## 🚀 Getting Started

No complex installation is required. Finzo is a zero-dependency frontend implementation.

### 1. Launch Instruction
```bash
# Fastest deployment using Node.js
npx serve .

# Alternative using Python
python -m http.server 8000
```
Open your browser at `http://localhost:3000`.

### 2. Demo Credentials ![Demo Data](https://img.shields.io/badge/Value-Demo--Data-0891b2?style=flat-square)
Use the following credentials to explore the different institutional pillars:

| Role | Identity (Email / Account) | Password | Access Link |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@finzo.com` | `admin123` | [Admin Portal](views/auth/admin-login.html) |
| **Staff** | `staff@finzo.com` | `staff123` | [Staff Portal](views/auth/staff-login.html) |
| **Customer** | `user@finzo.com` **or** `12345678` | `user123` | [Customer Portal](views/auth/login.html) |

### 💳 Institutional Reference
| Account Holder | Account Type | Account Number | Security PIN |
| :--- | :--- | :--- | :--- |
| **Rahul Kumar** | **Savings (Primary)** | `FNZ-SAV-12345678` | `1234` |
| **Rahul Kumar** | **Current (Secondary)** | `FNZ-CUR-87654321` | `1234` |
| **Priya Sharma** | Beneficiary Node | `FNZ20260002` | N/A |

---

## 🛠️ Diagnostic & Testing Suite
For technical reviewers, Finzo includes a hidden `FinzoDemo` diagnostic tool. Open your browser's console (`F12`) to trigger global state changes:
- `FinzoDemo.setActive()` — Unlocks all banking and settlement features.
- `FinzoDemo.setPending()` — Simulates a "Under Review" KYC state.
- `FinzoDemo.setRejected(reason)` — Triggers an institutional rejection flow.

---

## 🛤️ Evolution Roadmap
- [ ] **Unified Backend Integration**: Transitioning to a PHP/MySQL core (Project In Progress).
- [ ] **Live Market Analytics**: Integration of Chart.js for real-time portfolio trend tracking.
*Built with passion by **Sujay Paul**.*
