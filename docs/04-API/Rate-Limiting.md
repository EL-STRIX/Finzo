---
title: "Enterprise Rate Limiting"
version: "1.0.0"
category: "Core Specification"
owner: "Architecture Team"
last_updated: "2026-07-22"
---

# Enterprise Rate Limiting

## Overview
To protect the core banking engine from volumetric DDoS, credential stuffing, and abusive scripting, Finzo employs a high-speed Atomic Cache Rate Limiter.

## Thresholds
- **Global API**: 120 requests / 60 seconds per IP.
- **Authentication**: 5 requests / 5 minutes per IP.

## Headers
Responses will return:
- `X-RateLimit-Limit`
- `X-RateLimit-Remaining`
- `X-RateLimit-Reset`

When a threshold is breached, the API throws `429 Too Many Requests`.

