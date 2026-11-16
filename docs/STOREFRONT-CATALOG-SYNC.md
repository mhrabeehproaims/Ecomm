# Admin product → storefront synchronization

Verified locally on 16 November 2026. The existing Next.js 16.4.6 / React / TypeScript storefront and Laravel 12 Blade admin were preserved. Checkout remains disabled.

## Root cause and evidence

The admin was saving products correctly. Its normal MySQL database, `e-comm`, contained one active product, **Premium Shirt**. The storefront's unchanged `.env.local` points to `http://127.0.0.1:8137`, but that Laravel server was not running when investigated. Port 8000 belonged to a different application and returned 404 for `/api/api-products`; the separate QA API on 8138 used an isolated sample database. Neither was an appropriate replacement for the admin API.

Starting this repository's Laravel server on 8137 restored the product. Read-only verification decrypted the returned product IDs in memory and compared them with the active admin IDs: exact match, zero missing products. No key values were logged. The API returns `{ "status": "success", "data": [...] }`, which the frontend already parsed correctly. It returns the full active collection; storefront pagination is applied at 12 products per page. No runtime demo catalog overrides real results, and the configured origin is local, not the live public API.

Two additional problems affected synchronization: shared catalog fetches previously revalidated after 60 seconds without admin notifications, and public detail/recommendation endpoints could still return inactive products. Admin product updates and status changes deliberately suppress Eloquent events, so a generic observer alone would miss them.

## Changes

- Added `npm.cmd run dev:local` to launch the matching local Laravel and Next servers together, validate the product response, reject fixture APIs and occupied ports, and keep checkout disabled. It generates an ephemeral, separate catalog notification credential. `npm.cmd run catalog:check` diagnoses the configured API independently. No environment files are overwritten.
- Added authenticated catalog-tag invalidation after committed catalog changes. Notifications coalesce within a request, execute during termination, have short network timeouts, and cannot undo a successful admin save. Rollbacks produce no notification. Explicit notifications cover event-suppressed product update/status actions and bulk featured removal.
- Retained shared Next caching with a 15-second revalidation interval. Visible homepage/catalog/category tabs refresh every 30 seconds and on focus; private customer pages are not polled. Search responses revalidate in the browser rather than keeping an additional 30-second browser cache. Product details retain uncached reads.
- Applied the existing `products.status = 1` publication rule consistently to public detail, category, subcategory, brand, random, featured and top-selling product reads. Main product listing already required active status. No new price, stock, image or category eligibility restrictions were introduced. Invalid/inactive detail IDs return 404; unknown category filters return empty success.
- Eager-loaded catalog relationships, retained encrypted identifiers, used those identifiers for card keys, and included category context when matching best-selling recommendations with randomized encrypted resource IDs. Cards now display their category before the brand fallback.
- Preserved backend-selected selling prices, MRP/discount presentation, real storage images and the existing accessible missing-image fallback. Active zero-stock and image-less products remain visible. Featured products still require admin curation; best sellers still derive from existing order records. New products appear in the latest homepage section and paginated catalog without being automatically featured or assigned invented sales.
- Corrected an existing admin-provisioning regression test to isolate and restore environment adapters. Local provisioning settings had caused that test to fail. The seeder and application provisioning behavior were not changed.

## API and browser verification

Normal MySQL product: **Premium Shirt**, category Clothing, selected `special_price` ₹1,100, MRP ₹1,500, stock 10. API response, image HTTP 200, storefront card and encrypted detail navigation were verified in Chromium. The image is served from this Laravel application's `/storage/Products/` path. No storage symlink or existing product file needed modification.

New SQLite databases under `storage/framework/testing/vanthara-checkout-catalog-20261116-{a,b,c}/` were used for all mutations. The CLI harness authenticated a synthetic admin and invoked the real Blade category/product/featured routes through Laravel's HTTP kernel. It did not insert product rows directly or expose fixture mutation endpoints. Test image uploads use a separate test storage root. Checkout is disabled in the catalog HTTP harness.

The final production-mode Chromium suite verified:

1. Warm caches → admin category/product creation → public API → homepage, shop, category and search listings; uploaded image loaded, correct selected price/MRP/discount, one card per product, encrypted detail link.
2. Featured selection, name/price/stock edits, inactive removal, old encrypted detail API rejection, new category visibility and missing-image fallback. Mobile category screenshot at 393px showed no document overflow; no unexpected browser console/page errors occurred in the mutation journey.
3. Missing, incorrect and non-ASCII notification credentials rejected with 401; correct credential accepted. Timed revalidation recovered an edit made with notifications disabled.

Screenshots and browser results: `storefront/artifacts/catalog-sync/`:

- `admin-created-shop.png`, `admin-created-detail.png`
- `new-category-mobile.png`
- `real-admin-catalog-desktop.png`, `real-admin-detail-desktop.png`, `real-admin-home-mobile.png`
- `results.json`, `http-checks.json`, failure traces if generated

Earlier responsive QA was not repeated wholesale. The new catalog flow was tested in actual locally launched Playwright Chromium, independently of any connected browser session.

## Final verification results

| Check | Result |
| --- | --- |
| Complete isolated Laravel suite | 65 tests, 424 assertions passed |
| Frontend unit/contract/transport suite | 31 tests passed |
| Catalog Playwright suite | 3 tests passed |
| TypeScript | Passed |
| Next production build | Passed; final build restored to the normal local API configuration |
| Laravel admin Vite build | Passed |
| Read-only HTTP checks | 16 passed: list/category/search/pages/image and expected 404 responses for invalid IDs, private documents and `.env` |
| Scoped PHP formatting / diff whitespace checks | Passed |
| Normal data/environment preservation | Root `.env` and `storefront/.env.local` hashes unchanged; product count, active status, stock, prices and timestamps unchanged |

The first full Laravel run exposed the provisioning-test environment contamination described above; the final full run is green. No outstanding failure remains in these checks. Vite emitted a Node `module.register()` deprecation warning without failing the build.

Raw database/API evidence, environment preservation results and Laravel JUnit output are under `storage/framework/testing/catalog-sync-20261116/`. Before modifications, 332 existing source/config/test files were backed up with a SHA-256 manifest under `storage/framework/testing/backups/catalog-sync-20261116-125858/`. Pre-existing order/payment/security changes were preserved; they were not reset or reformatted broadly.

## Files affected

Modified:

```text
.env.example
app/Http/Controllers/ProductController.php
app/Http/Controllers/FeaturedProductController.php
app/Http/Controllers/Api/ProductController.php
app/Http/Controllers/Api/FeaturedApiController.php
app/Http/Controllers/Api/UtilityController.php
app/Models/Product.php
app/Providers/AppServiceProvider.php
storefront/.env.example
storefront/package.json
storefront/README.md
storefront/src/app/layout.tsx
storefront/src/app/page.tsx
storefront/src/app/api/search/route.ts
storefront/src/components/catalog.tsx
storefront/src/lib/contracts.ts
storefront/src/lib/transport.ts
tests/Feature/StorefrontRemediationTest.php
```

Added:

```text
config/catalog.php
app/Observers/CatalogChangeObserver.php
app/Services/Storefront/CatalogInvalidation.php
scripts/local-api-router.php
storefront/scripts/dev-local.mjs
storefront/src/lib/catalog-cache.ts
storefront/src/components/catalog-freshness.tsx
storefront/src/app/api/catalog/revalidate/route.ts
storefront/playwright.catalog.config.ts
storefront/tests/catalog/synchronization.spec.ts
tests/Feature/StorefrontCatalogTest.php
tests/Storefront/catalog-fixture.php
tests/Storefront/catalog-router.php
docs/STOREFRONT-CATALOG-SYNC.md
```

No database migrations or dependencies were added. Existing PHP files received only scoped formatting alongside the catalog changes. Existing financial/order runtime code and the admin-provisioning seeder were not modified by this task.

## Operating the normal local storefront

From `storefront/`, run:

```powershell
npm.cmd run dev:local
```

Open `http://127.0.0.1:3000`. Laravel runs on `http://127.0.0.1:8137`, including its existing authenticated admin routes. If ports are already occupied, the launcher explains the conflict and leaves those processes untouched. It never silently falls back to port 8000, the isolated catalog or a public API. MySQL/Laragon must be running and PHP must be on PATH. With an already-running normal Laravel server, run `npm.cmd run catalog:check` and `npm.cmd run dev` separately instead of starting a second pair.

For independently hosted Laravel/Next processes, set these optional **server-only** values using deployment-managed configuration:

```text
Laravel: STOREFRONT_CATALOG_REVALIDATE_URL=<Next origin>/api/catalog/revalidate
Laravel and Next: STOREFRONT_CATALOG_REVALIDATE_SECRET=<same random value, at least 32 characters>
```

Use HTTPS outside loopback. Rebuild Laravel's configuration cache and restart Next after environment changes, after checking the intended local/staging database configuration. No secret belongs in `NEXT_PUBLIC_*`. The local paired launcher supplies its own ephemeral credential; another Laragon admin process outside that pair uses timed revalidation unless its persistent notification settings match a separately configured Next process.

The notification expires server data for the next request; it does not push into browsers. Existing visible tabs refresh on their timer/focus. Without notifications, fallback is request-driven stale-while-revalidate and may need a following request before updated data is rendered. Direct SQL/bulk writes that bypass model events are covered by this timed fallback. Large catalogs still use the existing full-array API and client-side page slicing; backend pagination would require a separate compatible API improvement.

## Reproducing the isolated browser test

Choose a new `catalog-*` label; setup refuses to overwrite an existing database. Use the same label in the helper/API/test terminals. The following credential is intentionally a test fixture, never a deployment secret.

```powershell
# Laravel root: initialize once for a NEW label.
$env:STOREFRONT_TEST_RUN = 'catalog-new-run'
php tests/Storefront/setup.php
php tests/Storefront/catalog-fixture.php prepare
$env:STOREFRONT_CATALOG_REVALIDATE_URL = 'http://127.0.0.1:3150/api/catalog/revalidate'
$env:STOREFRONT_CATALOG_REVALIDATE_SECRET = 'catalog-test-invalidation-secret-not-for-production'
php -S 127.0.0.1:8140 tests/Storefront/catalog-router.php
```

```powershell
# Separate storefront terminal; stop any task-owned QA server on 3150 first.
$env:LARAVEL_API_ORIGIN = 'http://127.0.0.1:8140'
$env:STOREFRONT_URL = 'http://127.0.0.1:3150'
$env:STOREFRONT_CATALOG_REVALIDATE_SECRET = 'catalog-test-invalidation-secret-not-for-production'
$env:STOREFRONT_SANDBOX_TRANSACTIONS = 'false'
npm.cmd run build
npm.cmd run start -- --port 3150
```

```powershell
# Separate storefront test terminal.
$env:STOREFRONT_CATALOG_TEST_RUN = 'catalog-new-run'
npm.cmd run test:catalog
```

The suite expects the fresh empty catalog prepared by this harness. Retain completed databases for evidence and use a new label for another run. Clear temporary test origin overrides and rebuild with the normal `.env.local` when finished; do not publish a QA build or enable checkout.

## Remaining limitations

No known local catalog integration blocker remains. The application servers must be running; a stopped local Laravel server cannot synchronize products. Only the existing MySQL catalog was read during verification; destructive/creation tests used isolated SQLite. No live server, production deployment, customer transaction or payment was exercised. Previous release/security/payment acceptance requirements remain outside this catalog fix; this report does not authorize production checkout.
