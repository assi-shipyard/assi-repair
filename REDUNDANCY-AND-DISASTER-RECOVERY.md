# SIREKA ASSI Redundancy and Disaster Recovery Guide

This guide explains how to keep the application available when a server, internet connection, or entire building becomes unavailable.

It complements the [production and scaling checklist](./PRODUCTION-SCALING-CHECKLIST.md). It is a planning guide, not confirmation that replication, backups, or failover are already configured. Exact configurations depend on the installed software versions, network topology, and approved hosting provider.

## 1. The Main Risk in the Current Arrangement

The production server is in the office building, but people access it over the internet, including through mobile data.

```text
Mobile user -> Internet -> Building internet connection -> Office server
Office user -------------------------------------------> Office server
```

Mobile data keeps the user's phone connected. It does not keep the office server connected.

Examples:

- If the building loses internet access, outside users cannot reach the office server.
- If the building loses power, the server and network equipment can stop.
- If the server fails, a working internet connection does not help.
- If files are accidentally deleted, a second server may receive the same deletion.

**A second server in the same building protects against some hardware failures, but not loss of the building itself.**

## 2. Important Terms in Plain Language

| Term | Meaning | Example |
|---|---|---|
| Redundancy | Another component can replace a failed component | A second application server |
| High availability (HA) | A design that reduces service interruption | Healthy servers continue serving requests when one fails |
| Disaster recovery (DR) | A procedure to restore service after a major incident | Run the application elsewhere after losing the office |
| Failure domain | Components that can fail together | Servers sharing one building, power supply, or network |
| Primary | The active database accepting business writes | Users save project updates here |
| Replica | A database receiving changes from the primary | A standby copy receives project updates |
| Failover | Move service to a replacement system | Promote the standby and route users to it |
| Failback | Move service back after recovery | Return to the repaired site after synchronization |
| Fencing | Prevent the old primary from accepting writes | Disable its application and database write access through a verified control |
| Split-brain | Two systems both believe they are the writable primary | Office and standby accept conflicting project updates |
| RPO | Maximum acceptable recent data loss | Losing at most five minutes of changes |
| RTO | Target time to restore usable service | Recovering within one hour |

Replication helps availability. Backups help recover older, correct data. You need both.

## 3. Choose Targets Before Buying Equipment

Agree on these decisions with management:

- [ ] How long can login, project updates, and docking workflows be unavailable?
- [ ] How much recent data loss is acceptable?
- [ ] Do uploaded documents require the same recovery targets as database records?
- [ ] Must office users continue working when the office internet connection fails?
- [ ] Is off-site hosting allowed for employee and ship-repair information?
- [ ] Which data locations and providers are approved?
- [ ] Who can authorize failover and accept potential data loss?
- [ ] Who responds outside office hours?

Illustrative planning profiles, not guarantees:

| Profile | Example objective | Likely approach |
|---|---|---|
| Basic recovery | Several hours of downtime acceptable | Off-site backups and a tested rebuild procedure |
| Warm standby | Recovery within about an hour; small recent data loss acceptable | Preconfigured off-site system with database and file replication |
| High availability | Short interruption and stricter data-loss requirements | Redundant services, reliable failure detection, fencing, and tested automated failover |

Measure actual recovery in drills. Do not promise an RTO based only on a provider's marketing or a server being powered on.

An asynchronous replica can lag, especially during network outages. It cannot guarantee zero data loss. Stronger commit guarantees require a suitable database design and may increase latency or stop writes when connectivity is lost.

## 4. Architecture Choices

### Option A: Primary Production Outside the Building

If organizational policy permits it, this is usually the simplest way to remove the office building from the public application's critical path.

```text
Office users -- Internet --+
                          +-> Public HTTPS -> Hosted application
Mobile users -- Internet -+                       |
                                                  +-> Private MySQL
                                                  +-> Private attachments
                                                  +-> Queue and scheduler

Separate failure domain -> Protected backups and recovery environment
```

Advantages:

- Office power loss does not stop mobile users from accessing the application.
- Office internet loss does not stop users on other networks.
- Power, cooling, and network connectivity are handled by the hosting facility.

Limitations:

- Office users still need working internet access to reach the hosted application.
- A single hosted VM remains a single point of failure.
- A provider outage can still affect the application.
- Provider snapshots alone are not a complete backup strategy.

Start with reliable hosting, isolated backups, monitoring, and a tested recovery environment. Add redundant application/database services when required by the agreed availability target.

### Option B: Office Primary With Off-Site Warm Standby

Use this when the production primary must remain in the building.

```text
Users -> Public HTTPS endpoint -> Office application -> Office MySQL
                                      |                   |
                              Private file sync    Encrypted replication
                                      |                   |
                                      v                   v
                                Off-site files      Standby MySQL
                                      \                   /
                                       Standby application

During an incident: fence old primary -> promote standby -> switch traffic
```

The standby has a compatible application release, configuration, database copy, and private attachments ready. Normally, it does not accept business writes or run competing business jobs.

Advantages:

- Provides a recovery location outside the building.
- Usually simpler than operating two writable sites.
- Can be expanded gradually.

Limitations:

- Requires replication monitoring and regular testing.
- Recent transactions or files may be missing at failover.
- Safe promotion may need an operator.
- Standby capacity must be enough for the agreed recovery workload.

### Option C: Two Servers in the Office

Useful as an additional layer, but not a replacement for off-site recovery.

Two office servers can protect against one server failing. They still share building risks. A single router, switch, storage device, or load balancer can also remain a single point of failure.

**Recommendation:** choose Option A if permitted. Otherwise choose Option B. Add Option C only when it solves a measured local availability need.

## 5. Power and Internet Redundancy at the Office

### Power

- [ ] Protect servers, switches, routers, and ISP equipment with a correctly sized UPS.
- [ ] Measure runtime under actual load, not only the UPS nameplate rating.
- [ ] Configure graceful shutdown before batteries are exhausted.
- [ ] Test batteries and replace them according to condition and manufacturer guidance.
- [ ] Use generator support if service must continue through longer outages.
- [ ] Monitor temperature, power events, and hardware health.

RAID or mirrored disks can help survive a disk failure. They do not protect against deletion, ransomware, or loss of the building.

### Internet

- [ ] Use independent ISPs where practical.
- [ ] Check whether both providers share the same last-mile cable or upstream path.
- [ ] Test router failover and failback.
- [ ] Verify public IP availability and whether either connection uses CGNAT.
- [ ] Test incoming website access through the backup connection from an external network.

**Outbound failover is not automatically inbound failover.** Office staff may browse the internet through the backup ISP while outside users still cannot reach the website.

If the office loses internet but the primary still runs locally, office users must not continue writing there after the off-site standby becomes primary. There must be one authoritative writable site.

## 6. Keep the Public Address Stable

Users should access one public domain, regardless of the active site.

### DNS Failover

DNS changes the domain's destination to the standby.

- Relatively simple and often less expensive.
- Some users continue reaching the old address while DNS answers remain cached.
- A low TTL helps but does not guarantee immediate switching.
- Existing connections do not automatically move to the new destination.

### Hosted Reverse Proxy or Load-Balancing Service

A hosted service receives HTTPS requests and forwards them to the active site.

- Can keep the user-facing endpoint stable while changing origins.
- Can detect unhealthy origins, subject to the configured checks.
- Needs appropriate security, capacity, and availability of its own.
- TLS termination may expose decrypted traffic to the service; review provider access and data-handling requirements.

**Do not automatically send write traffic to a database standby that has not been safely promoted.** Traffic switching and database failover must be coordinated.

A tunnel can solve connectivity problems such as CGNAT. It cannot make an offline building serve requests without another working origin.

### Health Checks

- [ ] Monitor from outside the office network.
- [ ] Check application readiness, including essential dependencies.
- [ ] Separate basic process health from readiness to serve business requests.
- [ ] Return minimal status information; never expose credentials, database details, or records.
- [ ] Require sustained failure before triggering escalation to reduce false alarms.
- [ ] Remember that an unreachable site is not proof that its database stopped accepting writes.

## 7. Database Redundancy

For an office primary and off-site MySQL standby:

- [ ] Keep one writable primary.
- [ ] Restrict replication traffic to approved private connections and require encryption.
- [ ] Use dedicated replication credentials with appropriate privileges.
- [ ] Configure distinct server identities and a documented replication setup; consider GTID-based replication for easier transaction tracking.
- [ ] Keep standby MySQL read-only using the applicable read-only controls and restricted application credentials.
- [ ] Disable standby application writes, workers, and scheduled business tasks until promotion.
- [ ] Monitor replication errors, transaction progress, and lag.
- [ ] Retain enough binlogs for expected outages, with disk-space monitoring.
- [ ] Establish and test a fencing mechanism before allowing automatic promotion.

Read-only settings are an additional safeguard, not a substitute for fencing and access controls.

Do not use a single lag number as proof that the standby contains every acknowledged transaction. During primary failure, compare available replication evidence and document uncertainty.

If the business requires zero loss of acknowledged transactions, obtain a database architecture specifically designed and tested for that requirement. Do not assume ordinary asynchronous replication satisfies it.

## 8. Replicate More Than the Database

| Component | What to arrange | What fails if omitted |
|---|---|---|
| Private attachments | Off-site file replication or approved resilient private object storage | Database records exist but documents cannot be downloaded |
| Application and assets | Compatible release deployed to standby | Standby code cannot use the restored schema correctly |
| Encryption keys | Securely provision required keys | Encrypted values cannot be decrypted |
| Configuration and secrets | Separate secure provisioning with standby-specific endpoints | Standby depends on unavailable office services |
| Sessions | Recoverable storage or an explicit forced-login policy | Sessions disappear or depend on the failed site |
| Queues | Defined durable storage and retry/recovery procedure | Jobs are lost, duplicated, or remain inaccessible |
| Scheduler | Activate on the intended site without duplicate execution | Notifications or business operations run twice |
| Audit records | Recover consistently with related business changes | Accountability is incomplete after recovery |

### Private Attachments

For file synchronization, measure lag and verify downloads after failover. Do not assume database replication and file copying reach the same recovery point.

For object storage, require private access, authorized downloads, encryption, appropriate retention, and an approved location. Shared storage itself needs a recovery plan.

Protect older file versions or backups from replicated deletions. Include a procedure for finding missing files and orphaned records after recovery.

### Queues and Scheduled Tasks

Assume a job may be retried after its first execution partly succeeded. Design important jobs to avoid duplicate effects, using business constraints or idempotency keys where appropriate.

External effects such as sending an email cannot be undone by restoring a database. Reconcile uncertain job outcomes before replaying them.

Do not keep standby queues, sessions, or locks connected only to a service in the office. Do not give a standby server production side effects merely to test it.

## 9. Backups: Independent From Replication

- [ ] Back up MySQL, private attachments, required keys, and recovery configuration.
- [ ] Use a backup method appropriate to database size and recovery targets.
- [ ] Preserve binlogs when point-in-time recovery is required.
- [ ] Keep encrypted copies outside the building.
- [ ] Use separate backup access controls; prevent the application account from deleting all backups.
- [ ] Use immutable retention or another deletion-resistant mechanism where available.
- [ ] Define daily, longer-term, and legal/business retention with management.
- [ ] Monitor backup completion and recoverability.
- [ ] Restore to an isolated environment regularly and verify application behavior.

For example, if a mistaken change replicates at 14:00, a healthy standby may contain the same mistake. Recovery may require restoring a backup and replaying transactions only to the chosen point before the mistake.

For suspected ransomware or compromise, do not blindly fail over to a potentially compromised replica. Isolate affected systems and follow incident-response procedures.

## 10. Controlled Failover Runbook

Start with operator-controlled failover. Automate only after failure detection, fencing, and recovery procedures have been tested.

### Step 1: Confirm and Classify the Incident

- [ ] Check external monitoring, office connectivity, application health, and database health.
- [ ] Identify whether the issue is a server failure, network failure, data corruption, or suspected compromise.
- [ ] Record the incident start and responsible operator.
- [ ] Decide whether repair within the RTO is realistic.
- [ ] Notify users in Indonesian without exposing sensitive infrastructure details.

### Step 2: Prevent Conflicting Writes

- [ ] Stop or isolate the original application's write paths, workers, and scheduler.
- [ ] Fence the old primary through a verified mechanism, such as independent management controls or a predesigned write-lease system.
- [ ] Verify that local office clients cannot keep writing to the old site.

**Changing public DNS is not fencing.** Local users and background workers can still write to the office database.

If the site is unreachable and fencing cannot be verified, stop at the documented escalation point. Do not treat loss of contact as permission to create a second writable primary.

### Step 3: Assess Standby Data

- [ ] Check replication health and available transaction evidence.
- [ ] Check attachment availability and synchronization state.
- [ ] Estimate possible lost transactions/files and record uncertainty.
- [ ] Obtain approval if potential loss exceeds the agreed RPO.
- [ ] Preserve available recovery evidence before changing roles.

### Step 4: Activate the Standby

- [ ] Promote MySQL using the approved version-specific procedure.
- [ ] Verify application database/storage endpoints and compatible code.
- [ ] Enable standby writes only after the promotion checks succeed.
- [ ] Activate intended workers and scheduler without enabling competing instances.
- [ ] Switch public traffic to the new active site.

### Step 5: Verify Real Workflows

- [ ] Test from outside the office, including mobile data.
- [ ] Verify HTTPS, login, and role-based access.
- [ ] Download a permitted private document and confirm unauthorized access is denied.
- [ ] Perform an approved, clearly identified test transaction and verify its audit entry.
- [ ] Check queue processing, scheduled tasks, and error rates.
- [ ] Confirm backups now protect the new primary.
- [ ] Record recovery time and observed data loss.

An HTTP success response alone is not proof that service has recovered.

## 11. Failback: Returning to the Original Site

Do not switch back just because the office server starts again.

1. Keep the old primary isolated or read-only.
2. Investigate and fix the original failure.
3. Identify any transactions that exist only on the old site; reconcile them deliberately rather than merging databases blindly.
4. Rebuild the recovered site's replica from the current authoritative primary using the approved procedure.
5. Synchronize private files and verify code/configuration compatibility.
6. Confirm replication health and recovery readiness.
7. Decide whether returning to the office is necessary at all.
8. If returning, schedule a controlled switchover with write quiescence, final synchronization, fencing, promotion, and traffic switching.
9. Revalidate workflows and restore the standby/backup arrangement.

Do not allow both sites to accept independent business writes during failback.

## 12. Monitoring and Ownership

Monitor from outside the building, and deliver alerts through a channel that does not depend on the failed application.

| Signal | Why it matters |
|---|---|
| Public application availability and latency | Detects what external users experience |
| Power, UPS, and office connectivity | Warns of building-level failure |
| Primary and standby database health | Confirms both operating and recovery capacity |
| Replication progress, errors, and lag | Identifies possible data-loss exposure |
| File synchronization and storage health | Identifies missing-attachment exposure |
| Queue backlog and failed jobs | Shows stalled business processing |
| Backup failures and restore results | Shows whether recovery is actually possible |
| Certificate/domain expiry | Prevents avoidable loss of public access |
| Standby capacity and configuration drift | Prevents a nominal standby from being unusable |

For every alert, assign an owner, escalation path, and response procedure. Avoid collecting sensitive record contents in monitoring.

## 13. Test the Arrangement

Run drills in a safe environment first. Schedule approved production exercises with rollback and user communication.

- [ ] Stop an application instance and verify expected recovery.
- [ ] Simulate primary-site connectivity loss, including the case where its database remains running.
- [ ] Test total loss of the building's connectivity, not just NGINX failure.
- [ ] Check that the old site's workers and local users cannot continue writing after promotion.
- [ ] Test with replication lag and a missing attachment.
- [ ] Recover from a backup, not only a replica.
- [ ] Verify office and mobile access after traffic switching.
- [ ] Measure detection time, decision time, recovery time, and lost data.
- [ ] Test failback as well as failover.

Illustrative cadence: monitor continuously, review recovery readiness monthly, conduct a recovery drill quarterly, and repeat relevant tests after major infrastructure or application changes. Adjust this to business risk and staffing.

Keep test environments isolated from real notifications, external integrations, and sensitive production data unless explicitly authorized.

## 14. Phased Implementation Checklist

### Phase 1: Protect Data and Understand Dependencies

- [ ] Agree on RPO/RTO, data-location policy, budget, and incident ownership.
- [ ] Inventory power, ISP, DNS, servers, MySQL, files, sessions, queues, and secrets.
- [ ] Configure isolated off-site backups and external alerts.
- [ ] Test a complete restore outside the building.
- [ ] Document the public access path and all single points of failure.

### Phase 2: Improve the Existing Building

- [ ] Add/test UPS protection for servers and network equipment.
- [ ] Add/test backup connectivity where justified.
- [ ] Verify inbound public access through the backup connectivity.
- [ ] Protect maintenance access and management controls.
- [ ] Create the incident communication and escalation procedure.

### Phase 3: Remove the Building as the Only Operating Location

- [ ] Move the primary off-site if approved, or provision an off-site warm standby.
- [ ] Deploy compatible application code and securely provision configuration.
- [ ] Replicate database and private attachments with monitoring.
- [ ] Define sessions, queue recovery, and scheduler activation.
- [ ] Establish verified fencing and public traffic switching.
- [ ] Exercise failover and failback.

### Phase 4: Add High Availability When Required

- [ ] Add redundant application capacity.
- [ ] Select a supported database HA design that meets the actual RPO/RTO.
- [ ] Remove single points of failure in public ingress and shared services.
- [ ] Automate promotion only with reliable fencing and failure detection.
- [ ] Test under realistic workload and network failures.
- [ ] Review residual risks, staffing needs, and recurring costs.

## 15. Budget and Decision Summary

Budget for more than a second server:

- Standby compute and storage.
- Database/file replication bandwidth and possible transfer charges.
- Private connectivity and public traffic management.
- Backup storage and immutable retention.
- Monitoring and alert delivery.
- Certificates/domain management where applicable.
- Maintenance, patching, incident response, and recovery exercises.

There is no universal hardware size or failover time. Measure database size, attachment growth, peak load, transfer speed, and restore duration before sizing.

**Practical starting recommendation:** reliable off-site backups, external monitoring, and a tested recovery procedure first. Then choose off-site primary hosting or an off-site warm standby. Keep one writable primary, and make building-loss recovery a tested capability rather than an assumption.
