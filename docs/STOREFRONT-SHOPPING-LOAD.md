# Cart and wishlist loading improvement

Date: 16 November 2026. Local implementation only; checkout remains disabled.

The pages render their shell before client-side private data becomes available. Previously `CustomerScreen` requested the session, waited for it, and then requested cart or wishlist data. That page read authenticated again. The header separately requested a summary that fetched customer, cart and wishlist data. A direct visit therefore issued three browser API reads and six private Laravel requests. PHP's local development server handles those requests serially, amplifying the delay. Development compilation can add a separate first-visit cost.

## Change

- Added authenticated `GET /api/shopping-state` under the existing Sanctum, active-customer and customer-rate-limit middleware. It composes the existing cart and wishlist read contracts, with the customer's own delivery/profile data. It does not write cart records, recalculate prices or change any existing endpoint contract.
- Next.js `GET /api/customer/shopping-state` validates and strips the upstream response, retains encrypted product identifiers and returns only the whitelisted private customer/product/cart DTOs.
- Cart/wishlist pages and header badges join the same currently running request. The shared promise is removed when it settles; there is no persistent private-data cache.
- Cart and wishlist changes invalidate and abort an older in-flight read. Unmounted/replaced page loads ignore stale responses. Header and page state update from the same result. Logout and subsequent customer login do not reuse another customer's data.
- Other header reads also use one authenticated Laravel read, while the browser-facing summary continues to expose only counts/saved-product keys, not customer profile information.
- Private upstream fetches and both Next.js/Laravel responses remain `no-store`. The public catalog cache, authentication checks, CSRF/origin checks and financial/write operations are unchanged.

## Measured results

Real Chromium, 393px viewport, local production build, same isolated SQLite sample customer and PHP test server; three direct-load samples per page. Timings include navigation until the first real item is visible. These are local diagnostic measurements, not production latency guarantees.

| Page | Before median | After median | Private browser reads | Private Laravel reads |
| --- | --- | --- | --- | --- |
| Cart | 2,408 ms | 892 ms | 3 → 1 | 6 → 1 |
| Wishlist | 1,931 ms | 880 ms | 3 → 1 | 6 → 1 |

Raw measurements: `storefront/artifacts/shopping-load/before.json` and `after.json`. Path-only request logging runs only when explicitly enabled in the isolated test router; it records no tokens, cookies, credentials or request bodies.

## Verification

- Laravel regression suite: 77 tests / 542 assertions passed, including new ownership, inactive/anonymous access, freshness, read-contract and no-cart-write tests.
- Frontend: 44 tests passed, including in-flight deduplication, no settled-response caching and mutation cancellation.
- TypeScript and the production build passed.
- Real isolated browser journey passed: quantity changes, matching bag counts, wishlist remove/save, private headers, summary field restrictions, logout and a second customer receiving an empty own cart/wishlist. No unexpected script exceptions.
- Normal data was not used for mutations. Browser tests accepted only the identified isolated API and ports, with array mailer and no live payments.

Main files: `ShoppingStateController.php`, `routes/api.php`, `customer-shopping.ts`, `shopping-contracts.ts`, `shopping-request.ts`, `customer-cart.ts`, `customer-client.ts`, the Next customer route, `CustomerScreen`, `ShoppingProvider`, focused unit/feature tests and the isolated benchmark/journey scripts. The pre-change files are backed up under `storage/framework/testing/backups/shopping-load-20261116-173051/`.

A fresh network read is still required for private pages. They should load much sooner; an entirely instantaneous result is not promised. A first request in `next dev` can still include compilation time.
