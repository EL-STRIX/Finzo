# Chapter 04 — Information Architecture

Version: 1.0.0

Status: Approved

Category: Design System

Document Owner: Finzo Team

Last Updated: July 2026

Document ID: FEPS-04

---

## Chapter Purpose

Information Architecture (IA) defines how information is organized, connected, presented, and accessed throughout the Finzo Banking Management System.

A professional banking platform must allow users to understand where they are, what they can do, and how to reach any feature with minimal effort.

This chapter establishes the official standards for organizing pages, navigation, modules, workflows, and content hierarchy across the entire application.

Every page within Finzo must follow these standards.

---

## Information Architecture Philosophy

The purpose of Information Architecture is not simply to organize pages.

Its purpose is to organize information in a way that makes banking simple, predictable, secure, and efficient.

Users should never search for critical banking functions.

Important actions should always be discoverable.

Every section should feel like part of one connected system.

---

## Core IA Principles

Every page and workflow must satisfy the following principles.

### Clarity

Users must immediately understand the purpose of the current page.

---

### Discoverability

Important banking functions should never be hidden.

Primary actions must always be easy to locate.

---

### Predictability

Navigation patterns should remain consistent throughout the application.

Users should never need to relearn navigation.

---

### Logical Hierarchy

Information should be grouped according to user goals.

Related content should remain together.

Unrelated content should never appear in the same section.

---

### Progressive Disclosure

Display essential information first.

Advanced functionality should appear only when needed.

---

### Consistency

Navigation, layouts, labels, and workflows must remain consistent across every module.

---

## Enterprise Application Structure

The Finzo Banking Management System should be divided into clearly defined modules.

### Public Website

Purpose:

Introduce the platform.

Build trust.

Educate visitors.

Encourage registration.

Pages include:

Homepage

About

Services

Security

FAQ

Contact

Privacy Policy

Terms of Service

---

### Authentication Module

Purpose:

Secure access.

Identity verification.

Account recovery.

Pages include:

Login

Register

Forgot Password

Reset Password

Verification

Pending Account

Rejected Account

Locked Account

Session Expired

---

### Customer Banking Module

Purpose:

Provide complete banking functionality.

Includes:

Dashboard

Accounts

Transfers

Beneficiaries

Transactions

Statements

Profile

Settings

Notifications

Support

---

### Staff Operations Module

Purpose:

Operational banking activities.

Customer assistance.

Approvals.

Transaction handling.

Reports.

---

### Administrator Module

Purpose:

Enterprise management.

Includes:

Dashboard

Users

Staff

Branches

Transactions

Audit Logs

Approvals

Reports

Interest Configuration

System Settings

Security Monitoring

---

## Navigation Philosophy

Navigation should never compete with content.

Navigation exists to help users complete banking tasks.

It should remain:

Simple

Consistent

Predictable

Professional

Accessible

---

## Navigation Hierarchy

Navigation should follow this hierarchy.

Primary Navigation

↓

Module Navigation

↓

Page Navigation

↓

Section Navigation

↓

Component Actions

Users should always understand which level they are currently viewing.

---

## Primary Navigation Standards

Primary navigation should contain only major modules.

Avoid excessive menu items.

Every menu item should have a clear business purpose.

Menu labels should use banking terminology.

Avoid marketing language.

---

## Sidebar Standards

Sidebars should organize operational functionality.

Group related pages together.

Examples:

Accounts

Transfers

Payments

Transactions

Statements

Profile

Support

Administrative sidebars should group management functions logically.

---

## Breadcrumb Standards

Breadcrumbs should appear on pages where users navigate through multiple levels.

Example:

Dashboard

↓

Accounts

↓

Savings Account

↓

Transaction History

Breadcrumbs improve orientation.

Do not display breadcrumbs unnecessarily.

---

## Page Hierarchy

Every page should follow the same hierarchy.

Header

↓

Primary Information

↓

Secondary Information

↓

Supporting Information

↓

Actions

↓

Additional Resources

Critical information must always appear before supporting content.

---

## Dashboard Hierarchy

Every dashboard should prioritize:

Account Summary

↓

Important Notifications

↓

Quick Actions

↓

Recent Transactions

↓

Financial Insights

↓

Secondary Information

Never prioritize decorative content over financial information.

---

## Content Grouping

Related information should remain together.

Examples:

Profile information

Security settings

Notification preferences

Documents

Should never be mixed with unrelated financial information.

---

## Search & Discovery

Search should help users locate information quickly.

Search must include:

Relevant results

Filters

Sorting

Recent searches (where appropriate)

Helpful empty states

---

## Quick Actions

Frequently used banking operations should always be easily accessible.

Examples:

Transfer Money

View Transactions

Download Statement

Manage Beneficiaries

Update Profile

Quick Actions should remain visually consistent across dashboards.

---

## Contextual Navigation

Pages should provide navigation relevant to the current task.

Avoid overwhelming users with unrelated options.

---

## Internal Linking Standards

Pages should link naturally to related functionality.

Examples:

Transaction Details

↓

Download Statement

↓

Report Issue

↓

View Account

Avoid unnecessary navigation loops.

---

## Information Density

Banking platforms display large amounts of information.

Prioritize readability.

Use:

Whitespace

Grouping

Visual hierarchy

Section headings

Avoid:

Overcrowded cards

Long paragraphs

Large blocks of data

Unstructured layouts

---

## Labels & Naming

Use clear banking terminology.

Examples:

Accounts

Transactions

Beneficiaries

Statements

Interest Rate

Available Balance

Pending Transfer

Avoid vague labels.

Examples:

Stuff

More

Data

Miscellaneous

---

## Error Navigation

Users should never reach a dead end.

Every error page should provide:

Explanation

Recovery option

Return path

Support contact

---

## Footer Navigation

Public pages should include:

Privacy Policy

Terms of Service

Security

Contact

Support

Accessibility

Copyright

Administrative pages should avoid unnecessary marketing links.

---

## Cross-Module Consistency

Navigation patterns should remain consistent across:

Public Website

Authentication

Customer

Staff

Administrator

Users should never need to relearn navigation when changing modules.

---

## Navigation Accessibility

Navigation must support:

Keyboard navigation

Screen readers

Visible focus states

Logical tab order

Touch accessibility

Accessible labels

---

## Information Architecture Review Checklist

Every module must answer "Yes" to the following questions:

✓ Can users immediately understand where they are?

✓ Can users quickly locate important banking functions?

✓ Is navigation consistent?

✓ Is information logically grouped?

✓ Are critical actions easy to find?

✓ Is content hierarchy clear?

✓ Are labels meaningful?

✓ Is navigation accessible?

✓ Is every module connected logically?

✓ Does the application feel like one unified banking platform?

---

## Chapter Completion Criteria

This chapter is complete only when:

✓ Information Architecture is standardized.

✓ Navigation hierarchy is documented.

✓ Module relationships are defined.

✓ Dashboard hierarchy is established.

✓ Content organization is standardized.

✓ Search standards are documented.

✓ Quick Actions are standardized.

✓ Breadcrumb usage is defined.

✓ Labels are standardized.

✓ Accessibility requirements are documented.

✓ Cross-module consistency is established.

✓ Every page follows the same enterprise information architecture.

## End of Chapter 4
---

## See Also

- **Chapter 00 — Foundation**

---

## Related Chapters

- **Chapter 00 — Foundation**

---

## References

- W3C HTML Standard
- W3C CSS Specifications
- WCAG 2.2

---

## Version History

| Version | Date | Author | Summary of Changes |
|---------|------|--------|--------------------|
| 1.0.0 | July 2026 | Finzo Team | Initial enterprise release |
