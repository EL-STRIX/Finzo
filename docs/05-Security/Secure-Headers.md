---
title: "Secure Headers"
category: "Security"
module: "Middleware"
last_updated: "2026-07-25"
---

# Secure Headers Enforcement

## 1. Overview
Modern web browsers possess powerful built-in security features. The Finzo Banking System leverages these features globally by enforcing strict HTTP Response Headers on every single endpoint and view via the `SecurityHeadersMiddleware`.

## 2. Enforced Headers

- **`X-Frame-Options: DENY`**
  - *Purpose:* Completely prevents the application from being embedded in an iframe on any domain.
  - *Mitigation:* Defends against Clickjacking and UI redressing attacks.

- **`X-XSS-Protection: 1; mode=block`**
  - *Purpose:* Forces the browser's native Cross-Site Scripting (XSS) filter into strict mode. It refuses to render the page if XSS is detected.
  - *Mitigation:* Secondary defense against Reflected XSS.

- **`X-Content-Type-Options: nosniff`**
  - *Purpose:* Prevents the browser from trying to "guess" the MIME type of a file. It forces the browser to obey the `Content-Type` strictly.
  - *Mitigation:* Defends against MIME-sniffing attacks where malicious scripts are disguised as image files.

- **`Strict-Transport-Security: max-age=31536000; includeSubDomains; preload`**
  - *Purpose:* HSTS instructs the browser to ONLY communicate with the server over HTTPS for the next 1 year (31,536,000 seconds).
  - *Mitigation:* Defends against Protocol Downgrade attacks and Man-in-the-Middle (MITM) attacks.

- **`Content-Security-Policy (CSP)`**
  - *Purpose:* Strictly controls which external resources (scripts, styles, images) the browser is allowed to load.
  - *Mitigation:* Primary defense against XSS, data injection, and malicious CDN compromises.
  - *Current Policy:* `default-src 'self'` (Only resources originating from the exact same host are permitted).

- **`Referrer-Policy: strict-origin-when-cross-origin`**
  - *Purpose:* Strips URL path data when navigating to external sites to prevent leaking sensitive tokens or IDs in the URL.
