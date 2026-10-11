# SIREKA ASSI Production and Scaling Checklist

Target server: Rocky Linux 9, NGINX, PHP-FPM, Laravel, and MySQL.

Keep MySQL as the primary operational database. Scale based on measured workload and availability requirements, not row count alone.

For building outages, off-site standby architecture, and failover procedures, see the [redundancy and disaster recovery guide](./REDUNDANCY-AND-DISASTER-RECOVERY.md).

## Recommended Starting Architecture

```text
Users -> HTTPS -> NGINX -> PHP-FPM -> Laravel -> MySQL
                                      |-- Queue workers
                                      |-- Scheduler
                                      `-- Private file storage
```

One server can initially host these services if capacity and availability requirements allow it. Keep database ports inaccessible from the public internet.

## Do Now: Before Production

### 1. Rocky Linux 9 and Network

- [ ] Use supported PHP and MySQL versions compatible with the installed Laravel version.
- [ ] Establish a regular OS/package security-update schedule.
- [ ] Keep SELinux enforcing; configure appropriate contexts and permissions instead of disabling it.
- [ ] Configure `firewalld`: expose only HTTPS, HTTP for redirects/certificate renewal if needed, and restricted SSH.
- [ ] Restrict SSH to VPN or approved IPs where practical.
- [ ] Use SSH keys; disable direct root login and password login after verifying alternative access.
- [ ] Run application services with dedicated, least-privileged accounts.
- [ ] Enable time synchronization; store timestamps consistently, preferably UTC, and display Indonesian dates/WIB in the application.

### 2. NGINX and PHP-FPM

- [ ] Set the NGINX document root to Laravel's [public/](./public) directory, not the repository root.
- [ ] Prevent access to dotfiles, environment files, repository metadata, and private storage.
- [ ] Restrict PHP execution to the intended application entry point; do not allow arbitrary uploaded PHP files to execute.
- [ ] Configure HTTPS with automated certificate renewal and expiry monitoring.
- [ ] Redirect HTTP to HTTPS. Enable HSTS only after verifying HTTPS works for all affected hosts.
- [ ] Configure upload size limits consistently across NGINX, PHP, and Laravel validation.
- [ ] Configure request timeouts deliberately; move long processing to queues instead of increasing timeouts indefinitely.
- [ ] Size PHP-FPM workers using measured worker memory and available RAM, leaving capacity for MySQL, the OS, and queue workers.
- [ ] Enable production OPcache and ensure deployments refresh it correctly.
- [ ] Protect NGINX/PHP-FPM status endpoints from public access.
- [ ] Allow Laravel writes only where necessary, principally [storage/](./storage) and [bootstrap/cache/](./bootstrap/cache). Never use `chmod 777`.

### 3. Laravel Production Configuration

- [ ] Set `APP_ENV=production`, `APP_DEBUG=false`, and the correct HTTPS `APP_URL`.
- [ ] Keep secrets outside Git and protect environment-file permissions.
- [ ] Preserve the application encryption key across deployments; back it up securely.
- [ ] Configure secure session cookies and suitable session expiration.
- [ ] Configure trusted proxies correctly if using a reverse proxy or load balancer.
- [ ] Rate-limit login and expensive endpoints without blocking legitimate shared-network users.
- [ ] Enforce authorization on every resource access, including downloads. UUIDs do not replace authorization.
- [ ] Store uploads privately and serve them through authorized endpoints.
- [ ] Validate upload type and size; consider malware scanning for document uploads.
- [ ] Record required audit events without storing passwords, tokens, or unnecessary sensitive payloads.

### 4. Deployment and Background Processing

- [ ] Use a repeatable deployment process, not manual edits on the live server.
- [ ] Install production dependencies and build frontend assets during deployment.
- [ ] Apply appropriate Laravel configuration, route, and view caches.
- [ ] Run migrations through a controlled process with a recovery plan.
- [ ] Run queue workers under systemd or Supervisor, with automatic restart.
- [ ] Configure queue timeouts, retry limits, and failed-job alerts. Make retryable jobs safe to execute more than once.
- [ ] Run Laravel's scheduler automatically.
- [ ] Restart long-lived workers after deployment so they use the new code.
- [ ] Keep a known-good release available for rollback; account for database compatibility.
- [ ] Do not use `composer dev`, `php artisan serve`, or the Vite development server in production.

### 5. MySQL and Application Data Access

- [ ] Use InnoDB with foreign keys and appropriate unique constraints.
- [ ] Restrict MySQL to localhost or a private network.
- [ ] Use a dedicated application database account with only necessary privileges; separate migration privileges where practical.
- [ ] Add indexes for actual filters, joins, and sorting patterns.
- [ ] Enable controlled slow-query monitoring; protect logs because query text can contain sensitive data.
- [ ] Use server-side pagination and filtering for growing lists.
- [ ] Avoid N+1 queries and fetching unnecessary columns.
- [ ] Process imports and exports in bounded batches through queues.
- [ ] Keep money and precise quantities in `DECIMAL` columns.
- [ ] Avoid storing growing collections of jobs/materials in one large JSON document.

### 6. Backups and Recovery: Highest Priority

- [ ] Agree on recovery point objective (RPO): how much recent data loss is acceptable.
- [ ] Agree on recovery time objective (RTO): how long the application may remain unavailable.
- [ ] Automate database and private-upload backups.
- [ ] Enable binlogs and point-in-time recovery if required by your RPO.
- [ ] Encrypt backups and keep an off-server copy with separate access controls.
- [ ] Protect backups from deletion by a compromised application/server, using immutable storage where available.
- [ ] Back up encryption keys and essential configuration securely.
- [ ] Test a full restore before launch and regularly afterward.
- [ ] Document how database records and uploaded files are restored consistently.

**A successful backup job is not proof that recovery works. A tested restore is.**

## Do Now: Monitoring

- [ ] Monitor external availability and HTTPS certificate expiry.
- [ ] Monitor response-time percentiles, especially p95, and error rates.
- [ ] Monitor CPU, RAM, swap activity, disk latency, free space, and inodes.
- [ ] Monitor PHP-FPM busy workers and request backlog.
- [ ] Monitor MySQL connections, slow queries, lock waits, and deadlocks.
- [ ] Monitor queue age/backlog, failed jobs, and scheduler execution.
- [ ] Rotate logs and define retention so logs cannot fill the server.
- [ ] Alert on failed backups and approaching disk exhaustion.
- [ ] Ensure logs and error reporting do not expose employee or ship-repair information.
- [ ] Assign an owner and response procedure for each actionable alert.

## Next: As Usage Grows

| Observed problem | Action |
|---|---|
| Slow pages with high rows scanned | Profile SQL, fix indexes and query shape first |
| PHP-FPM backlog | Reduce request work; tune worker capacity within RAM limits |
| Reports exhaust memory or time out | Queue them, batch processing, and use summary tables |
| Queue delays affect important jobs | Separate critical and heavy queues; allocate dedicated workers |
| Frequent reads repeat expensive work | Introduce caching with explicit invalidation rules |
| One host experiences CPU/RAM/I/O contention | Separate database or heavy workers from the web server |
| Read traffic remains a database bottleneck | Consider a read replica, accounting for replication lag |
| Downtime becomes unacceptable | Add redundancy and test failover; another web node alone is insufficient |

- [ ] Load-test representative workflows using realistically sized synthetic or anonymized data.
- [ ] Define measurable response-time, error-rate, and queue-delay targets.
- [ ] Review query plans after significant data growth.
- [ ] Establish retention rules for audit logs, historical records, exports, and temporary uploads.
- [ ] If introducing Redis, keep it private and configure durability appropriately for sessions/queues. A queue is not merely disposable cache.

## Future: Multiple Web Servers or Heavy Analytics

### Multiple Web Servers

- [ ] Place web nodes behind a load balancer with health checks.
- [ ] Use shared session storage.
- [ ] Use shared private attachment storage or private object storage.
- [ ] Use shared cache/locks for coordination.
- [ ] Prevent duplicate scheduled tasks where only one execution is intended.
- [ ] Deploy consistent application versions, configuration, and encryption keys.
- [ ] Plan database availability and recovery separately from web-server redundancy.

### Large Historical and Reporting Workloads

- [ ] Introduce a separate reporting system when analytics measurably interfere with operational work.
- [ ] Consider partitioning only when queries and retention operations justify it.
- [ ] Consider PostgreSQL for specific capabilities, not simply because tables exceed one million rows.
- [ ] Consider an analytical engine for large scan-heavy aggregations while retaining MySQL for operational transactions.

## Priority Order

1. Restore-tested backups.
2. Secure server configuration.
3. Reliable deployments and workers.
4. Monitoring.
5. Query optimization.
6. Infrastructure expansion.

Do not wait for a million rows to implement the first four.
