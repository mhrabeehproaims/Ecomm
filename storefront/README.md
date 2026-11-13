# Vantara storefront — phase 1

Independent Next.js App Router + React application. The Laravel API, database schema, root Vite setup and Blade admin are unchanged. This is the storefront foundation, not a completed transactional store.

## Run locally (Windows PowerShell)

From the Laravel root, in one terminal:

```powershell
php artisan serve --host=127.0.0.1 --port=8137 --no-reload
```

In another terminal:

```powershell
cd C:\laragon\www\vanthara-main\storefront
Copy-Item .env.example .env.local
npm.cmd ci
npm.cmd run dev
```

Open http://127.0.0.1:3000. Use `npm.cmd` on Windows when PowerShell blocks npm.ps1. Node >=20.9 is required; Node 26.12.1 was available during development. Exact package versions are locked in package-lock.json; Next.js is pinned to 16.4.6. Never run Laravel migrations/seeders against production for this frontend.

For an existing Laragon virtual host, set `LARAVEL_API_ORIGIN` to that origin (no `/api` suffix). Change `STOREFRONT_URL` to the storefront's real origin. Environment changes require restarting Next.js; changing media origin also requires a new production build. No credentials are needed in frontend environment variables. Use HTTPS for public storefront and API media origins.

```powershell
npm.cmd run typecheck
npm.cmd test
npm.cmd run build
npm.cmd run start
# With the production server running:
npm.cmd run smoke
```

## Implemented

- Shared responsive header, actual Vantara logo, search suggestions, horizontal category navigation, footer, five-item mobile navigation, focus styles and skip link.
- Editorial homepage with real Laravel categories, active/date-filtered banners, featured products, best-selling products, newest active products, special offers and brands when records exist. Offer collections require a valid backend offer amount (including a genuine zero offer), and omit missing/invalid amounts. Empty sections display truthful empty states or are omitted. No invented inventory, customer counts, reviews, discounts or free shipping. Newsletter omitted.
- Server-rendered catalog, keyword search and exact category filtering; brand/subcategory/stock/maximum-price filtering, sorting and 12-item pagination over the loaded full catalog response. The UI documents this API limitation. No query parameters are passed blindly to Laravel.
- Category landing/index pages; read-only product details, multi-image gallery, specifications, backend-selected prices/offers, active-catalog check and related products. Rich descriptions are rendered as plain text; raw comments/customers are discarded for privacy.
- Runtime-validated API schemas, fixed server-only Laravel origin, six-second timeout, explicit invalid/unavailable/empty states, 60-second public cache, fresh detail reads, same-origin allowlisted search handler. No bearer tokens or generic open proxy.
- Metadata, home/catalog/category canonicals, conservative sitemap, product structured data, local optimized editorial hero, lazy media below the fold and reduced-motion styles. Private/HTTP development media is served without Next's server-side optimizer; public configured HTTPS storage can use it. Media errors show a fallback.
- Live return-policy page reads active policies from `GET /api/return-policies` using validated title/window/type/conditions fields and removes internal fields. An empty response differs visibly from an unavailable API; no terms are invented. Policy HTML is displayed as plain text. About/help/privacy/terms/shipping/contact/wholesale pages retain clearly provisional content. Account/wishlist/bag pages disclose unavailable phase-2 features; they do not simulate sessions or transactions.

## Structure

```text
src/app/                     App Router pages, metadata, error/loading boundaries
src/app/api/search/route.ts   Read-only, allowlisted search suggestion endpoint
src/components/              Layout, search, catalog cards, catalog view and media
src/lib/api.ts               Server-only catalog services / React request deduplication
src/lib/transport.ts         Typed HTTP transport and error classification
src/lib/contracts.ts         Zod contracts and inferred TypeScript types
src/lib/catalog.ts           Price, filter, opaque-link and recommendation utilities
src/lib/media.ts             Laravel storage URL normalization
src/app/globals.css          Shared visual tokens and responsive styling + Tailwind
public/                      Copied existing logo/favicon + decorative hero
tests/                       Synthetic contract fixtures and real HTTP transport tests
scripts/smoke.mjs            Read-only production-server smoke checks
../docs/STOREFRONT-AUDIT.md   Source audit, API matrix, backend gaps and phased plan
```

Interactive state currently consists only of debounced search suggestions and image-error fallbacks. TanStack Query, Zustand, React Hook Form, Radix and Motion are deliberately deferred until customer mutations, forms and dialogs need them. Native semantic forms/disclosures handle the foundation without unnecessary client JavaScript.

## Release constraints

- Local API collections were empty during verification. Populated catalog rendering and customer mutations were not verified against real inventory/accounts. Unit tests use explicitly synthetic fixtures confined to tests.
- Product IDs are randomized Laravel ciphertext, not stable numeric IDs or slugs. Readable product paths carry an encoded opaque `id` query. Preserve it exactly. Product pages remain noindex and are omitted from sitemap pending a stable backend locator and resolver. Global indexing defaults off (`STOREFRONT_INDEXABLE=false`). Approve copy, finish release QA and address stable URLs before enabling indexing.
- No customer login, registration, cart/wishlist mutation, address management, checkout, payment, returns or enquiry submission is enabled in this phase. No credentials/payment SDK or token storage is introduced. See the audit for actual endpoint contracts and the required security/business-logic review.
- Before enabling checkout, obtain approval for the documented backend fixes: quote/totals/address/delivery, stock concurrency, coupon rules, pending-payment lifecycle, ownership/capture checks, idempotency and webhook handling. A frontend Razorpay callback alone is never treated as payment proof.
- No external deployment was requested or performed. Run Next.js alongside Laravel; do not replace Laravel's `/` admin login. Configure a dedicated storefront domain/process for deployment.

## Verification record

Verified 13 November 2026:

- `php artisan route:list --path=api --except-vendor --json`: 56 registered routes. Several resource actions are stubs, as identified in the audit.
- Local read-only Laravel connectivity: categories, products, subcategories, banners, featured, best sellers, brands and return policies returned HTTP 200 with empty collections. No records were inserted to manufacture inventory.
- `npm.cmd run typecheck`: passed with strict TypeScript.
- `npm.cmd test`: 25 passed, 0 failed. Covers encrypted ID URLs, selected/offer/missing/zero prices, randomized-ID recommendation matching, filters/sort, storage URLs, banner destinations/date boundaries, raw review privacy, return-policy contracts/internal-field removal/safe rendering/empty vs failure states, and real local HTTP transport success/error/redirect/invalid responses. Windows sandbox blocked `os.userInfo` in tsx; tests passed using approved process access outside that sandbox.
- `npm.cmd run build`: passed on Next.js 16.4.6; 18 static pages generated, with dynamic catalog/category/detail/search/sitemap routes. Both Laravel Vite lockfile and standalone storefront lockfile are retained; tracing/Turbopack roots are explicitly scoped to storefront.
- `npm.cmd run smoke`: 16 passed on the production server, including main pages, offer collection, live return-policy page, explicit phase-2 states, sitemap, robots, same-origin search, the local WebP hero, development noindex, response headers and HTML-escaped search input.
- Local API outage: after stopping only the task-owned Laravel process, an uncached search request returned HTTP 503; an uncached catalog page displayed its unavailable state and did not expose SQL errors. Laravel was restarted for final build verification. No customer or transaction calls were made.
- Dependency installation reported zero known vulnerabilities at installation time.

Browser visual QA requires a connected browser; no browser surface was available in this session (browser inventory was empty). CSS has mobile breakpoints and accessible controls but has not been visually signed off on devices. Populated catalog/details, real device responsiveness and authenticated/payment flows remain unverified. Task-owned preview processes were stopped after verification; use the commands above to launch again.

## Hero provenance

`public/hero.webp` is decorative editorial artwork generated with the built-in imagegen tool, then resized/converted to WebP (1400px, about 129KB) using Sharp. It is not a product listing. Existing Laravel product/category media is never replaced by generated inventory images.

Final generation prompt:

> Use case: photorealistic-natural. Asset type: decorative editorial homepage hero for Vantara, a premium everyday shopping storefront with a green leaf logo and plum accents. Create a sophisticated photographic still life: a single lush green branch in a sculptural warm ivory ceramic vase, a folded unbleached linen cloth and a curved plum-colored sculptural object on a pale warm limestone pedestal. Sunlit cream studio backdrop, soft diagonal botanical shadows, tactile natural materials, restrained elegant composition, bright natural morning light. Landscape 3:2 composition, objects grouped center and right with breathing room. No products for sale, no packaging, no food, no labels, no branding, no text or watermarks. This image is decorative atmosphere, not a catalog product photo.
