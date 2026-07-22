---
title: "API Versioning Architecture"
version: "1.0.0"
category: "Core Specification"
owner: "Architecture Team"
last_updated: "2026-07-22"
---

# API Versioning Architecture

## Introduction
The Finzo Enterprise API employs a strict URI-based versioning architecture to guarantee absolute backward compatibility for legacy integrators and mobile clients.

## Structure
`https://api.finzo.local/api/v1/{resource}`

- Current Production Version: `v1`
- Deprecation Strategy: Endpoints are supported for a minimum of 24 months post-deprecation.

