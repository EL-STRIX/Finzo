# Chapter 19 — Content & Copywriting

Version: 1.0.0

Status: Approved

Category: User Experience

Document Owner: Finzo Team

Last Updated: July 2026

Document ID: FEPS-19

## Scope

This chapter applies to every interface.

Including:

Public Website

Authentication

Customer Portal

Staff Portal

Administrator Portal

Dialogs

Forms

Tables

Cards

Navigation

Notifications

Charts

Reports

Future Modules

---

---

## Chapter Purpose

The Finzo Design System defines the visual identity, interaction patterns, and reusable UI components for the entire Banking Management System.

Every page must follow one unified design language.

The purpose is to ensure:

• Visual Consistency

• Professional Banking Appearance

• Enterprise Quality

• Predictable User Experience

• Accessibility

• Scalability

• Maintainability

The design system is mandatory.

No page may create its own visual language.

---

## Enterprise Design Philosophy

Finzo should communicate:

Trust

Security

Stability

Reliability

Professionalism

Modern Banking

Clarity

Calmness

The interface should never feel playful, experimental, or entertainment-focused.

---

## Overall Visual Style

The design language should be:

Minimal

Elegant

Clean

Structured

Balanced

Professional

Whitespace-driven

Content-first

Avoid excessive decoration.

---

## Brand Identity

The Finzo brand should remain consistent across all pages.

Maintain:

Logo placement

Typography

Primary colors

Spacing

Iconography

Illustration style

Never distort the brand identity.

---

## Color System

The color palette should communicate trust and professionalism.

Primary Color

Used for:

Primary Actions

Navigation

Important Highlights

Secondary Color

Used for:

Supporting Actions

Secondary Components

Neutral Colors

Used for:

Backgrounds

Cards

Borders

Typography

Success

Used only for:

Successful operations

Confirmed actions

Completed workflows

Warning

Used only for:

Pending review

Attention required

Expiring actions

Danger

Used only for:

Errors

Critical warnings

Destructive actions

Avoid decorative colors.

---

## Background Standards

Backgrounds should remain clean.

Use:

Light neutral surfaces

Subtle elevation

Soft contrast

Avoid:

Heavy gradients

Animated backgrounds

Pattern overlays

Dark decorative textures

Gaming aesthetics

---

## Typography Philosophy

Typography should prioritize readability.

Hierarchy must remain consistent.

Use:

Page Titles

Section Titles

Card Titles

Body Text

Helper Text

Labels

Captions

Avoid oversized marketing typography.

---

## Typography Standards

Text should remain:

Readable

Consistent

Balanced

Professional

Never use typography purely for decoration.

---

## Spacing System

Spacing should follow one consistent scale.

Every component should use the same spacing rules.

Avoid:

Random spacing

Inconsistent padding

Crowded layouts

Excessive whitespace

---

## Grid System

Layouts should use a unified responsive grid.

Support:

Mobile

Tablet

Laptop

Desktop

Ultra-wide

Every page should align consistently.

---

## Border Radius

Corners should remain subtle.

Avoid:

Sharp inconsistent edges

Extreme rounded corners

Bubble-style interfaces

Consistency is mandatory.

---

## Shadows

Use shadows sparingly.

Purpose:

Visual hierarchy

Elevation

Focus

Avoid:

Large glows

Heavy drop shadows

Neon effects

---

## Buttons

Every button should communicate importance.

Categories:

Primary

Secondary

Outline

Danger

Ghost

Disabled

Loading

Buttons should remain:

Consistent

Accessible

Predictable

Professional

---

## Button Behavior

Every button should provide:

Hover feedback

Focus feedback

Loading state

Disabled state

Pressed state

Avoid flashy animations.

---

## Forms

Forms should maintain:

Consistent labels

Helper text

Validation

Spacing

Grouping

Accessibility

Every form should look identical across modules.

---

## Inputs

Input components should support:

Text

Email

Password

Search

Phone

Date

Number

Select

Textarea

File Upload

States:

Default

Focused

Error

Disabled

Success

---

## Cards

Cards should remain:

Simple

Structured

Readable

Content-first

Avoid decorative card designs.

Cards should support:

Headers

Body

Footer

Actions

Status

---

## Tables

Tables should follow one standard.

Support:

Sorting

Filtering

Pagination

Responsive layout

Status badges

Row actions

Sticky headers where appropriate

---

## Navigation

Navigation should remain identical throughout the platform.

Including:

Header

Sidebar

Breadcrumbs

Footer Navigation

Tabs

Navigation should clearly communicate location.

---

## Sidebar Standards

The sidebar should prioritize:

High-frequency actions

Logical grouping

Role-specific navigation

Collapsed support

Consistent icons

---

## Breadcrumb Standards

Breadcrumbs should:

Reflect hierarchy

Support navigation

Avoid unnecessary depth

---

## Dialogs

Dialogs should remain focused.

Include:

Title

Description

Content

Primary Action

Secondary Action

Close Action

Avoid oversized modal windows.

---

## Notifications

Notification categories:

Success

Information

Warning

Error

Security

System

Notifications should remain concise.

---

## Alerts

Alerts should prioritize:

Security

Errors

Approvals

System Maintenance

Operational Issues

Avoid excessive alert frequency.

---

## Status Indicators

Statuses should combine:

Color

Text

Icon

Never rely solely on color.

---

## Charts

Charts should remain simple.

Support:

Line

Bar

Area

Pie (limited)

Use charts only when data visualization improves understanding.

Avoid decorative analytics.

---

## Icons

Use one icon library across the project.

Icons should:

Remain consistent

Support accessibility

Match enterprise banking style

Avoid mixing icon styles.

---

## Empty States

Every empty state should include:

Meaningful Title

Helpful Description

Suggested Action

Optional Illustration

Avoid blank screens.

---

## Loading States

Loading should include:

Skeletons

Progress Indicators

Button Loading

Table Loading

Avoid unnecessary loading animations.

---

## Error States

Errors should provide:

Clear explanation

Suggested next action

Support guidance

Professional language

Avoid technical jargon.

---

## Success States

Successful operations should reassure users.

Include:

Confirmation

Summary

Reference Information (where appropriate)

Next Action

---

## Micro-Interactions

Allowed:

Hover

Focus

Fade

Button feedback

Loading transitions

Forbidden:

Bounce

Pulse

Glow

Infinite animation

Floating objects

Decorative motion

Micro-interactions should improve usability—not attract attention.

---

## Accessibility

Every component must support:

Keyboard Navigation

Screen Readers

High Contrast

Visible Focus

Semantic HTML

Accessible Labels

WCAG 2.2 AA

Accessibility is part of the design system.

---

## Component Reusability

Every component should be:

Reusable

Documented

Configurable

Scalable

Consistent

Avoid one-off implementations.

---

## Naming Standards

Component names should remain predictable.

Avoid ambiguous naming.

Follow one naming convention throughout the project.

---

## Design Tokens

The design system should centralize:

Colors

Spacing

Typography

Border Radius

Shadows

Transitions

Breakpoints

Z-Index

Future changes should require updating tokens—not individual components.

---

## Component Transformation Requirements (Mandatory)

Before modifying any UI component:

Read the existing implementation completely.

Understand:

Purpose

Behavior

Dependencies

Accessibility

Responsive behavior

Preserve:

Business Logic

Functionality

Accessibility

Responsiveness

Performance

Then improve:

Visual consistency

Readability

Spacing

Hierarchy

Professional appearance

Reusability

Maintainability

Remove:

Inconsistent styles

Duplicate components

Template artifacts

Visual clutter

Cheap effects

Anything that breaks enterprise consistency.

The final UI must appear as one cohesive enterprise banking design system.

---

## Design System Review Checklist

Every component must answer "Yes".

✓ Does it match the enterprise design language?

✓ Is it visually consistent?

✓ Is spacing standardized?

✓ Is typography consistent?

✓ Is accessibility maintained?

✓ Is responsiveness maintained?

✓ Is the component reusable?

✓ Is unnecessary decoration removed?

✓ Is functionality preserved?

✓ Would this component belong in a real enterprise banking application?

If any answer is "No," the component requires redesign.

---

## Chapter Completion Criteria

This chapter is complete only when:

✓ One unified enterprise design language exists.

✓ Color system is standardized.

✓ Typography is standardized.

✓ Spacing system is standardized.

✓ Components are reusable.

✓ Forms, buttons, tables, cards, and navigation follow one standard.

✓ Accessibility is integrated into every component.

✓ Visual consistency exists across the entire project.

✓ No unnecessary visual styles remain.

✓ Every page follows one enterprise banking design system.

## End of **Chapter 19 — Content & Copywriting**
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
