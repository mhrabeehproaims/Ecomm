# Staging deployment preparation

**Prepared, not deployed.** Production checkout remains disabled. Treat all commands/configuration below as an operator plan requiring separately approved staging deployment; none authorizes production deployment, key rotation, production migrations or live payments.

## Two environments with different purposes

| Environment | Purpose | Checkout behavior |
| --- | --- | --- |
| Production-compatible hosted staging | APP_ENV=staging, separate MySQL, real HTTPS/domain/proxy, private documents, supervised PHP/Node services | Disabled by backend environment/database gate and Next sandbox policy; suitable for deployment/admin/browsing acceptance, not payment-order acceptance |
| Private isolated Razorpay acceptance | Existing test harness on loopback, sample SQLite or restricted test MySQL, Test Mode credentials, tightly limited public webhook ingress only | Sample/test orders only; see the [acceptance runbook](RAZORPAY-TEST-ACCEPTANCE.md) |

Do not expose the harness publicly, rename a production environment local/testing, point helpers at a real customer database or remove gates to make hosted staging payable. A hosted test-checkout adapter and eventual production activation need separate review/approval; no such adapter is delivered here.

## Runtime and artifact requirements

Laravel remains 12.72.0 with PHP>=8.2; local evidence used8.3.33. Use the locked compatible packages and verify PHP extensions using composer check-platform-reqs. Include PDO MySQL, OpenSSL, cURL, DOM/XML, fileinfo, mbstring and the lockfile's requirements. Use a separate MySQL 8.x database with utf8mb4/strict mode; local concurrency evidence used8.4.3. Pin/rehearse an exact host PHP/MySQL/Node version. Next 16.4.6 requires Node>=20.9 per this project; select a supported host release and rerun checks there rather than assuming an arbitrary Node version is accepted.

Build an immutable source/artifact from a reviewed commit, including all new controllers, services, middleware, jobs, templates and the54th migration. The current worktree is uncommitted and historically tracks vendor. Exclude the checked-in vendor tree from the clean build context and reinstall from composer.lock; copying a partially updated tracked vendor tree is unsafe. Exclude real env files, local configuration caches, logs, SQLite/test MySQL datadirs, tests/harness, diagnostic scripts, `.git`, sample .next output and private documents from the published artifact. Keep private runtime volumes and secrets outside releases. Do not delete the local workspace to build a clean artifact.

In that isolated artifact, with controlled build networking:

```text
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts
composer check-platform-reqs --no-dev
composer audit --locked --no-dev
npm ci --ignore-scripts
npm run build
cd storefront
npm ci
npm test
npm run typecheck
npm run build
```

Composer scripts include key generation/migration hooks for project creation; never use composer create-project or update for deployment. Package discovery must be run explicitly after safe target configuration is loaded if scripts were disabled. Recheck audits online; the last completed audit had a Packagist metadata cache fallback. Native Node package install behavior, build network access and package lifecycle scripts must be reviewed in the host/CI sandbox. Preserve package locks.

Use the actual staging API/site origins at **both build and runtime**. Next prerenders catalog content and its image allowlist depends on the build origin. Build against the approved staging API with synthetic catalog data, not a loopback sample API or production customer data. Run under Node using next start; this application has server routes/HttpOnly sessions and cannot be deployed as a static export. The current config does not declare standalone output; do not assume a standalone server artifact exists. Permit the required .next/ISR/image-cache writes with a reviewed service account and cache strategy; avoid sharing caches between environments/releases unintentionally.

## Environment and secrets

Review `deploy/staging/laravel.env.example` and `storefront.env.example`. They contain reserved example.invalid domains, blank secrets and disabled checkout; load neither over existing local env files. Staging needs a unique never-exposed APP_KEY, separate DB credentials and a distinct random Next session secret (32+ characters). Provision a new environment's key through secret management only after confirming its data is new; any existing key/data change follows the [APP_KEY approval procedure](APP-KEY-REMEDIATION.md). Never reuse the committed key or copy local cached config.

The examples use secure host-only cookies, database sessions/cache/queues, false debug/indexing flags, private durable document storage and daily warning logs. External mail/broadcasts are suppressed until sample-recipient SMTP/broadcast acceptance; configure approved transports explicitly. These suppression defaults are not production mail acceptance. Set timezones consistently with application Asia/Kolkata and verify DB/provider report timestamps.

Use a secret manager or service-readable protected environment files outside document roots/build contexts; separate migration and runtime DB roles. Do not put passwords in shell history/process arguments, reports, CI output, image layers or NEXT_PUBLIC variables. Cache files contain resolved secrets: restrict access and generate them only on the target host. Replace/restart every instance consistently when approved secrets change. Do not automatically rotate any current secret as part of this plan.

## HTTPS and access

Reserve approved staging API/admin and shop domains; issue valid certificates and redirect HTTP to HTTPS. Terminate TLS at the reviewed proxy. Serve Laravel only from its public directory; deny dotfiles/config/private storage and unnecessary PHP paths. Bind Node privately and proxy approved shop requests with correct host/scheme. If TLS terminates upstream, explicitly configure trusted proxy addresses in Laravel bootstrap and validate generated URLs/cookies; no unimplemented TRUSTED_PROXIES env flag or wildcard trust is assumed.

Use VPN/IP/access controls for staging/admin. Keep browser writes same-origin and Laravel customer auth through the Next BFF. Verify private cookies, no open redirects, no secret-bearing client bundle and no credentialed wildcard CORS. Apply `deploy/nginx/financial-documents.conf` or the Apache deny rule on every media origin, and purge/review previously cached invoice copies. Check anonymous/foreign/owner/admin documents through the actual proxy/CDN, including uppercase and encoded paths. Ensure public catalog media remains accessible. Review HSTS only after domain/subdomain TLS coverage is known. Keep noindex and staging access restrictions; robots.txt is not access control.

## MySQL migration and recovery plan

Confirm target host/schema and credentials against an approved staging inventory before any command. Do not use normal Laragon3306 as an implicit test target. Use sample data or an approved sanitized copy, not production customer records. The added migration creates financial evidence tables and retry/lease columns and makes refund return linkage nullable; it does not backfill completed amounts or invoices. Review MySQL DDL locks/time and disk requirements on the exact host version. MySQL DDL is not made atomic by Laravel transactions.

Take a consistent DB backup plus private invoice metadata/files and release/config references, with secrets protected separately. Record backup checksum and row/document manifests. Restore into a **new unused** staging restore schema/volume and compare table counts/row hashes/document SHA256 and functional reads. The prior local rehearsal restored59 tables; it is not a production backup certification. Require an owner-approved RPO/RTO, retention, encryption, access policy and restore operator.

After the backup/restore gate and explicit staging approval, pause staging writers/workers as needed, run migrate:status, review SQL in the isolated rehearsal, and execute only the reviewed forward migration against the named staging target. Never migrate:fresh/reset or run default/admin seeders on existing data. The financial migration down() refuses to destroy evidence. Test application compatibility and read paths before switching traffic.

Rollback means restoring the compatible code release with retained schema/evidence or applying a reviewed forward correction. Do not automatically down financial tables or overwrite an active DB with an older backup. If a restore is necessary, preserve post-backup writes for reconciliation and restore to a new isolated target first. A code rollback must retain access to data written under any new encryption key. Document DNS/traffic/service reversal independently from financial data recovery.

## Processes, monitoring and acceptance

`deploy/staging/supervisor.conf.example`, `storefront.service.example` and `cron.example` are uninstalled Linux examples; adapt PHP/Node paths, service users, permissions and directories first. On Windows, use reviewed services/Task Scheduler equivalents with the same environment and hidden/background execution. No OS service or scheduled task was created locally.

Supervise PHP-FPM, a Laravel default queue worker and Node; invoke schedule:run every minute. Restart workers on release after loading target config. The ordinary hosted-staging financial schedule intentionally fails its enablement filter; an empty/dormant schedule does not establish payment recovery acceptance. In the separate private acceptance environment, supervise the dedicated storefront-financial queue and financial minute schedule using the harness wrapper. Use90-second retry_after above the60-second worker timeout; per-event leases are five minutes, batch mutex ten minutes, with DB idempotency and token-bound recovery. Measure backlog/batch duration and adjust reviewed worker design before scale.

Run package:discover/config:cache/view:cache explicitly on the approved target configuration. Rehearse route:cache before relying on it because legacy route registration contains auth-dependent behavior; do not blindly run a broad optimize command. Keep storage/bootstrap-cache writable only where needed. Review cache isolation and DB least privilege; never run cache clearing against a shared production cache.

Centralize restricted logs with deployment version/environment and sanitized correlation/order references; redact Authorization, Cookie, passwords, keys, signatures, raw payment bodies and customer/bank data. Assign alert routing/acknowledgement and retention. Monitor HTTPS certificates, availability, 5xx/auth spikes, decrypt failures, queue age/failed jobs, scheduler heartbeat, disk/DB capacity and document access failures. In isolated acceptance also monitor financial-status, unresolved payment/refund intents, old pending webhooks, late captures, payment_review, external refunds and unremitted COD. A process returning200 is not financial reconciliation. Demonstrate an alert reaching its named operator and recovery after a worker interruption.

## Deployment acceptance checklist

- [ ] Reviewed commit/artifact, complete new files, fresh lockfile installs and online audits.
- [ ] Separate approved host/DB/domains/secrets; exposed-key reuse assessment closed.
- [ ] Verified restore on new target and forward migration rehearsal; reversible traffic/code plan.
- [ ] Real HTTPS/proxy/cookie/media/document-denial checks; admin/staging access restricted.
- [ ] Correct Next build/runtime origins, server hosting and cache behavior; no sample build shipped.
- [ ] Supervised workers/scheduler and tested monitoring; approved sample mail/broadcast acceptance.
- [ ] Relevant Laravel/frontend/type/build/browser/HTTP checks repeated on the final host/artifact.
- [ ] Commercial checklist and Razorpay test acceptance recorded separately; bank evidence requirements assigned.
- [ ] Production checkout/COD/sandbox flags remain disabled; no live credential configured.

This checklist authorizes no deployment. Record the staging acceptance owner, exact artifact, host configuration, evidence and residual exceptions before requesting any further release approval. [Laravel deployment guidance](https://laravel.com/framework/docs/12.x/deployment) is supporting host guidance; the repository-specific guards above still apply.
