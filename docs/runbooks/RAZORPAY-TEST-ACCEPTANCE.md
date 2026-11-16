# Razorpay test acceptance

16 November 2026. **Provider acceptance is pending.** No real test credentials are configured in the local environment; no live credential or provider transaction was used. The preceding simulated-provider verification remains 58 Laravel tests/372 assertions, 31 frontend tests, 9 Chromium tests and8 MySQL concurrency scenarios. Those results are not Razorpay merchant-account acceptance.

## Current implementation audit

PaymentService persists a unique server-priced order/attempt and verifies owned callbacks against the stored provider order. It fetches payment/order evidence and verifies INR, exact paise, receipt, captured status, amount paid and zero due. It reconciles the captured payment itself so older failed attempts cannot hide a later refund. Mutating provider requests have no automatic HTTP retries; an ambiguous response becomes indeterminate.

WebhookProcessor checks the raw-body HMAC before storing sanitized IDs/body hashes, rejects ID/body conflicts, and queues durable processing. Pending visibility remains retryable; signed duplicates and worker crashes do not repeat financial entries. Previous webhook secrets support retry overlap. ProviderRefunds/RefundService verify partial/full/failed refunds, unique intents and receipt-bound recovery without blind resubmission. FinancialOperations provides bounded recovery; late captures and payment_review remain operator decisions. All provider operations remain local/test gated.

## Exact acceptance topology and setup

Use a **fresh isolated sample database** and a private Next/API pair. Ordinary APP_ENV=staging with a normal MySQL database is intentionally blocked. Do not bypass gates or relabel a real database local/testing. The default browser suite expects missing credentials and mocks SDK failure/pending states; keep it separate from credentialed acceptance.

1. In Razorpay Dashboard select **Test Mode** and create a dedicated test API key/secret. Only rzp_test_ keys are allowed. Confirm automatic capture or nominate the test manual-capture operator; the application does not itself capture authorized payments. Create a separate random webhook secret of at least 16 characters (prefer32+). Supply secrets through the private process environment/secret tooling, not source, command arguments or NEXT_PUBLIC variables.
2. In each root API/worker/helper terminal, choose the same new `STOREFRONT_TEST_RUN`, for example `rzp-acceptance-UNIQUE`. Securely inject `STOREFRONT_RAZORPAY_TEST_KEY`, `STOREFRONT_RAZORPAY_TEST_SECRET`, `STOREFRONT_RAZORPAY_WEBHOOK_SECRET`; set `STOREFRONT_TEST_QUEUE_CONNECTION=database`. Then run setup.php and seed.php **once** for that unused label:

```powershell
php tests/Storefront/setup.php
php tests/Storefront/seed.php
php -S 127.0.0.1:8138 tests/Storefront/router.php
```

3. In separate terminals with the same run label/secrets start `php tests/Storefront/console.php queue:work database --queue=storefront-financial --sleep=1 --tries=3 --timeout=60`; invoke `php tests/Storefront/console.php schedule:run` every minute under a supervised scheduler. Check schedule:list and financial-status. The harness uses an isolated SQLite file and fictional rates, suppresses real mail/broadcasts and uses a fixture-only encryption key. It is not an ordinary hosted staging application.
4. In a separate storefront process set `LARAVEL_API_ORIGIN=http://127.0.0.1:8138`, exact browser `STOREFRONT_URL`, a separate random session secret of at least 32 characters, `STOREFRONT_SANDBOX_TRANSACTIONS=true`, `STOREFRONT_INDEXABLE=false`. Optional Next `STOREFRONT_RAZORPAY_TEST_KEY` must match the backend public test key. Preserve `.env.local`; use secure process overrides. Run `npm.cmd run build`, then `npm.cmd run start -- --port 3140` on a free port. Do not package this sample-data build for release.
5. A provider cannot deliver to localhost. After separately approving ingress, supply a public HTTPS endpoint forwarding **only POST /api/razorpay/webhook** to the private API, preserving raw bytes and X-Razorpay-Signature/X-Razorpay-Event-Id. Deny all other harness paths, including identity and fixture endpoints. Keep the browser/API administration behind authentication/VPN; do not put Basic Auth in front of the provider-only webhook path. Do not open or publish a tunnel as part of this preparation. Confirm the chosen domain is permitted by current provider restrictions.
6. Register that endpoint in the Test Dashboard with the separate webhook secret. Subscribe to payment.authorized, payment.captured, payment.failed, order.paid, refund.created, refund.processed and refund.failed. Optional `STOREFRONT_RAZORPAY_PREVIOUS_WEBHOOK_SECRETS` is a JSON array of explicitly approved old webhook secrets; load it into every receipt process, verify retry/replay behavior and retire it after the agreed window. Do not confuse it with the API secret or APP_PREVIOUS_KEYS.

[Provider test/live separation](https://raw.githubusercontent.com/razorpay/markdown-docs/master/payments/dashboard/test-live-modes.md) states that Test Mode moves no real money. [Provider webhook validation](https://raw.githubusercontent.com/razorpay/markdown-docs/master/webhooks/validate-test.md) requires raw-body verification and handling duplicate/out-of-order delivery, and documents public endpoint restrictions.

## Acceptance matrix

Each row below has simulated coverage where described; **every merchant/provider column remains pending**. Record test operator/date, app order ID, provider entity IDs, exact paise/currency, expected/observed status, stock/coupon/cart deltas and sanitized timing evidence. Never record full payment bodies, signatures, cookies, secrets or payment instrument details.

| Case | Required provider test and expected result |
| --- | --- |
| Order creation | Owned order creates one matching INR provider order with the persisted UUID receipt. Duplicate/concurrent submissions reuse it. Browser-supplied amounts are ignored. |
| Callback/capture | Actual test SDK success plus server verification commits once. Authorized-only stays pending. Wrong HMAC/order/customer/amount/currency/capture evidence is rejected. |
| Failed/dismissed/retry | Real test failure and modal dismissal show no success. Retry within the reservation reuses the provider order; a stale failure cannot downgrade capture. |
| Ambiguous create | Interrupt the response after provider creation in a controlled test proxy; reconcile by receipt and verify one provider order. Never force a second mutation because the browser timed out. |
| Lost callback | Close the browser after test capture; queued webhook/periodic reconciliation commits once without callback reliance. |
| Webhook verification | Invalid signature, modified raw body, oversized/malformed body and reused ID with changed content are rejected. Receipt acknowledges promptly after durable storage; measure against the provider delivery window. |
| Duplicate/order variations | Send provider replay and simultaneous repeated deliveries, including early order.paid before payment visibility and failed events after capture/refund. Verify eventual convergence and no duplicate stock/money effects. |
| Worker outage | Stop only the test worker, receive a durable event, restart and confirm recovery. Rehearse worker timeout/lease expiry, provider-read failure/backoff, scheduler outage and alerts. |
| Reservation expiry | Capture before/after expiry. A released reservation plus late capture is flagged for refund review, never automatically fulfilled or deducted twice. |
| Partial/full refund | Collected approved return creates one test refund intent within the remaining payment budget. Pending does not mean completed. Fetch/verify processed status and reconcile amount/refund ledger once. |
| Failed/lost refund | Verify failed-state alert and no blind retry. For a lost response, recover the original provider refund by its UUID receipt; no second payout request. |
| Dashboard refund | Perform only a test-dashboard refund, verify signed refund events/periodic discovery and exact balance. Unmapped item allocations require review. First-seen already-refunded captures pause fulfillment. |
| Secret overlap | Rehearse old-secret automatic retries and current-secret replay behavior using actual Dashboard delivery evidence. No mismatched secret silently marks an event processed. |
| Reconciliation | Compare local intents/captures/refunds and provider reports with the browser closed. Resolve every mismatch and alert; successful capture is not bank settlement. |

Do not deliberately run untrusted payloads or network fault injection against production. For cases the provider sandbox cannot reproduce, retain the automated simulated proof and record the exact limitation instead of fabricating a pass.

The provider's [webhook validation and local/staging guidance](https://raw.githubusercontent.com/razorpay/markdown-docs/master/webhooks/validate-test.md) documents raw-body signatures, duplicate IDs, ordering and current ingress-domain restrictions. Confirm those restrictions before arranging an endpoint; no public ingress was created here.

## Acceptance sign-off

Require zero unexplained duplicate attempts, stock/coupon deltas or monetary mismatches; resolved operational alerts; demonstrated worker/scheduler recovery; measured webhook acknowledgement; approved capture/refund settings; and signed operator evidence for every row. Real bank credit cannot be accepted through Test Mode. Remove private acceptance ingress and temporary secret access when done. Production activation remains a separate approval and implementation gate.
