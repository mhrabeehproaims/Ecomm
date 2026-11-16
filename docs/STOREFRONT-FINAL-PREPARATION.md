# Vanthara final preparation and release decision

16 November 2026. **Production NO-GO remains in force.** Safe local preparation is complete; deployment, APP_KEY rotation, history rewriting, live credentials and production checkout were not authorized or performed. The runtime Laravel/Next.js implementation and existing production data were not changed during this preparation.

This report follows [blocker remediation](STOREFRONT-BLOCKER-REMEDIATION.md). Completed development and broad browser/build suites were not unnecessarily repeated. The preceding verification remains 58 Laravel tests/372 assertions, 31 frontend tests, 9 Playwright tests, 8 MySQL concurrency scenarios, TypeScript, both builds, HTTP checks and local backup/restore verification. Those are prior local results, not a new host certification or actual Razorpay acceptance.

## Deliverables and findings

| Deliverable | Conclusion and review document |
| --- | --- |
| APP_KEY exposure and safe remediation | Current local key matches both recorded exposed Git revisions and the local cached key. Prior read-only remote verification confirmed an exposed revision on origin main. **Deployed reuse is unknown.** [Dependency inventory and approved-change procedure](runbooks/APP-KEY-REMEDIATION.md). |
| Razorpay acceptance | No test key/secret/webhook secret is configured in local env. Secure primitives have simulated coverage; merchant-account SDK/webhook/refund acceptance is pending. [Exact test configuration and case matrix](runbooks/RAZORPAY-TEST-ACCEPTANCE.md). |
| Settlement reconciliation | Internal capture/refund/COD evidence exists. Provider settlement allocation matching and approved bank evidence do not. Bank settlement remains false/unverified. [Evidence sources, local report and matching design](runbooks/SETTLEMENT-RECONCILIATION.md). |
| Commercial approval | Explicit owner/accountant decisions remain for delivery, GST/invoices, promotions, COD, cancellations, returns/refunds, inventory disposition and finance operations. [Concise decision checklist](STOREFRONT-COMMERCIAL-DECISIONS.md). |
| Staging preparation | Environment/service templates and a forward migration/restore/operations checklist are prepared, not installed. Hosted staging stays nontransactional; payment acceptance uses a separately guarded sample environment. [Staging instructions](runbooks/STAGING-DEPLOYMENT.md). |

## APP_KEY conclusions

Source inspection identifies encrypted catalog/admin identifiers, Laravel cookies, optional encrypted session data and framework signed/temporary URLs as key-dependent. No current application encrypted database casts/custom encrypted field writes or encrypted jobs were found; that cannot exclude older/imported/deployed ciphertext. Password hashing, Sanctum token hashes, Next's separate cookie key and Razorpay signature secrets have different dependencies.

Root `.env` and storefront `.env.local` remain on disk and excluded from the current Git index/future normal adds; examples remain tracked. Existing historical exposure remains. No key value or fingerprint was exposed in the report. Removing an index entry does not rotate a credential or prove that a deployed host is safe. The hosting owner must compare each effective deployed/cached key against exposed material in protected tooling and return only a reuse/nonreuse result; do not send environment files or key values to chat/logs.

The key plan preserves verified encrypted bytes, requires an isolated restore/re-encryption rehearsal and explicit approval for actual rotation/session handling. It also identifies deserialization risk in legacy decrypt() identifier inputs when an exposed key is accepted. Retaining that key indefinitely through APP_PREVIOUS_KEYS is not remediation. Old-only rollback cannot read new-key writes; coordinated cache/worker restart and a compatible recovery plan are necessary.

## New local verification

| Focused preparation check | Result |
| --- | --- |
| Disposable-key compatibility/conversion rehearsal | **8 checks passed** with ephemeral random keys; no real APP_KEY/config/DB read or rotation |
| Local configuration metadata inspection | Local/cached key equality and Git revision matches confirmed using boolean-only output; no provider credentials present |
| Read-only SQLite financial evidence | **9 secured sample orders**, 4 legacy fixtures excluded, 0 internal mismatches; database-file SHA256 unchanged |
| Read-only MySQL financial evidence | **8 sample orders**, 1 expected missing-receipt warning from a manually COD-paid replacement-race fixture; no automatic repair |
| Provider/bank calls from evidence tool | **0**; records updated **0**; bank settlement always reported unverified |
| Diagnostic PHP and configuration/template checks | Scoped lint/format/syntax and placeholder/disabled-state checks performed; templates not loaded into running applications |

The MySQL warning confirms missing evidence is visible: an existing synthetic test fixture sets paid status directly to exercise replacement behavior and has no COD receipt. It is not a newly discovered production transaction or proof of settlement. Four excluded SQLite legacy fixtures need separate evidence review if used for any future acceptance. No legacy data was backfilled or altered.

New evidence is under `storage/framework/testing/preparation-20261116/`: `configuration-metadata.json`, `key-rehearsal.json`, `financial-evidence.json`, `mysql-financial-evidence.json` and preparation review metadata. The diagnostic helper is intentionally limited to existing isolated test databases and is not a public endpoint, production accounting tool or statement importer.

## Files prepared

- Three local diagnostic helpers: `tests/Storefront/app-key-rehearsal.php`, `preparation-inspect.php`, `financial-evidence.php`.
- Disabled, secret-free examples: `deploy/staging/laravel.env.example` and `storefront.env.example`.
- Uninstalled service/schedule examples: `deploy/staging/supervisor.conf.example`, `storefront.service.example`, `cron.example`.
- This report, four linked runbooks and the commercial checklist.

No migrations or runtime endpoints were added in this preparation. Existing user modifications remain untouched. Root `.env` differs from the older remediation checkpoint by additional bootstrap-admin settings; comparisons found no changed existing entries versus committed HEAD. Those local settings were preserved without printing values or resetting the file. Both real env files remain excluded from the index, while the original examples remain tracked. New preparation files remain uncommitted for review; include them deliberately in a reviewed source commit, while excluding real secrets, diagnostics/test databases and private runtime artifacts from deployment.

## Outstanding approval and acceptance gates

| Gate | Required closure before real orders |
| --- | --- |
| Exposed key and account incident | Hosting-owner reuse assessment; approved data-preserving key/session/link strategy; admin credential reuse review; completed controlled remediation and signed evidence |
| Test-provider acceptance | Authorized rzp_test credentials, approved HTTPS webhook ingress, capture settings and completed SDK/failure/replay/refund/recovery/reconciliation case matrix |
| Bank and finance operations | Approved evidence source/account/schema and controlled matching, fees/adjustments policy, operator ownership, unresolved/late payment and COD payout/cancellation procedures |
| Commercial readiness | Signed decisions and effective configuration; approved invoice and customer policy/support content; remaining customer security/recovery scope resolved or formally accepted |
| Deployment acceptance | Reviewed complete artifact, actual host/proxy/media privacy, isolated forward migration, restored backup, monitored workers/scheduler, mail/broadcast acceptance and rollback rehearsal |
| Activation authorization | Separate reviewed production adapter and explicit activation approval; existing gates must not be bypassed through environment relabeling |

**Current recommendation:** GO for review of these local preparation artifacts. NO-GO for production orders and NO-GO for claims of completed Razorpay/bank acceptance. No deployment is authorized by this report. Normal staging can be deployed only after a separate approval with checkout disabled; financial acceptance remains isolated until its controls and environment are explicitly approved.
