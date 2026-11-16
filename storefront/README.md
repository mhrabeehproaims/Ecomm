# Vanthara storefront

The existing customer frontend is **Next.js 16.4.6 App Router + React + TypeScript + Tailwind 4**, not React + Vite. Root Vite builds Laravel assets. Existing code was preserved; brand colors remain plum `#7C0046` and green `#1C3F1D`.

Public browsing, customer shopping and the approved Laravel checkout safeguards are implemented locally. **Production checkout and live payments remain disabled.** See the [implementation, verification and release blockers](../docs/STOREFRONT-CHECKOUT-IMPLEMENTATION.md), [historical approval proposal](../docs/approvals/CUSTOMER-API-COMPLETION.md) and [earlier visual QA](../docs/STOREFRONT-VISUAL-QA.md).

## Local setup (Windows PowerShell)

This workspace now has an ignored `.env.local` with normal loopback origins, a random development session secret and sandbox checkout disabled. Preserve it. The copy/generation steps below apply to a fresh checkout; never reuse its development secret for deployment.

The normal Laravel API for this workspace runs at `http://localhost:8000`. When it is already running, start only the storefront:

```powershell
cd C:\laragon\www\vanthara-main\storefront
npm.cmd run dev
```

Set `LARAVEL_API_ORIGIN=http://localhost:8000` in the existing ignored `.env.local`. Run `npm.cmd run catalog:check` to diagnose the configured API. The fixture API on port 8138 contains a separate sample database and must not replace the admin API. If Laravel is stopped, the optional `npm.cmd run dev:local` starts both applications using the configured loopback origins and normal local Laravel database. It rejects occupied ports without stopping other applications and keeps checkout disabled. Use it only when those ports are free.

For a fresh checkout, install dependencies and configure the session secret before starting:

```powershell
cd C:\laragon\www\vanthara-main\storefront
if (!(Test-Path -LiteralPath '.env.local')) { Copy-Item .env.example .env.local }
npm.cmd ci
node -e "console.log(require('node:crypto').randomBytes(48).toString('base64url'))"
# Put the generated value in STOREFRONT_SESSION_SECRET in .env.local.
npm.cmd run dev:local
```

Open http://127.0.0.1:3000. Preserve an existing `.env.local`. Node >=20.9 is required; use npm.cmd when PowerShell blocks npm.ps1. Set `LARAVEL_API_ORIGIN` without `/api` and `STOREFRONT_URL` to the exact browser origin; restart Next after changes. Public deployment needs HTTPS and a deployment-managed random session secret. No Laravel bearer token or payment secret belongs in NEXT_PUBLIC variables. No production migrations/seeders are part of frontend setup.

```powershell
npm.cmd test
npm.cmd run typecheck
npm.cmd run build
npm.cmd run start
# With the production server running:
npm.cmd run smoke
```

If Node's os.userInfo fails inside an execution sandbox before tsx starts, use a normal authorized terminal for the same tests; do not skip assertions.

## Customer integration

Registration/mobile-password login/logout use encrypted HttpOnly cookies and same-origin JSON writes. Profile, delivery addresses, wishlist, cart add/quantity/removal, coupon validation, owned order history/detail and eligible return requests use actual Laravel APIs. Checkout reviews a server-calculated expiring quote before idempotent order placement. Address snapshots, atomic stock reservations, coupon limits and payment amounts are enforced by Laravel, including for direct API callers.

The test-only Razorpay adapter verifies signatures and fetched capture/amount/currency state, deduplicates webhooks and reconciles ambiguous results. Operator-only test refunds require verified provider results. These paths have fake-provider automated coverage; real Razorpay acceptance needs test credentials. Tracking shows actual internal records; no external carrier integration exists. Durable backend throttling is enabled. Commercial shipping/tax/return terms, external transports and operational acceptance remain release requirements; see the full report.

## Isolated transactional tests

Playwright 1.65 and Chromium were already installed. If missing, run `npx.cmd playwright install chromium` once; no connected browser is needed.

From the Laravel root, **once per fresh run label**:

```powershell
$env:STOREFRONT_TEST_RUN = 'qa-20261113'
php tests/Storefront/setup.php
php tests/Storefront/seed.php
```

Setup uses only `storage/framework/testing/vanthara-checkout-<run>/isolated.sqlite`, overrides config cache/DB_URL and refuses to overwrite an existing DB. The default run label is `v2`; choose a new label to start another sample database without deleting existing data. Set the same label in each test API/helper terminal. Do not seed twice or substitute normal artisan migrate:fresh. No production database reset is permitted.

Start the isolated API in a root terminal:

```powershell
$env:STOREFRONT_TEST_RUN = 'qa-20261113'
php -S 127.0.0.1:8138 tests/Storefront/router.php
```

In a storefront terminal:

```powershell
$env:LARAVEL_API_ORIGIN = 'http://127.0.0.1:8138'
$env:STOREFRONT_URL = 'http://127.0.0.1:3140'
$env:STOREFRONT_SESSION_SECRET = 'isolated-test-session-secret-at-least-32-characters'
$env:STOREFRONT_SANDBOX_TRANSACTIONS = 'true'
npm.cmd run build
npm.cmd run start -- --port 3140
```

In another storefront terminal:

```powershell
$env:STOREFRONT_TEST_RUN = 'qa-20261113'
npm.cmd run test:transactions
$env:PLAYWRIGHT_BASE_URL = 'http://127.0.0.1:3140'
npm.cmd run test:browser -- --grep 'browse category|filters update|keyboard skip' --workers=1
```

The suite creates fake customers/sample orders only. It tests invalid credentials/coupons, owned resources, quote recovery, forged totals, reservation cancellation, returns, persistent login throttling, cross-origin rejection and production order/payment rejection using a second task-owned Next process on 3141. It also explicitly mocks pending/failed/cancelled SDK states without claiming a provider payment. Ports 8138, 3140 and 3141 must be free before their corresponding servers start. Reports and twelve mobile/desktop screenshots are in `artifacts/transactions/`. Repeated orders consume sample stock; choose a fresh run label when needed. Existing and new Laravel tests: `php tests/Storefront/run-tests.php` from the root (feature tests use fresh in-memory SQLite).

The sandbox flag also requires a loopback API, exact test-harness identity and the backend's isolated-database gate; it cannot enable production checkout. Optional gateway acceptance requires `STOREFRONT_RAZORPAY_TEST_KEY` (`rzp_test_` only) and `STOREFRONT_RAZORPAY_TEST_SECRET` in the **test API** process, plus a separate `STOREFRONT_RAZORPAY_WEBHOOK_SECRET`. The backend returns the public test key; the secret never belongs in Next. Keep credentials unset for the default unavailable-payment browser test, which expects zero gateway calls. Real provider success/failure/cancellation/webhook/refund acceptance remains unverified. Never deploy the test harness publicly.

Isolated operational wrappers and guarded MySQL concurrency-test setup are documented in the [implementation report](../docs/STOREFRONT-CHECKOUT-IMPLEMENTATION.md). All 53 migrations passed on fresh isolated SQLite and MySQL. Do not point these helpers at the normal Laragon database.

Stop task-owned processes after testing. Clear temporary shell overrides and rebuild with normal origins before using/packaging `.next`: public prerendering and image configuration depend on the API selected at build time. Do not ship a sample-data build.

## Preserved browsing and structure

`src/app/` contains public/customer routes and allowlisted search/customer API handlers. `src/components/` contains shared browsing and customer UI; `src/lib/` separates public catalog contracts from private sessions/customer adapters. Tests and scripts contain Chromium, unit, HTTP and review-artifact tooling.

Public browsing retains real backend products/categories/offers, opaque encrypted links, keyboard search, filters/pagination, media fallbacks, actual return policies and metadata. Filters operate over the loaded full API response; backend pagination is needed for scale. Stable product locators remain missing, so product pages stay noindex and global indexing defaults off. Business information pages remain provisional. No deployment was performed.

The existing decorative `public/hero.webp` was generated in the earlier visual phase (1400px WebP, about 129KB): ivory vase, leafy branch, linen and plum sculpture on warm limestone. It is not product imagery. Authentic Laravel product media was preserved; isolated test-logo images never replace production inventory.

Fashion redesign: see `docs/STOREFRONT-FASHION-REDESIGN.md` and `docs/FASHION-DATA-PROPOSAL.md` in the repository root. Original editorial assets live in `public/fashion`; product photography still comes from Laravel. `npm run test:fashion` exercises seven responsive widths plus navigation and isolated customer pages. It requires the normal read-only app on port 3000 and the explicitly isolated test harness on ports 8138/3140; never point the writable harness at a normal database. The existing catalog and transaction test commands remain available.
