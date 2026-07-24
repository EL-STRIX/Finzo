---
title: "Performance Testing Guidelines"
version: "1.0.0"
status: "Active"
owner: "QA Architecture Team"
category: "Specialized Testing"
last_updated: "2026-07-24"
---

# Performance Testing Guidelines

> This document defines the strategies and expectations for monitoring, verifying, and maintaining optimal execution speed and resource efficiency within the Finzo application.

## 1. Purpose and Scope

In a banking environment, slow responses can lead to double-submissions and poor user trust. 
The scope of this document covers **Application-Level Performance Verification**—ensuring PHP scripts and MySQL queries execute efficiently. 
*Note: Large-scale Distributed Load Testing (e.g., handling 10,000 concurrent HTTP connections) is handled at the infrastructure/DevOps level via tools like k6, and is outside the scope of PHPUnit automation.*

## 2. Performance Objectives and Goals

*   **API Response Times:** 95% of all HTTP API requests should execute in under `200ms`.
*   **Query Efficiency:** No single endpoint should execute more than `5` database queries unless explicitly performing batch operations.
*   **Memory Footprint:** PHP processes should not exceed `32MB` of memory usage per standard request lifecycle.

## 3. Database Performance Verification

Database I/O is the primary bottleneck in PHP applications. During testing and development, engineers must monitor:

### 3.1 N+1 Query Prevention
When fetching relational data (e.g., retrieving 10 Users and their Bank Accounts), ensure the `Repository` utilizes efficient `JOIN` statements rather than executing a loop of individual `SELECT` queries.
*   *Verification:* Enable slow query logging in local MySQL (`long_query_time = 1`) to flag unoptimized joins.

### 3.2 Index Utilization
Ensure complex analytical queries (e.g., Admin Audit Logs) leverage appropriate database indices. Queries requiring full table scans (`type: ALL` in MySQL `EXPLAIN`) must be refactored.

## 4. Resource Utilization & Scalability

### 4.1 Memory Usage
*   Do not load massive datasets into PHP memory using `fetchAll()`. 
*   If an Admin needs to export 100,000 transactions to a CSV, the Repository must use **Generators** or chunking to keep the memory footprint stable.

### 4.2 Scalability Considerations
The application is designed statelessly. Sessions and configurations should not be reliant on local disk I/O to ensure the system can scale horizontally across multiple web nodes.

## 5. Performance Monitoring & Reporting

While we do not use PHPUnit to fail a build based on exact millisecond timing (as CI runner speeds vary), developers should actively monitor execution times during manual testing.

*   **Automated Suite Time:** The entire `phpunit` suite of 134+ tests should complete in under `60 seconds` on standard CI hardware. Significant deviations from this baseline indicate a systemic regression (e.g., a missing index or an accidental sleep state).

## 6. Best Practices

*   **Cache Configuration Data:** System configurations fetched during the HTTP lifecycle should leverage rapid-access mechanisms rather than constant database polling.
*   **Optimize Autoloading:** Ensure Composer's Classmap Authoritative mode (`composer dump-autoload -o -a`) is utilized in production/staging environments to remove filesystem `file_exists` overhead.
*   **Limit Payload Sizes:** API endpoints returning lists should enforce strict `LIMIT` and `OFFSET` pagination rules to prevent massive JSON serialization penalties.
