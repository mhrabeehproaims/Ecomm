# Vanthara production blocker remediation

16 November 2026. **NO-GO for real customer orders.** The locally verifiable security and financial recovery fixes below are implemented and tested. Production checkout remains disabled by code, not merely an environment default. No production migration, deployment, live payment, credential rotation or history rewrite was performed.

The application remains Next.js 16.4.6 / React / TypeScript with Laravel 12 and Blade admin. Laravel dependencies were patched within their existing constraints; no storefront rebuild or framework-major migration was made. This report updates the findings in [the original readiness audit](STOREFRONT-RELEASE-READINESS.md); that audit is retained as historical evidence.

## 1. Security findings and closure

| Finding | Local remediation | Remaining release condition |
| --- | --- | --- |
| R01 invoice privacy | Invoice and packing-slip routes check the owning active customer or authenticated Laravel admin. Customer API/BFF downloads use the private session. Unknown, anonymous and foreign filename requests return 404. New PDFs use a separate private disk, UUID paths, hashes and private/no-store headers. Historical static paths are denied by Laravel, Apache rules and an Nginx include. | Apply and verify the deny rule on every actual API/media/CDN origin, purge previously cached copies, review access logs and historic exposure. Native isolated Nginx tests pass; the production proxy/CDN and Apache deployment were not tested. |
| R02 documents | Order-time identity, selected delivery address, product/pricing/GST/coupon/delivery snapshots feed immutable PDFs and shipment/delivery emails. Hardcoded dates/numbers/carrier fallback and double-counted delivery in emails were removed. Issuance and fulfillment updates share a DB transaction. | Approve seller identity, GST/HSN/billing presentation, numbering/credit-note requirements and legacy amendments. These PDFs are not certified statutory invoices. |
| R03 Composer advisories | Runtime audit fell from 59 advisory records across 15 packages to 0. Development-only PHPUnit deserialization advisory was patched too. | Repeat audits in the release artifact and maintain patch monitoring. See dependency table below. |
| R04 tracked environment | Added root exclusions; removed `.env` and PHPUnit cache from the Git index only. Real local env files remain byte-for-byte unchanged. `.env.example` is preserved and extended with disabled/default-safe examples. Removed the built-in admin seeder password; future provisioning requires explicit credentials and never resets an existing user. | A nonempty APP_KEY is reachable through current remote main history. Coordinate key/account review and rotation; index removal does not remove history or invalidate compromised credentials. |
| R05 premature webhook completion | Valid signed events are durably received before acknowledgement; pending provider visibility remains retryable. Early order.paid eventually converges without a browser callback. | Real test-mode provider delivery/timing acceptance. |
| R06 refunds | Provider refunds, partial/full refunded balances, receipt-bound lost-response recovery and capture/refund ledger entries are reconciled monotonically. Delayed payment events cannot undo refunds. | Real provider acceptance; review unmatched dashboard refunds, item allocations and bank settlement. |
| R07 webhook latency | Receipt performs signature/schema/deduplication/database work, with provider reads moved to queued/scheduled processing. Previous webhook secrets support bounded operational rotation overlap. | Measure real HTTPS acknowledgement latency and outage/replay behavior; securely supply and retire overlap secrets. |
| R08 unattended recovery | Minute schedule, dedicated webhook queue, durable DB scheduler mutex, worker leases, bounded batches, retry backoff and sanitized persistent alerts are implemented. Expiry failures do not starve other orders. | Supervise the actual scheduler/workers and connect alert delivery. No OS services were installed or activated. |
| R09 COD and cancellation | Test-only admin COD collection/remittance receipts are append-only, evidence-bound, idempotent and balance-limited. Complete collection marks a delivered COD order paid and permits existing return eligibility. Unsafe rejection of committed orders is blocked. | COD stays off pending cancellation, collection timing, cash refunds/manual payout and reconciliation policy. Paid/committed cancellation is not a completed business workflow. |
| R10 hosted staging | Existing local/testing, isolated-DB, loopback-origin and test-key restrictions remain intact. | Ordinary hosted staging/production cannot be enabled by these flags. A separately approved hosting/activation adapter and acceptance plan are required. |

No anonymous guest document feature was added. If needed, review an order/document-bound HTTPS signed URL with a short expiry, random revocable token, private/no-store delivery and optional one-use redemption. Do not expose UUID storage paths as public access tokens or treat a predictable invoice number as authorization.

All Laravel `User` accounts retain the existing admin access model; this change does not introduce new admin roles. Confirm that only trusted operators can obtain those accounts. A full admin permission/penetration audit is outside the regression coverage.

## 2. Dependency remediation

The original runtime records included1 critical,14 high,30 medium,6 low and8 unrated advisories. Counts refer to package advisory records, not demonstrated exploit paths. Fixed compatible installed versions:

| Runtime package | Before | After |
| --- | --- | --- |
| laravel/framework | 12.26.3 | 12.72.0 |
| dompdf/dompdf | 3.1.4 | 3.1.6 |
| guzzlehttp/guzzle | 7.10.0 | 7.16.1 |
| guzzlehttp/psr7 | 2.8.0 | 2.13.3 |
| league/commonmark | 2.7.1 | 2.10.7 |
| league/flysystem | 3.30.0 | 3.36.0 |
| paragonie/sodium_compat | 2.4.0 | 2.5.2 |
| psy/psysh | 0.12.10 | 0.12.24 |
| Affected Symfony components | 7.3.2 | 7.4.23 |

PHPUnit 11.5.35 became 11.5.58, covering CVE-2026-24765 without a major upgrade. The detailed advisory links, package severities and lockfile versions are in `storefront/artifacts/remediation/dependencies.json`. Composer update scripts were deliberately disabled; no application migration was triggered by dependency installation. Final full/runtime audits returned 0; the final request warned that Packagist package metadata fell back to cache after a timeout. Earlier update/audit requests succeeded online. Repeat a fresh online audit in the release pipeline.

The previously failing admin Vite build imported two undeclared packages. Added laravel-echo 2.6.3 and pusher-js 8.6.0, then applied compatible npm audit fixes. Root Vite 7.1.7 became 7.3.8 and Axios 1.12.2 became 1.21.1. The root npm audit fell from 12 affected package entries to 0. Next.js dependencies and architecture remain unchanged; its runtime npm audit is clean. Both production builds pass.

## 3. Invoices and historical records

New documents store the accepted source JSON, document SHA256, invoice/order identity and private file path. The invoice uses its persisted unique identifier and issue date, plus the actual order date. Subsequent profile, address, product, coupon and policy changes cannot rewrite an issued PDF. An inconsistent accepted/order/invoice total fails closed. Tracking details printed at first issuance remain immutable; changing an issued shipment requires an explicitly reviewed amendment.

Tests cover local and interstate tax, a nondefault selected address, 13.33% coupon allocation, taxable delivery, later edits, missing snapshots, foreign/anonymous/admin access, no-store responses and historical byte preservation. Both PDFs were rendered to PNG and visually inspected: one page each, readable totals/address and no clipping.

Existing legacy PDF bytes remain untouched and are served only through protected routes. The local/test `storefront:archive-documents` command copies and hash-verifies existing documents privately without deleting originals or inventing snapshots. Missing historical documents raise alerts. Legacy orders without reliable snapshots cannot issue a new reconstructed invoice automatically. Older secured orders without the new customer identity snapshot show missing identity rather than substituting the current profile. A business/accounting review must decide amendments and incomplete historical identity; completed financial records are not silently backfilled.

Immutable files and database transactions are different durability boundaries: an interrupted issuance can leave an unreferenced private UUID file. It is not served by download routes. No automatic orphan deletion was introduced. Back up both document storage and database metadata together.

## 4. Payments, refunds and COD

Amounts remain authoritative integer paise from accepted checkout snapshots. Payment callbacks and webhook workers re-fetch provider evidence and verify order/payment IDs, receipt, INR currency, amount, captured flag, provider-order paid amount and zero amount due. Callback HMAC and raw webhook HMAC remain distinct checks.

Webhook receipt stores sanitized identifiers and a body hash, not full payment/customer payloads. Event-ID/body conflicts are rejected; duplicate bodies do not create another financial event. Queued workers use token-bound leases; failed/pending events back off and recover after crashes. Signed replay can restore bindings absent from older pending rows. Already-processed historical events are not reset automatically: run explicit verified payment reconciliation if an old premature completion is suspected.

Capture, refund and COD evidence use unique append-only ledger references. Row locks and transactions prevent duplicate monetary entries, stock commits, over-refunds and excess COD collection. MySQL locking reads avoid stale repeatable-read balances after a worker waits for another worker's lock.

Refund processing supports verified partial/full provider refunds and existing collected-return intents. Unknown-response mutations remain indeterminate and are never blindly sent again. Recovery binds a verified provider refund with the original UUID receipt. Periodic reads discover known-receipt refunds and record unmatched external refunds for operator allocation review. Failed refund intents retain their budget and need a reviewed recovery decision rather than automatic new money movement. A verified late capture after stock release has a separate operator-requested, unique test refund intent; it does not create a fictitious sales return or deduct stock again.

An already-refunded payment first discovered while stock is reserved enters `payment_review`; fulfillment is paused. Automatic allocation, cancellation/restock or a new payout would require approved rules. This state raises an alert and needs an operator disposition. Committed/paid cancellations and COD cash refunds are intentionally not invented.

**Bank settlement is not implemented or certified.** Verified capture means the merchant payment was captured, not that the merchant bank received settlement. Balance responses explicitly report `bank_settlement_verified=false`. COD remittance records operator evidence only. There is no enabled merchant settlement API/feed or automatic bank statement matching. Define this requirement and perform real test-mode/provider operational acceptance before accepting real orders.

The refund collection endpoint follows [Razorpay's published payment refund API](https://github.com/razorpay/markdown-docs/blob/master/api/refunds.md). Provider status interpretation should be accepted against the merchant's test account, including [refund events](https://github.com/razorpay/markdown-docs/blob/master/payments/refunds/subscribe-to-webhooks.md), rather than inferred from a successful mocked test.

## 5. Scheduler, queue and operator runbook

Only local/test databases accepted by `CheckoutAccess` may run these operations. Use one reviewed test run label consistently for API, worker and scheduler processes. `tests/Storefront/setup.php` refuses to overwrite an existing SQLite database. MySQL helpers verify loopback 3339 and the separate test datadir; normal Laragon 3306 is excluded.

For a fresh private acceptance run, provision sample data using the existing setup/seed helpers. Supply secrets through protected process environment, not command arguments, source, browser variables or logs. Set `STOREFRONT_TEST_QUEUE_CONNECTION=database` for the private harness. Launch:

```powershell
php tests/Storefront/console.php queue:work database --queue=storefront-financial --sleep=1 --tries=3 --timeout=60
php tests/Storefront/console.php schedule:list
php tests/Storefront/console.php schedule:run
php tests/Storefront/console.php storefront:financial-status
```

These are setup instructions, not services installed by this change. Invoke schedule:run every minute using Windows Task Scheduler or, on a reviewed Linux host, cron under the service account. Supervise/restart queue workers and restart them on release. Queue retry_after must exceed the60-second worker timeout (default 90); worker timeout must remain below webhook lease duration5 minutes. Scheduler overlap locking explicitly uses the durable database cache mutex, even when the harness's general cache is an array. Database locks/leases also protect individual financial changes if a batch outlives its10-minute schedule mutex. Monitor batch duration/backlog and size/split workers before increasing volume.

Maintenance reviews at most 100 rows per operation: expired reservations, outbox effects, pending webhooks, payment attempts and refund intents. Payment reads are scheduled five minutes apart; exceptions back off 30 seconds up to an hour. One expiry failure is delayed five minutes without blocking other orders. Notification delivery is at least once, with token-bound leases and backoff; it is not a guarantee of exactly-once email. Financial mutations have separate database idempotency.

`storefront:financial-status` returns nonzero for unresolved alerts. Maintenance returns nonzero for counted batch errors. Centralize sanitized application logs and route alerts to an assigned operator; configure alerts for pending/failed jobs, old pending events, indeterminate intents, released late captures, payment_review, unmatched external refunds and unremitted COD. No alert destination or external monitoring service was configured locally.

Manual, test-gated commands include `storefront:reconcile-payment {order}`, `storefront:refund-return {return}`, `storefront:recover-refund-intent {intent} {provider_refund}` and `storefront:refund-late-capture {order}`. Refund initiation commands can create a TEST provider mutation when test credentials are supplied: require an authorized operator and recorded decision. The schedule only reconciles/recovers existing evidence; it does not initiate new provider payments or refunds.

## 6. Credentials and environment requirements

Redacted evidence is in `storage/framework/testing/remediation-secret-audit.json`. APP_KEY occurred in commits `0dda0390383e` and `0ca10169687d`; a read-only ls-remote/main ancestry check confirmed the exposed `0dda0390383e` commit is reachable on current origin main. Repository privacy and all prior readers/clones/CI copies were not established. No credential values were printed. Targeted tracked-source pattern screening found no additional live Razorpay/AWS/private-key pattern; it is not a full forensic guarantee.

Required owner/security actions:

1. Establish whether that APP_KEY or the former seeded admin password was reused anywhere. Review repository access, forks/clones, CI artifacts/logs and actual admin account provisioning. Keep compromised material out of staging/production.
2. Review and rotate affected admin passwords and any reused service/database/API credentials through their owners. Revoke affected sessions/tokens using an approved plan. Existing accounts and real credentials were not read or modified by this work.
3. For APP_KEY, inventory encrypted database values, cookies/sessions and encrypted product identifiers/links; take a protected backup and rehearse recovery/re-encryption in isolation. Agree downtime, session invalidation, data conversion and rollback with the owner before changing the key. Laravel supports APP_PREVIOUS_KEYS, but indefinitely accepting a compromised old key is not remediation. No key:generate was run.
4. Give each environment distinct new secrets. Overlap old webhook secrets only for the documented retry/replay window, then retire them after reconciliation. Remove one-time bootstrap-admin process credentials after provisioning.
5. Removing secrets from future commits does not remove old history. Any shared-history rewrite requires separate authorization and coordination with collaborators; none was performed.

Required backend variables: normal Laravel APP_KEY/APP_URL/database/mail/log/session/queue settings; `STOREFRONT_DOCUMENT_ROOT` on private durable storage; local-only `STOREFRONT_TEST_CHECKOUT`, `STOREFRONT_TEST_COD`, explicit shipping/state/postcode/tax configuration; `STOREFRONT_RAZORPAY_TEST_KEY`, `STOREFRONT_RAZORPAY_TEST_SECRET`, `STOREFRONT_RAZORPAY_WEBHOOK_SECRET`, optional JSON `STOREFRONT_RAZORPAY_PREVIOUS_WEBHOOK_SECRETS`. Checkout/COD defaults are false. Optional one-time `VANTHARA_BOOTSTRAP_ADMIN_EMAIL` and `VANTHARA_BOOTSTRAP_ADMIN_PASSWORD` replace the old built-in seed login.

Next requires `LARAVEL_API_ORIGIN`, exact `STOREFRONT_URL`, a distinct random `STOREFRONT_SESSION_SECRET`, `STOREFRONT_INDEXABLE=false` and the explicit isolated sandbox flag for test transactions. Do not expose provider secrets via NEXT_PUBLIC variables. Normal `.env` and storefront `.env.local` hashes still match the pre-change checkpoint.

## 7. Files, migrations and Git review

Checkpoint: `storage/framework/testing/backups/remediation-20261116052919`,332 original files plus supplemental package/seeder/example backups. All original checkpoint copies verify against their hashes. Initial uncommitted checkout-controller/test/documentation edits and four existing audit helpers were retained. No broad reset or cleanup occurred. The original 128-file formatting restoration remains accounted for; six of those files now intentionally differ for this remediation. AdminTableSeeder now has a deliberate security change; it must no longer be described as formatting-only. DatabaseSeeder remains unchanged.

Created application files:

- `app/Http/Controllers/{OrderDocumentController,CodAccountingController}.php`
- `app/Http/Middleware/BlockPublicFinancialDocuments.php`
- `app/Jobs/ProcessStorefrontWebhook.php`
- `app/Services/Storefront/{OrderDocuments,WebhookProcessor,ProviderRefunds,FinancialLedger,FinancialOperations,OperationalAlert,CodAccounting}.php`
- `resources/views/invoices/partials/address.blade.php`
- `deploy/nginx/financial-documents.conf`, root `.gitignore`
- `tests/Feature/StorefrontRemediationTest.php`, `tests/Storefront/document-fixture.php`

Modified application areas: OrderController and checkout capabilities; CheckoutPricing, PaymentService/Gateway/RazorpayTestGateway, RefundService and Outbox; shipment listener and shipment/delivery mailables/templates; invoice/packing templates and admin order view; web/API/console routing, bootstrap middleware, filesystem/storefront configuration; AdminTableSeeder; Composer lock and root npm manifest/lock; Next customer BFF, order contracts/support/payment feedback; transaction, fixture/gateway, concurrency, isolation and test-runner helpers; `.env.example`.

One migration was added: `2026_11_16_120000_add_financial_recovery_and_private_documents.php`. It adds document metadata, provider-refund evidence, append-only financial entries, operational alerts, retry/lease columns, late-capture refund linkage and expiry/outbox recovery columns. Fresh isolated SQLite and MySQL migration runs pass. No completed order amounts or historical document bytes are backfilled. Its down() deliberately refuses to drop financial evidence: review a forward recovery rather than running a destructive automatic rollback. MySQL DDL deployment still needs a staging rehearsal/backup because it is not covered by an application transaction.

API changes preserve existing contracts: protected existing invoice URL; new packing-slip URL; owned `GET /api/orders/{order}/documents/{kind}`; private Next `GET /api/customer/document`; test-gated authenticated/CSRF-protected admin `POST /orders/{order}/cod-receipt`; existing payment/webhook endpoints now use durable processing. There is no new anonymous financial download or public refund endpoint.

The complete source path/hash inventory and sanitized diff are `storefront/artifacts/remediation/changes.json` and `source-review.diff`. Source changes remain uncommitted for review; new source/migration/docs files must be included in the reviewed release commit. Only environment/cache index removals are staged. The repository historically tracks vendor: dependency regeneration therefore produces many expected vendor differences. Ignoring vendor does not untrack existing files. Do not commit a partial vendor tree: build releases from the lockfile in a clean artifact directory excluding checked-in vendor, then run Composer install there. A dedicated decision to stop tracking vendor is separate from this patch; no broad index cleanup was performed.

## 8. Verification and artifacts

| Check | Final result |
| --- | --- |
| Laravel automated suite | **58 tests, 372 assertions passed**, PHP 8.3.33 / PHPUnit 11.5.58 |
| Frontend unit tests | **31 passed** |
| Playwright Chromium | **6 transaction/access tests +3 browsing/keyboard tests passed** |
| MySQL 8.4.3 concurrency | **8 multi-process scenarios passed**: last stock, duplicate order, last coupon use, duplicate capture, expiry/capture, replacement, simultaneous partial refunds and excess simultaneous COD receipts |
| Composer | Runtime and full/development audits clean; validate and runtime platform checks passed |
| npm | Admin full audit and Next runtime audit clean |
| TypeScript | Passed |
| Next production build | Passed, 22 generated pages |
| Laravel admin Vite build | Passed after compatible dependency fixes |
| Read-only HTTP smoke | **16 passed** |
| Native isolated Nginx | **5 passed**: public control plus four denied static-document paths |
| Fresh migrations | SQLite and separate MySQL schemas passed, 54 migrations |
| Backup/restore rehearsal | **59 tables** restored to a new test schema; every row hash matched |
| Formatting and source review | Scoped Pint and source diff whitespace checks passed; original env hashes unchanged |
| PDF render review | One-page invoice and packing slip rendered and visually inspected |

Negative coverage includes anonymous/foreign documents, unknown filenames, unsafe totals/address access, forged signatures/bindings, changed webhook body under a reused ID, duplicate bodies, malformed signed JSON, unavailable provider evidence, crashed leases, over-refunds/over-collection, production checkout gates and legacy snapshot absence. Database-queue execution was tested through Laravel's real queue worker with a simulated provider; no network provider mutation was made. The existing seven-width visual QA was not unnecessarily repeated; this run adds targeted browser access/transaction coverage.

Evidence locations:

- `storage/framework/testing/remediation-phpunit-final.xml` and `remediation-composer-*.json`; npm audit JSON alongside them.
- `storefront/artifacts/transactions/results.json`, `report/`, `screenshots/`, `customer-invoice.pdf`.
- `storefront/artifacts/visual-qa/results.json` for the three targeted browsing tests; older responsive screenshots remain in that directory.
- `storefront/artifacts/remediation/documents/{invoice,packing-slip}.pdf`, corresponding PNGs and `render-check.json`.
- `storefront/artifacts/remediation/nginx-static-deny.json`, dependency/inventory artifacts.
- `storage/framework/testing/vanthara-checkout-remediation-20261116/mysql-concurrency-results-*.json` and isolated dump under `backup-drill/`; restore evidence is `storefront/artifacts/readiness-20261116/restore-drill.json`.

Initial development failures were corrected: stale MySQL ledger reads, undeclared admin packages, dependency advisories, and a missing Next invoice contract field. Windows sandbox blocked npm's userInfo and Python renderer imports; approved normal-Windows local execution succeeded. No failing required check remains in the recorded local suite. Historical readiness-probes deliberately assert old defects and are not a post-remediation acceptance suite.

## 9. Remaining acceptance, decisions and production blockers

1. **Credential incident closure:** key/default-admin reuse assessment and approved rotation/session/data plan; remote history still contains the old key.
2. **Real Razorpay TEST acceptance:** credentials were not supplied. Configure Test Mode keys, capture settings and a separate HTTPS webhook secret in the merchant Dashboard. Subscribe to payment.authorized/captured/failed, order.paid and refund.created/processed/failed. Expose only the HMAC webhook ingress to the private sample API; never expose harness fixture/identity routes. Complete actual SDK success/failure/retry, lost callback, delayed/out-of-order/replayed webhook, secret overlap, wrong amount/currency, partial/full/failed/dashboard refund, timeout/lost-response recovery and local/provider total reconciliation. Measure delivery acknowledgement and worker recovery with the browser closed. Simulated provider events and mocked checkout UI do not establish Razorpay acceptance.
3. **Financial operations:** define merchant bank-settlement evidence/feed, reconciliation ownership, mismatch escalation, COD cash handling and refunds, paid cancellation/restock, released late captures and payment_review disposition. No automatic settlement or cash payout can be inferred from a status edit.
4. **Commercial approval:** service area/shipping charges, delivery tax/refundability, product tax/origin/billing/GST/HSN requirements, coupon/offer priority and stacking, usage restoration, reservation duration and cancellation/return/refund terms. Existing fixture rates/windows are test inputs. No external carrier connector or invented tracking event was added.
5. **Deployment acceptance:** fresh dependency artifact, reviewed forward migration, private durable document volume, verified Apache/Nginx/CDN denials and cache purge, distinct secrets, HTTPS/trusted proxy/cookie configuration, supervised queue/scheduler, mail/broadcast/error-monitoring acceptance, alert routing and protected backup/restore/rollback rehearsal. Local backup evidence is not a production disaster-recovery certification.
6. **Remaining customer release scope:** approved policy/contact details, account recovery/identity verification and earlier audit's unbounded reads/malformed wishlist-ID/session-revocation gaps still require scoped resolution or an explicitly accepted release scope. This financial patch does not constitute a full application security audit.
7. **Explicit activation approval:** keep production checkout disabled. The current CheckoutAccess and Next sandbox restrictions intentionally reject ordinary production/staging writes. No environment flag in this patch authorizes live checkout.

These are the conditions for a future GO decision. The local fixes are reviewable and verified, but their completion does not authorize production transactions.
