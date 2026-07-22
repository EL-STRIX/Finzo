---
title: "Frontend Architecture"
version: "1.0.0"
status: "Active"
owner: "Frontend Team"
category: "UI Design"
last_updated: "2026-07-22"
---

# Frontend Architecture

## Table of Contents
1. [Overview](#overview)
2. [Directory Structure](#directory-structure)
3. [Asset Management](#asset-management)
4. [Communication Flow](#communication-flow)
5. [Security](#security)

## Overview
The Finzo frontend acts as an independent consumer of the `v1` REST API. It utilizes semantic HTML5, Vanilla CSS for raw performance, and decoupled JavaScript modules.

## Directory Structure
```text
assets/
├── css/
│   ├── style.css       # Core design system
│   └── responsive.css  # Media queries
├── js/
│   ├── api.js          # Centralized API fetch wrapper
│   └── app.js          # Global state and DOM interaction
└── images/
```

## Asset Management
CSS utilizes CSS variables (Custom Properties) heavily for theming and dark-mode toggling. JavaScript leverages native ES6 Modules and `async/await` for seamless non-blocking network I/O.

## Communication Flow
The frontend negotiates entirely over JSON. All fetch requests intercept global 401/403 errors to route users back to the authentication portal dynamically.

## Security
- DOM manipulation explicitly utilizes `textContent` over `innerHTML` to prevent DOM-based XSS.
- Sensitive state (like transaction PINs) is never stored in `localStorage`.
## Navigation
- **Parent Document**: [Overview.md](Overview.md)
- **Child Documents**: See sub-folders.
- **Previous Document**: N/A
- **Next Document**: N/A
- **Related Documents**: [Documentation Standards](Documentation-Standards.md)

## Change Log
| Version | Date | Author | Description |
|---|---|---|---|
| 1.0.0 | 2026-07-22 | Architecture Team | Initial Phase 1 Setup |
