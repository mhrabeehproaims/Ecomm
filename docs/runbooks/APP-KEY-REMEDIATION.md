# APP_KEY exposure and safe remediation

16 November 2026. **Assessment complete locally; rotation is not approved or performed.** This runbook preserves encrypted data and existing uncommitted work. It is not an instruction to run key:generate on an existing environment.

## Exposure assessment

| Question | Evidence and conclusion |
| --- | --- |
| Committed? | Yes. Earlier redacted history inspection found a nonempty APP_KEY in `.env` at `0dda0390383e` and `0ca10169687d`. |
| Pushed? | Yes, at least the `0dda0390383e` exposure. The prior same-day read-only remote/main ancestry check confirmed it was reachable from origin main. Repository visibility, other branches/forks, past readers and CI copies are unknown. No remote write was made. |
| Current local use? | The local `.env` key matches both recorded exposed Git revisions and the local cached Laravel key. Local env/cache identify local mode. No key value or fingerprint was printed. Session-data encryption in that local cached configuration is false; browser cookies still use encryption. |
| Used in deployment? | **Unknown.** No authenticated deployment inventory or deployed secret access is available here. A public hostname or encrypted URL cannot prove whether a deployed host uses this key. Do not claim either reuse or safety. |
| Future commits? | `.env` is already removed from the index and ignored; its local file remains. Both root and storefront `.env.example` remain tracked. Existing history is unchanged. Ignored config caches/build artifacts must also stay out of release source. |

Evidence: `storage/framework/testing/remediation-secret-audit.json`, `preparation-20261116/configuration-metadata.json`, and the prior remediation checkpoint. A previously built-in admin password is a separate account-reuse incident; rotating APP_KEY would not fix it.

## Actual dependencies

| Data or facility | Application evidence | Effect and handling |
| --- | --- | --- |
| Catalog product/category IDs | Product accessor; ProductResource, ProductWithCommentResource, CategoryResource, SubCategoryResource and OrderHistoryResource encrypt identifiers. Product/category/subcategory/cart/wishlist/return APIs decrypt them. | Existing URLs, cached catalog responses and submitted product IDs depend on the key. Refresh/reissue IDs and invalidate appropriate catalog/Next caches; preserve carts/order records, whose relational IDs are not ciphertext. Old bookmarks need an approved compatibility or stable-link policy. |
| Admin order/customer/return/enquiry links | OrderController, Customers, SalesReturnController, WholesaleEnquiryController and Blade customer/notification/order/return/enquiry views. | Open tabs/bookmarks/notifications containing old links may stop resolving after retirement. Document/reissue links; do not delete source orders or notifications. |
| Laravel cookies | Installed EncryptCookies middleware encrypts/authenticates cookies using the app encrypter. | Admin session and remember-me cookie compatibility is affected. Plan approved reauthentication and incident session handling. Do not assume the local SESSION_ENCRYPT=false makes cookies independent of APP_KEY. |
| Persisted session payloads | SessionManager selects EncryptedStore when SESSION_ENCRYPT=true. | Review each host's effective configuration and existing sessions. Old encrypted session rows need compatibility or approved expiry/invalidation. Neither operation was run. |
| Signed/temporary URLs | Installed URL generator resolves APP_KEY plus previous keys; local filesystem can serve signed temporary files. No custom signed guest invoice feature exists. Financial documents use a separate nonserved disk. | Retaining old keys can also retain old signature acceptance. Inventory outstanding signed URLs if a deployed feature uses them; revoke/reissue according to a reviewed policy. |
| Encrypted database fields/files/jobs | No application encrypted casts, custom encrypted database writes, custom OpenSSL encryption or ShouldBeEncrypted jobs were found in source. | This is source evidence, not a forensic database scan. Imported/legacy ciphertext, extensions, old deployments, serialized job payloads and external integrations remain to inventory on an authorized isolated copy. Never bulk-reencrypt every string or JSON column. |
| Signed serialized closures | Installed EncryptionServiceProvider supplies the current parsed APP_KEY to SerializableClosure's signer. No application queued closures were found in source. | Inventory any legacy pending closure payloads before rotation. APP_PREVIOUS_KEYS does not configure a second SerializableClosure signing key; do not assume its compatibility from successful ordinary decryption. |
| Framework password-reset token generation | PasswordBrokerManager/DatabaseTokenRepository use APP_KEY as the generation HMAC key; no customer/admin reset route was found here. Verification uses the stored token hash. | A future/deployed reset feature and existing token rows require incident review; rotating APP_KEY alone should not be assumed to revoke old reset tokens. |
| Hashes and independent secrets | Passwords use hashing; Sanctum stores SHA256 token hashes. Next customer cookies use AES-GCM with STOREFRONT_SESSION_SECRET. Razorpay callback/webhook HMACs use their separate secrets. PDFs/snapshots are not APP_KEY-encrypted. | APP_KEY rotation does not rehash passwords, rotate provider secrets, revoke bearer tokens or rotate Next cookies. Each incident action requires its own explicit plan. |

The legacy `decrypt()` calls deserialize by default. A disclosed valid key can authenticate attacker-crafted ciphertext; numeric-ID deserialization and ownership validation need security review before retaining that key on public ingress. This is a code-risk finding, not a demonstrated code-execution exploit. Do not use encrypted IDs as authorization or expose old-key conversion as a web endpoint.

## Procedure requiring owner approval before execution

1. Assign incident and data owners. Inventory all deployed Laravel/worker/cron releases, container images, secret-store versions, config caches, DB copies, local tooling and backups. Compare key equality **inside the authorized host/secret tooling**, returning equality/environment labels only. Do not send keys through chat, browser pages, logs or command-line arguments. No host comparison has been performed here.
2. Determine the blast radius and whether temporary network/access restrictions are necessary. Restrict repository and backup access. Review who could read the exposed commit and whether the old admin seed password or service credentials were reused. Obtain an approved credential/session response; deleting a Git file alone does not resolve exposure.
3. Create an encrypted, access-controlled backup of affected databases, private document files and required key versions. Retain a key-to-backup manifest separately from ciphertext and test a restore to a new isolated target. Do not overwrite a source database or use Git as a secret backup.
4. On that isolated copy, list encrypted columns/serialized formats and representative counts by key version. Confirm decryption under controlled tooling without logging plaintext. Separate hashed fields and normal financial snapshots. Quarantine failures for review; never replace unreadable values with null, guessed plaintext or a new empty value.
5. Approve one strategy: maintenance-window conversion and immediate retirement, or a tightly limited compatibility interval. Laravel supports APP_PREVIOUS_KEYS for old-key decryption while new writes use the current key. Because this key is exposed, global compatibility also accepts potentially forged old-key input. Prefer a narrowly controlled offline converter for stored data where possible; define a cutoff and explicit link/session policy.
6. For verified encrypted fields, preserve decrypted **bytes**, including whether the original representation was PHP-serialized. Reencrypt without deserializing untrusted historical bytes. Use an explicitly reviewed field allowlist, bounded transactions, optimistic version checks, checkpoints, per-row source hashes and a reversible mapping. Do not modify prices, order balances, inventory or document contents as part of conversion.
7. Rehearse old/new/mixed-key reads, identity links, admin/customer sign-in, private invoices, workers and failure rollback. The disposable rehearsal supplied here proves cryptographic mechanics only; it does not prove a deployed dataset can be converted safely.
8. Only after approval, provision a fresh key in the secret manager and update all web/worker/scheduler instances together. Rebuild config caches within the target environment and restart long-lived workers; never ship a local cached config. Refresh catalog/Next caches containing encrypted IDs. Apply only the approved session/token actions.
9. Reconcile conversion counts/hashes and exception rows; verify complete data readability and monitor decrypt failures. Retire old keys on the approved deadline. A code rollback must still read new-key writes: **reverting to the old key alone is unsafe**. Pause writes and follow the rehearsed compatibility/recovery plan instead of overwriting data with an older backup.
10. Consider shared-history cleanup only through a separately authorized, coordinated change. Rotation and access review remain necessary even if history is later rewritten.

[Laravel key rotation documentation](https://laravel.com/framework/docs/12.x/encryption) describes previous-key fallback. This repository's AES-256-CBC configuration is unchanged.

## Local evidence and review gates

Run `php tests/Storefront/app-key-rehearsal.php` from the repository root. It creates disposable keys in memory, reads no real key/config/database and verifies eight cases: old-ID compatibility, new-key writes, retirement behavior, unsafe old-only rollback, and raw/serialized value preservation. Output is boolean-only and can be saved in an ignored test-artifact directory. All eight checks passed.

`php tests/Storefront/preparation-inspect.php` reports local configuration metadata only. Do not deploy this test helper or create an HTTP route for it. Real environment files were already excluded from future commits without deleting them. No APP_KEY rotation, history rewrite, data conversion or session invalidation was executed.
