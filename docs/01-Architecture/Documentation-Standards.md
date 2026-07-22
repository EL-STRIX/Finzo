---
title: "Documentation Standards"
version: "1.0.0"
status: "Active"
owner: "Architecture Team"
category: "Standards"
last_updated: "2026-07-22"
---

# Documentation Standards

## Table of Contents
1. [Heading Hierarchy](#heading-hierarchy)
2. [Markdown Formatting Rules](#markdown-formatting-rules)
3. [Table and Diagram Style](#table-and-diagram-style)
4. [Metadata Format](#metadata-format)
5. [Code and Note Blocks](#code-and-note-blocks)
6. [Navigation & Cross-References](#navigation--cross-references)

## Heading Hierarchy
- `#` H1 for Document Title
- `##` H2 for major sections (Table of Contents, Purpose, Scope)
- `###` H3 for sub-sections

## Markdown Formatting Rules
- Use bold `**bold**` for emphasizing components.
- Use inline code `` `code` `` for class names, files, and variables.

## Table and Diagram Style
- Use standard GitHub Flavored Markdown (GFM) tables.
- Use `mermaid` fenced code blocks for all architecture diagrams.

## Metadata Format
Every document MUST begin with YAML frontmatter containing: `title`, `version`, `status`, `owner`, `category`, and `last_updated`.

## Code and Note Blocks
Use GitHub syntax for alerts:
> [!NOTE]
> For general notes.

> [!WARNING]
> For critical architecture warnings.

> [!TIP]
> For best practices.

## Navigation & Cross-References
All documents must contain a `Navigation` section linking to Parent, Child, Previous, and Next documents, alongside a `Change Log` at the end of the document.

## Change Log
| Version | Date | Author | Description |
|---|---|---|---|
| 1.0.0 | 2026-07-22 | Architecture Team | Initial Creation |
