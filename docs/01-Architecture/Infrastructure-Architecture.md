---
title: "Infrastructure Architecture"
version: "1.0.0"
status: "Active"
owner: "DevOps Team"
category: "Infrastructure"
last_updated: "2026-07-22"
---

# Infrastructure Architecture

## Table of Contents
1. [Overview](#overview)
2. [Hosting Model](#hosting-model)
3. [Database Design](#database-design)
4. [File Storage](#file-storage)
5. [Monitoring](#monitoring)

## Overview
This document specifies the physical and logical layout of servers, databases, and persistent volumes driving the application.

## Hosting Model
The application is designed for standard containerized deployments (Docker) or bare-metal Linux servers running an Nginx + PHP-FPM stack.

## Database Design
- **MySQL 8.0**: Operates as the primary persistent datastore.
- **Atomic File System**: Acts as a pseudo-Redis layer for caching `Settings` and rate limits, bypassing heavy DB I/O for static configurations.

## File Storage
User-uploaded KYC documents and Branch Certifications are vaulted securely outside the web root (`public/`) to prevent direct URL enumeration. File access is mediated through the API.

## Monitoring
- APM tracing is supported natively via telemetry headers (`X-Runtime`, `X-Memory`) injected by the `ResponseBuilder`.
- Uncaught exceptions are piped to server `error_log` alongside exact request URIs for SIEM ingestion.
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
