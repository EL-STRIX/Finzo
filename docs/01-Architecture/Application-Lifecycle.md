---
title: "Application Lifecycle"
version: "1.0.0"
status: "Active"
owner: "Architecture Team"
category: "Lifecycle"
last_updated: "2026-07-22"
---

# Application Lifecycle

## Table of Contents
1. [Bootstrap Flow](#bootstrap-flow)
2. [Termination](#termination)

## Bootstrap Flow
1. Network ingress at `public/index.php`.
2. Composer `autoload.php` initializes class maps.
3. `config/bootstrap.php` prepares environment variables and DB connectors.
4. `Router` parses request URI and initiates the middleware execution stack.

## Termination
Upon sending the JSON payload back to the client, PHP gracefully dismantles the execution space, ensuring memory is strictly garbage collected (preventing enterprise memory leaks).