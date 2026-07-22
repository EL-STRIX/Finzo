---
title: "Infrastructure Architecture"
version: "1.0.0"
status: "Active"
owner: "DevOps Engineering"
category: "Cloud Operations"
last_updated: "2026-07-22"
---

# Infrastructure Architecture

## Table of Contents
1. [Executive Summary](#executive-summary)
2. [Infrastructure Overview](#infrastructure-overview)
3. [Hosting Environment](#hosting-environment)
4. [Runtime Environment](#runtime-environment)
5. [Environment Variables](#environment-variables)
6. [Configuration Management](#configuration-management)
7. [Application Configuration](#application-configuration)
8. [File Storage](#file-storage)
9. [Upload System](#upload-system)
10. [Logging](#logging)
11. [Monitoring](#monitoring)
12. [Queue](#queue)
13. [Scheduler](#scheduler)
14. [Notifications](#notifications)
15. [Mail](#mail)
16. [Backup](#backup)
17. [Disaster Recovery](#disaster-recovery)
18. [Scalability](#scalability)
19. [High Availability](#high-availability)
20. [Fault Tolerance](#fault-tolerance)
21. [Performance](#performance)
22. [Security](#security)
23. [CI/CD](#ci-cd)
24. [Deployment](#deployment)
25. [Future Improvements](#future-improvements)
26. [Cross References](#cross-references)
27. [References](#references)
28. [Change Log](#change-log)

## Executive Summary
The Finzo Infrastructure Architecture abstracts the physical and virtual hardware layers away from the application code. It ensures that the PHP 8.x backend and MySQL 8.0 databases operate within a highly available, fault-tolerant, and autoscaling cloud ecosystem.

## Infrastructure Overview
The application is designed to be Cloud-Agnostic, relying on standard POSIX environments and Docker containers rather than proprietary serverless functions. 

## Hosting Environment
Deployed via Kubernetes (EKS/GKE) or traditional load-balanced EC2/Compute Engine instances. The OS is strictly Linux (Ubuntu/Alpine).

## Runtime Environment
- **Web Server**: Nginx handling TLS termination and static asset delivery.
- **Processor**: PHP-FPM 8.x serving as the dynamic execution engine.
- **Cache**: Redis for volatile sessions and queues.
- **Database**: MySQL 8.0.

## Environment Variables
The `.env` file is the sole source of truth for runtime configuration. It is NEVER committed to version control. Keys are injected via Kubernetes Secrets or AWS Parameter Store during deployment.

## Configuration Management
Configurations are parsed during the application bootstrap. To prevent continuous disk I/O, configuration arrays are cached into PHP opcache memory on production environments.

## Application Configuration
Controlled via `config/app.php` and `config/database.php`.

## File Storage
Files are never stored inside the Docker container's ephemeral filesystem.

## Upload System
KYC Documents and user avatars are streamed directly to Object Storage (AWS S3) to guarantee persistence across container restarts.

## Logging
Logs are structured as JSON arrays and piped to `stdout`. From there, Fluentd or Logstash captures and ships them to a centralized ELK (Elasticsearch, Logstash, Kibana) stack.

## Monitoring
Datadog or New Relic APM agents are injected into the PHP-FPM container to track memory usage, SQL query latency, and HTTP 500 error spikes.

## Queue
Heavy jobs (Email dispatch, PDF generation) are serialized and pushed to a Redis Queue. 

## Scheduler
A centralized cron job (`* * * * * php artisan schedule:run`) invokes the internal PHP scheduler, which distributes tasks like "Nightly Interest Calculation" to the background queue workers.

## Notifications
Pushed asynchronously via WebSockets (if configured) or stored in the database for client-side polling.

## Mail
Dispatched via an external SMTP relay (e.g., SendGrid, Amazon SES) using an asynchronous queue job.

## Backup
Databases run continuous binary log backups. File Storage buckets are versioned to prevent accidental deletion.

## Disaster Recovery
Recovery Time Objective (RTO) is 15 minutes. Recovery Point Objective (RPO) is 5 minutes. Cross-region failover is tested quarterly.

## Scalability
The Nginx and PHP-FPM containers sit behind an Application Load Balancer. As CPU utilization crosses 70%, the Autoscaling Group spawns additional containers.

## High Availability
The architecture requires deployment across at least 3 Availability Zones (AZs) to survive a datacenter-level outage.

## Fault Tolerance
If the Redis cache dies, the application gracefully degrades to querying the MySQL database directly.

## Performance
CDN (Cloudflare/CloudFront) caches all static frontend assets at the Edge, ensuring the application servers only process dynamic API queries.

## Security
- All internal communication is restricted via VPC subnets and Security Groups.
- External ingress is protected by a WAF (Web Application Firewall) to block SQLi and XSS signatures before they reach Nginx.

## CI/CD
GitHub Actions automates the pipeline.
1. Linting (PHPCS).
2. Unit Testing (PHPUnit).
3. Docker Image Build.
4. Image Push to ECR.
5. Rolling Update to Kubernetes.

## Deployment
Blue/Green deployment strategy guarantees zero-downtime upgrades. The Load Balancer only switches traffic to the "Green" environment once the health-check endpoint (`/api/health`) returns `200 OK`.


### Deep Dive: Container Ephemerality
The Finzo infrastructure operates under the strict assumption that any server or container may be destroyed at any moment without warning.
- **Rule**: Local disk writes (other than temporary cache files) are completely forbidden. If a user uploads a document, and the container dies 2 seconds later, the document must not be lost.
- **Implementation**: The backend utilizes Flysystem (or equivalent abstraction) to ensure all file writes are streamed synchronously to an external S3 bucket before the API returns a `200 OK` response to the user.

### Queue Worker Topology
Background processing is separated from the web processing tier.
- While the Web Tier auto-scales based on HTTP request volume, the Queue Worker Tier auto-scales based on Queue Depth (the number of pending jobs in Redis).
- This ensures that a massive influx of end-of-month PDF generation jobs does not consume CPU cycles required by the web servers to serve the user dashboard.

### VPC and Network Segmentation
The infrastructure is heavily segmented to prevent lateral movement in the event of a breach.
- **Public Subnet**: Contains only the Application Load Balancers and NAT Gateways.
- **Private Subnet**: Contains the Nginx and PHP-FPM containers. These instances have NO public IP addresses. They can reach the internet via the NAT Gateway, but the internet cannot reach them directly.
- **Data Subnet**: Contains the MySQL Database and Redis Cache. This subnet has zero internet access route. It can only be accessed by IP ranges originating from the Private Subnet.

### Zero-Downtime Database Migrations in CI/CD
Deploying code is easy; deploying database schema changes without breaking the live application is difficult.
- The CI/CD pipeline executes database migrations *before* shifting traffic to the new code.
- Therefore, all schema changes must be backward compatible. (e.g., If renaming a column, the migration must add the new column, the code must write to both, and the old column can only be dropped in a *subsequent* deployment weeks later).
- This guarantees the old code running on the active containers does not crash while the new containers are booting up.

## Future Improvements
Implement Infrastructure-as-Code (IaC) strictly using Terraform to prevent manual drift in AWS environments.

## Cross References
- [Backend Architecture](../Backend-Architecture.md)
- [API Architecture](../API-Architecture.md)

## References
- The Twelve-Factor App
- Site Reliability Engineering (Google)

## Change Log
| Version | Date | Author | Description |
|---|---|---|---|
| 1.0.0 | 2026-07-22 | DevOps Team | Phase 7 Infrastructure Architecture |

### Kubernetes Cluster Architecture
The Finzo application is orchestrated via Kubernetes to ensure declarative state management and automated self-healing.
- **Ingress Controller**: An Nginx Ingress Controller handles incoming internet traffic, performing TLS termination and routing requests to the appropriate internal Service based on the host header.
- **Deployment Strategy**: PHP-FPM and Nginx are packaged as independent containers but deployed together within the same Kubernetes Pod, ensuring they share the same localized network namespace and can communicate over 127.0.0.1:9000.
- **Horizontal Pod Autoscaler (HPA)**: The HPA monitors CPU utilization. If average CPU exceeds 75% across the ReplicaSet, the HPA dynamically provisions additional Pods.

### External Secrets Management
Storing database passwords in a plaintext .env file within the Docker container is a critical security vulnerability.
- Finzo utilizes AWS Secrets Manager or HashiCorp Vault.
- The Kubernetes cluster uses an External Secrets Operator to sync the secure strings from Vault directly into native Kubernetes Secrets.
- These Secrets are then injected as Environment Variables into the Pod *only* at runtime, ensuring the cryptographic keys are never written to disk or baked into the Docker Image.

### Disaster Recovery: Cross-Region Failover
If the primary AWS Region (us-east-1) suffers a catastrophic outage:
- Route53 (DNS) performs a health check on the primary API Gateway.
- Upon detecting 3 consecutive failures, Route53 automatically shifts traffic to the standby region (us-west-2).
- The standby region is pre-warmed. Its database is an asynchronous read-replica of the primary. Upon failover, an automated Lambda script promotes the replica to Primary, allowing write operations to resume within an RTO of 5 minutes.

### Log Aggregation and Retention
Logs are crucial for post-mortem forensics and financial auditing.
- Container logs are harvested by Fluent-Bit running as a DaemonSet on every Kubernetes Node.
- The logs are shipped to Elasticsearch.
- To manage storage costs, Elasticsearch implements an Index Lifecycle Management (ILM) policy:
  - **Hot Phase**: Logs < 7 days old remain in high-speed RAM/SSD for immediate Kibana searching.
  - **Warm Phase**: Logs 7-30 days old are moved to cheaper disk storage.
  - **Cold Phase**: Logs > 30 days old are compressed and shipped to AWS S3 Glacier, retained for 7 years to satisfy regulatory compliance requirements.

### Content Delivery Network (CDN) Architecture
To reduce load on the origin servers and protect against volumetric DDOS:
- Cloudflare sits in front of the primary load balancer.
- Cloudflare aggressively caches all static assets (.js, .css, .webp) at Edge Nodes worldwide.
- Cloudflare's WAF (Web Application Firewall) intercepts malicious payloads, dropping SQL Injection and Cross-Site Scripting attempts before they traverse the Atlantic cable to the origin servers.


### Zero-Trust Network Architecture (ZTNA)
Historically, infrastructure relied on VPNs and firewall perimeters. Finzo assumes the perimeter is already compromised.
- Every service must mutually authenticate every other service.
- If the PHP-FPM container attempts to communicate with the Redis container, the communication is encrypted via TLS (mTLS - Mutual TLS).
- This is orchestrated by a Service Mesh (like Istio or Linkerd) running sidecar proxies next to every Kubernetes Pod.
- A compromised web container cannot simply scan the internal network; the Service Mesh strictly enforces network policies that deny all traffic not explicitly allowed by configuration.

### Immutable Infrastructure and Patching
Server maintenance (SSHing into a box to run pt-get upgrade) is an anti-pattern.
- Finzo infrastructure is Immutable.
- When an OS-level vulnerability (e.g., OpenSSL CVE) is detected, the DevOps team patches the base Dockerfile.
- The CI/CD pipeline builds a completely new Docker Image, runs regression tests, and deploys the new image.
- The old containers are destroyed. At no point is a live server ever modified in place, eliminating configuration drift and ensuring identical behavior across Staging and Production.

### Database Connection Pooling at the Infrastructure Layer
While PHP utilizes persistent connections, a sudden spike of 10,000 HTTP requests would exhaust MySQL's connection limit.
- Infrastructure provides **ProxySQL** deployed as a highly available cluster.
- The PHP Application points its database connection string to ProxySQL, not MySQL.
- ProxySQL multiplexes the 10,000 PHP connections down into 50 highly efficient, long-lived TCP connections to the MySQL Primary.
- Furthermore, ProxySQL is configured with query-routing rules: it intercepts any SELECT query not wrapped in a FOR UPDATE transaction and silently redirects it to the nearest AWS Aurora Read Replica.

### Secrets Rotation Automation
To minimize the blast radius of a compromised credential:
- Database passwords and 3rd party API keys are automatically rotated every 30 days.
- An AWS Lambda function connects to the MySQL instance, alters the user's password to a new random string, and updates the AWS Secrets Manager.
- The Kubernetes External Secrets Operator detects the change and triggers a rolling restart of the PHP Pods to ingest the new credentials, executing a complete credential rotation with zero application downtime and zero human intervention.


### DDoS Mitigation at the Infrastructure Edge
A banking application is a prime target for Layer 3/4 and Layer 7 Distributed Denial of Service (DDoS) attacks.
- **Layer 3/4 (Network/Transport)**: AWS Shield Advanced (or Cloudflare Magic Transit) automatically detects and mitigates UDP reflection and SYN floods before they even reach the Application Load Balancer.
- **Layer 7 (Application)**: The AWS Web Application Firewall (WAF) inspects every incoming HTTP request. Rate limiting rules are applied here (e.g., blocking IPs that generate more than 1000 requests in 5 minutes). Furthermore, rules block requests originating from known malicious Tor exit nodes or specific geographic regions explicitly embargoed by financial compliance regulations (OFAC sanctions).

### Container Orchestration Resiliency (Pod Disruption Budgets)
During routine cluster upgrades (e.g., upgrading the Kubernetes version), nodes must be cordoned and drained.
- To prevent an outage, Finzo configures strict Pod Disruption Budgets (PDB).
- The PDB mandates that at least 66% of the PHP-FPM pods must be healthy and active at any given time.
- If a cluster administrator attempts to drain a node that would violate this budget, Kubernetes intercepts and denies the action until new replica pods have successfully spun up on a different, healthy node and passed their readiness probes.

### Advanced Observability and Alerting
Waiting for user complaints to detect an outage is unacceptable.
- Infrastructure utilizes Prometheus to scrape metrics from the Nginx Ingress, the PHP-FPM status page, and the MySQL exporter.
- These metrics are visualized in Grafana dashboards.
- Alertmanager is configured with strict Service Level Objectives (SLOs).
- If the 99th percentile (p99) latency of the /api/v1/transfer endpoint exceeds 200ms for more than 2 consecutive minutes, or if the HTTP 500 error rate exceeds 1%, an automated PagerDuty alert is instantly fired to the on-call Site Reliability Engineer (SRE), complete with direct links to the relevant log queries.

### Automated Infrastructure Provisioning (Terraform)
Clicking through a cloud provider's web console to create servers leads to irreproducible 'snowflake' environments.
- 100% of the Finzo infrastructure—VPCs, Subnets, IAM Roles, EKS Clusters, RDS Databases, and S3 Buckets—is defined declaratively in Terraform (HCL).
- When a change to the infrastructure is required (e.g., increasing the minimum size of the Auto-Scaling Group), the change is submitted as a Pull Request to the inzo-infrastructure repository.
- A GitHub Action runs 	erraform plan to output the exact changes that will occur. Once approved and merged, 	erraform apply executes the structural modifications automatically.


### Blue/Green Deployments with Database Compatibility
A true zero-downtime deployment requires the infrastructure to route traffic intelligently during the switchover phase.
- **Blue Environment**: The current live production pods serving traffic.
- **Green Environment**: The new pods spun up by the CI/CD pipeline containing the updated application code.
- Traffic is not shifted to the Green environment until the readiness probes succeed. 
- However, if the Green environment contains code that expects a new database column, the database migration MUST run *before* the Green pods boot. This creates a brief window where the Blue (old) code is running against the New schema.
- Therefore, the infrastructure architecture dictates a strict engineering rule: **All database migrations must be purely additive and backward compatible.** (e.g., No dropping columns, no renaming columns without a 2-step deprecation phase). 

### Elastic Block Store (EBS) and Stateful Sets
While the PHP containers are entirely stateless, certain infrastructure components require durable storage that survives container restarts.
- If Redis is deployed within Kubernetes (rather than using a managed service like AWS ElastiCache), it cannot use an ephemeral volume.
- Infrastructure defines Redis as a StatefulSet.
- Kubernetes dynamically provisions an AWS EBS volume (or persistent disk) and attaches it exclusively to the Redis pod. If the Pod is evicted and rescheduled on a different Node, Kubernetes automatically detaches the EBS volume from the dead node and reattaches it to the new node, ensuring the queue data and session cache survive the eviction.

### Secrets Encryption at Rest (KMS)
The Kubernetes Secrets holding the .env variables are base64 encoded by default, which provides zero cryptographic security.
- To secure the infrastructure, the Kubernetes etcd database (which stores the cluster state) is configured with an EncryptionProvider.
- It delegates the encryption of the Secrets to an external Key Management Service (AWS KMS or Google Cloud KMS).
- Thus, even if an attacker gains physical access to the raw etcd disk volumes, the secrets remain heavily encrypted and unreadable without the IAM permissions required to execute a KMS decryption call.


### Service Mesh and Traffic Observability
As Finzo scales horizontally across multiple Kubernetes nodes, observing exactly how traffic flows between containers becomes impossible with traditional networking tools.
- Finzo implements a Service Mesh (e.g., Istio) to intercept all incoming and outgoing TCP/HTTP traffic at the Pod level.
- The Envoy sidecar proxy injects telemetry headers (x-b3-traceid) and exports metrics automatically to Prometheus.
- This allows DevOps to visualize the entire infrastructure architecture dynamically in Kiali, mapping precisely which microservice or container is communicating with the database, and identifying any bottlenecks where the p99 latency exceeds acceptable SLA thresholds (e.g., > 150ms).

### Ephemeral Environments for CI/CD Testing
Running end-to-end (E2E) tests against a static Staging server causes pipeline bottlenecks when multiple developers merge Pull Requests simultaneously.
- Finzo's Infrastructure-as-Code (Terraform/Helm) supports the dynamic generation of Ephemeral Environments.
- When a PR is opened, a GitHub Action provisions a completely isolated, miniaturized version of the entire Kubernetes namespace (PHP, Nginx, Redis, MySQL).
- Integration tests run against this ephemeral stack. Once the tests pass and the PR is merged, the namespace is instantly destroyed, ensuring clean tests without state pollution and minimizing AWS cloud compute costs.

### WAF Rules and Threat Intelligence Integration
The Web Application Firewall (WAF) must adapt to zero-day vulnerabilities faster than the DevOps team can manually write Regex rules.
- Finzo utilizes Managed Rule Groups provided by the Cloud Vendor (e.g., AWS Managed Rules for Core Rule Set, SQLi, and PHP specific vulnerabilities).
- Additionally, the WAF is integrated with threat intelligence feeds. If an IP address is flagged globally as part of a botnet participating in credential stuffing attacks on other financial institutions, that IP is automatically added to a dynamic Deny List at the WAF level, dropping their packets before they ever consume CPU cycles on the Application Load Balancer.


### Auto-Scaling Database Read Replicas
Handling massive spikes in traffic (e.g., during end-of-month payroll processing) requires elastic databases.
- The infrastructure is configured to monitor the CPU and Connection Count of the primary MySQL Database and its active Read Replicas.
- If the average CPU load across the Replicas exceeds 70%, the infrastructure (via AWS RDS Auto Scaling or equivalent Operator) automatically spins up an additional Read Replica node.
- The new node synchronously pulls the binlogs, catches up to the primary within minutes, and is automatically added to the ProxySQL connection pool to begin serving read queries, guaranteeing the dashboard remains highly responsive during extreme load events.


### Secrets Injection and Dynamic Key Rotation
Managing database passwords inside environment variables requires extreme security measures.
- Infrastructure utilizes an operator (like AWS Secrets Manager) to rotate the MySQL passwords dynamically every 30 days without manual intervention.
- The external operator connects directly to the DB, alters the password string, and updates the secret.
- The Kubernetes cluster detects the modified secret, cordons the old PHP pods, and executes a rolling deployment of new pods instantly provisioned with the updated credentials.
- This guarantees a zero-downtime key rotation mechanism while preventing stale credentials from lingering in memory.

