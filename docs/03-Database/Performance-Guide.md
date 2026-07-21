# Finzo Database Monitoring, Observability & Operational Excellence

## Version 1.0 - Phase 12 Enterprise Architecture

This document describes the observability and health monitoring architecture of the Finzo database.

## 1. Health Monitoring & Observability API
The system provides a RESTful health check endpoint specifically designed for CI/CD runners, Kubernetes Probes, and Load Balancers.

**Endpoint:** `GET /public/api/health.php`

**Responses:**
- `200 OK`: Database is healthy, ledger is balanced, storage is within limits.
- `503 Service Unavailable`: Database is offline or Ledger Integrity is fundamentally compromised (unbalanced journals).

## 2. CLI Monitor (Operational Support)
Administrators can run the real-time CLI monitor to instantly view database performance metrics without executing manual SQL.

```bash
php bin/finzo monitor
```

## 3. Database Observability Views
To support future BI Dashboards and avoid repetitive analytical queries in the PHP backend, the following strictly optimized views are deployed:
- `vw_monitoring_storage`: Monitors table sizes, fragmentation, and remaining capacity in MB.
- `vw_monitoring_ledger_health`: Specifically audits the double-entry accounting engine to detect unbalanced journals in real-time.
- `vw_monitoring_operational_metrics`: Tracks daily transaction volumes and success/failure ratios.

## 4. Alerting Architecture
The `monitoring_alerts` table acts as a centralized configuration engine for operational thresholds:
- `max_storage_mb`
- `max_query_time_seconds`
- `unbalanced_journals_allowed`

These thresholds allow the API and CLI layers to emit `warning` or `critical` alerts dynamically when operational stability is threatened.
