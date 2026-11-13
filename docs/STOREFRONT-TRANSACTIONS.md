# Vanthara customer integration report

> Historical frontend integration stage. The approved local backend work is now described in [the checkout implementation report](STOREFRONT-CHECKOUT-IMPLEMENTATION.md), which supersedes the backend limitations below. Production checkout remains disabled.

13 November 2026. This report supersedes the earlier foundation reports for customer features. Existing visual QA was retained; this stage used real local Chromium and real Laravel responses against a separate sample database. **The store is not ready for transactional production release.**

## Actual architecture

The customer storefront is **Next.js 16.4.6 App Router**, React, TypeScript, Tailwind 4 and Zod in `storefront/`. It is not React + Vite. The root Vite package builds Laravel's existing assets. No working application was deleted, rebuilt into another framework or migrated. The requested React + Vite stack differs from the existing implementation; a framework migration was deliberately not undertaken.

Customer requests now go through allowlisted Next route handlers to the existing Laravel 12/Sanctum APIs. Laravel bearer tokens remain server-side inside AES-256-GCM encrypted HttpOnly cookies; they are not returned to browser JavaScript or stored in localStorage. Private requests/responses are uncached. Writes require the configured same origin and JSON, validated input and owned resources. Cookies are Secure under HTTPS. Sign-out revokes the backend token when available and clears local cookies; revocation failures are disclosed. Session encryption requires a deployment-provided secret of at least 32 characters.

## Feature status

“Functional” means verified against the isolated local Laravel database, not production certification.

| Feature | Status | Verified behavior / limitation |
| --- | --- | --- |
| Registration, login, logout | Functional locally | Real sample registration; wrong-password rejection; login; encrypted HttpOnly session; logout and protected-route redirect. No production customers used. Optional email delivery and recovery/OTP flows were not tested. |
| Customer profile | Functional locally | Read/update and validation; frontend strips privileged fields such as status/password. Direct Laravel field restrictions need hardening. |
| Delivery addresses | Functional locally | Create/edit/remove, select state/default address and checkout selection; fresh profile supplies the address list because resource GET actions are empty. Frontend checks ownership before update/delete; direct API update ownership remains a backend blocker. |
| Wishlist | Functional locally | Save/remove, list saved products, move product to bag. Uses the backend toggle contract. |
| Cart | Functional locally | Add, increment/change quantity, remove, empty state and fresh product prices. Frontend validates quantity/stock and owned line IDs. Direct API quantity validation and atomic stock checks still require backend changes. |
| Coupon validation | Partial | Valid TESTSAVE and invalid code verified; code carried to checkout and revalidated. Backend provides no authoritative discounted quote; browser totals are ignored. |
| Checkout/order placement | Isolated tests only | Sample online order actually created by Laravel: 2 × INR250 − INR25 = INR475, unpaid. Forged browser totals/payment status ignored. No full address snapshot or delivery quote exists; production placement returns 503 and creates no order. |
| Razorpay | Partial, not payment-verified | Test-only SDK/initiation/confirmation adapters implemented, bound to owned backend order and encrypted provider context. Missing test credentials produces an honest unavailable state, with **zero Razorpay requests**. Success/capture/failure/webhook cases were not executed; no live payments. |
| Order history/detail | Functional locally | Owned order list/detail, backend totals, items, payment and order status; another customer's order not exposed. No invented payment success. |
| Tracking | Partial | Backend status displayed. Carrier links, tracking events and delivery estimates are absent from the API. |
| Return requests | Partial | Owned delivered sample order -> request with server-derived item price -> backend pending-review status. No refund issued or approval claimed. Authoritative policy deadline/eligibility, cumulative quantities and refund execution remain backend work. |

## Browser coverage and evidence

Real Playwright Chromium ran against a production Next build on `127.0.0.1:3140` and the isolated Laravel router on `127.0.0.1:8138`. A second local Next process with the sandbox flag **off** verified production order/payment rejection and unchanged owned order history. It used the same sample database, not a production origin.

Pages exercised: homepage, category, catalog/search/filter results, product detail/gallery, registration, login, account/profile/addresses, wishlist, cart, checkout, orders/detail and return request. Existing keyboard search, skip link and mobile navigation were checked. New customer screens were captured at **393px and 1440px**, with no horizontal overflow and no uncaught JavaScript errors in the customer journeys. The completed seven-width visual suite was not unnecessarily repeated.

Evidence under `storefront/artifacts/transactions/`:

- `report/index.html`: Playwright report; `results.json`: machine-readable results.
- `screenshots/account-addresses-{393,1440}.png`.
- `screenshots/cart-coupon-{393,1440}.png`.
- `screenshots/checkout-sandbox-{393,1440}.png`.
- `screenshots/order-payment-unavailable-{393,1440}.png`.
- `screenshots/return-request-{393,1440}.png`.

Focused existing browsing evidence remains in `storefront/artifacts/visual-qa/after/`; its latest report represents the focused regression selection, while the earlier seven-width production evidence is retained separately. Full-page captures include fixed mobile navigation at the viewport boundary. Test sample product images intentionally use the test logo endpoint; production catalog assets and brand tokens plum `#7C0046` / green `#1C3F1D` remain unchanged.

## Confirmed problems fixed

- Customer shortcuts previously led to phase notices. Real authentication, private screens and product purchase actions now use Laravel.
- Address resource GET actions are empty and update ownership is missing. The frontend reads addresses from fresh owned profiles, validates ownership, and sends complete optional fields.
- Backend delivery state requires enum codes such as `kerala (KL)`. All Indian state/territory choices now send the exact contract.
- Raw cart product IDs/media/prices differ from public product resources. Dedicated validation/normalization preserves encrypted IDs and backend-selected prices.
- Quantity validation, profile field filtering, owned order/item checks, same-origin checks, safe errors and private caching now protect frontend requests. These do not replace direct API fixes.
- Native wrapped selects produced ambiguous accessible names including their options; explicit names fixed keyboard/test selection.
- Address action-link CSS accidentally removed the primary save button's fill. Narrowed the selector and added a brand-color browser assertion. Customer copy now uses shopping terms instead of implementation terminology.
- Coupon success could appear before the checkout link included the code. Validation no longer unnecessarily reloads private state; checkout carries and revalidates the code.
- Category segments arrived percent-encoded in this installed Next runtime. Explicit single decoding fixes categories with spaces; Unicode and literal-percent unit coverage was added.
- Logout clears cookies and performs a fresh navigation to avoid retaining private route state.
- An early test DB location inside the Next project triggered development watcher churn. The DB now lives outside the frontend, generated artifacts are excluded from Tailwind scans, and acceptance runs use a production build.

## Verification

| Check | Final result |
| --- | --- |
| `npm.cmd test` | 31 passed, 0 failed (approved execution outside the sandbox). |
| `npm.cmd run typecheck` | Passed. |
| `npm.cmd run build` | Passed: optimized production compilation, TypeScript and 22 static pages; rebuilt with normal local origins after isolated testing. |
| `npm.cmd run test:transactions` | 3 passed, 0 failed in the final sequential run (49.2 seconds). |
| Existing browser suite, focused category/search/filter/keyboard/mobile selection | 3 passed, 0 failed (9.8 seconds). Earlier completed seven-width visual QA retained. |
| `npm.cmd run smoke` | 16 read-only HTTP checks passed against both isolated and normal-origin production builds. Normal config also confirmed sandbox/payment capabilities off and anonymous orders rejected with 401. |
| `php tests/Storefront/run-tests.php` | All 2 existing Laravel tests passed in the isolated environment. |
| Test-harness PHP syntax | All 6 files passed. |
| Backend review patch | Six proposed PHP files passed syntax checks; `git apply --check` passed; six original source hashes unchanged. Not applied or behavior-tested. |

Earlier failures were resolved: test locators matching the route announcer, select names, coupon handoff, encoded category routes, randomized Laravel ciphertext comparison, and an assertion observing a button's legitimate darker hover color. One concurrent browser run timed out on the loopback harness-identity probe and correctly refused payment; the sequential rerun passed. The built-in PHP server is single-threaded: run the two browser suites sequentially as documented. No gateway call occurred during that failure.

A sandbox Node `uv_os_get_passwd` failure prevented the unit runner starting; the same tests passed with approved execution outside that sandbox. No automatic approval rejection occurred. Razorpay capture/success/failure/webhook, MySQL concurrency and production deployment acceptance remain unexecuted, not passing tests.

All task-owned Next/Laravel/test API processes were stopped after final verification. The normal local build and ignored development configuration remain ready for the README start commands. No sample-data build is left as the final artifact.

## Isolated database safety

`tests/Storefront/environment.php` overrides database/config cache, clears DB_URL, disables external mail/broadcast/log effects and supplies test-only keys. The sample SQLite file is `storage/framework/testing/vanthara-storefront/isolated.sqlite`. All 51 existing migrations ran against that new file. Setup refuses to overwrite an existing database; there is no migrate:fresh. Fixtures create three sample products, a test category/brand/coupon, fake customers and delivered sample orders. No production record was read for customer tests or modified. SQLite does not prove MySQL locking/concurrency behavior.

The test harness and credentials must never be deployed publicly. `STOREFRONT_SANDBOX_TRANSACTIONS=true` alone cannot enable order placement: the frontend also requires a loopback API and the exact isolated harness identity. Live Razorpay keys are rejected. No production session secret was generated or persisted.

## Files added or modified in this stage

An ignored local `storefront/.env.local` was also created with a random development-only session secret, normal loopback origins, indexing off and sandbox checkout off. The root Laravel `.env` was untouched. Deployment must supply its own secret and HTTPS configuration.

- `storefront/src/app/api/customer/[action]/route.ts`: customer API/session boundary and test-only order/payment adapters.
- `storefront/src/lib/{customer-contracts,customer-server,customer-cart,customer-client,session-crypto,delivery-states,sandbox-policy,route-segment}.ts`: validated contracts, private transport, encrypted sessions, normalization and safety gates.
- `storefront/src/components/{auth-form,customer-screen,product-actions,test-payment}.tsx`: customer forms and journeys.
- `storefront/src/app/{login,register,account,wishlist,cart,checkout,orders}/page.tsx`, `orders/[id]/page.tsx`: customer routes.
- Existing `products/[slug]/page.tsx`, `categories/[name]/page.tsx`, `[page]/page.tsx`, `robots.ts`, `globals.css`, `src/lib/config.ts`: purchase controls, category decoding, current help copy, private-route indexing and customer styling.
- `storefront/.env.example`, `package.json`, `playwright.transactions.config.ts`, `tests/transactions/customer.spec.ts`, `tests/session.test.ts`, `tests/browser/storefront.spec.ts`, `scripts/smoke.mjs`: configuration and verification.
- `tests/Storefront/{environment,setup,seed,router,delivered-fixture,run-tests}.php`: new isolated-only test support. Existing Laravel application/config/admin/migrations remain untouched.
- `storefront/scripts/prepare-backend-review.mjs`, `docs/approvals/{customer-api-hardening.patch,CUSTOMER-API-COMPLETION.md}`: review-only backend proposal.
- `storefront/README.md`, audit/visual reports and this report: current setup and status.

## Missing contracts and remaining release blockers

1. No authoritative pre-order quote, shipping/serviceability rules, persisted chosen delivery-address snapshot, atomic stock/coupon reservation or idempotent checkout. Legacy checkout decrements stock/clears cart before verified payment. Production order creation stays disabled.
2. Payment APIs lack sufficient ownership/capture/amount checks and a monotonic idempotent webhook lifecycle. Real Razorpay test credentials and success/failure/cancellation/duplicate scenarios remain required after backend fixes. Payment records were not altered outside sample orders' normal backend creation.
3. Direct Laravel API authorization/validation defects remain in address update, cart, profile/inactive authentication and return submission. Frontend safeguards are verified but do not fix direct API callers. Durable shared rate limits are also required; the frontend login throttle is process-local only.
4. Missing owned tracking events/carrier details and server-derived return eligibility/delivery timestamps. Return requests are review requests, not a verified refund system.
5. Approved privacy/terms/shipping/contact copy, real deployment session secret/HTTPS, stable product locators, production configuration and operational monitoring remain release requirements.
6. No physical-device, Safari/WebKit/Firefox, screen-reader, MySQL race or real gateway acceptance certification is claimed. Existing responsive Chromium QA is retained.

Approval is needed for the concrete backend patch and completion contract in `docs/approvals/CUSTOMER-API-COMPLETION.md`. The user explicitly prohibited unapproved Laravel business-logic changes; those changes have not been applied. No external deployment was performed.
