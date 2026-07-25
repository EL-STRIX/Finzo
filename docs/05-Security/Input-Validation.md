---
title: "Input Validation & Sanitization"
category: "Security"
module: "Validation"
last_updated: "2026-07-25"
---

# Input Validation & Sanitization

## 1. Overview
The principle of "Never Trust User Input" is strictly enforced throughout the Finzo Banking System. The application employs centralized validation mechanisms before data ever reaches the domain services or the database.

## 2. Core Validation Utilities
The `App\Shared\Core\Validation\Validator` class provides a unified suite of static methods to validate and sanitize data.

### Validation Methods
- `isEmail(string $email)`: Validates format against RFC standards.
- `isStrongPassword(string $password)`: Enforces complexity rules.
- `isNumeric(string $value)`: Prevents SQL injection or type confusion when handling IDs or currency amounts.
- `isSet(mixed $value)`: Safely checks array keys to prevent undefined index notices.

### Sanitization Methods
- `sanitizeString(string $input)`: Strips tags, trims whitespace, and converts special characters to HTML entities (`htmlspecialchars`). This is the primary defense against Stored XSS.
- `sanitizeEmail(string $email)`: Removes illegal characters from email addresses.

## 3. Dedicated Module Validators
To prevent controller bloat, complex validation logic is abstracted into dedicated Validator classes.
For example, `App\Presentation\Validators\AuthValidator` intercepts raw `$_POST` data, applies strict type checks, verifies password confirmations, and returns localized validation errors.

## 4. PDO Prepared Statements
Even after validation and sanitization, all database interactions exclusively utilize PDO Prepared Statements with parameterized bindings. It is strictly forbidden to concatenate variables into SQL query strings within Finzo repositories.
