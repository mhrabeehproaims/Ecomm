# Vanthara storefront — browser QA and UI refinement

Date: 13 November 2026. This is the historical browsing QA report. The later [transactional integration report](STOREFRONT-TRANSACTIONS.md) supersedes its customer-feature limitations and documents isolated sample-database writes. No Laravel business logic, admin templates or production data was changed.

## Environment and evidence

The existing app is Next.js 16.4.6 App Router with React and Tailwind 4, not a Vite SPA. It was retained. Browser QA used local Playwright 1.65.0 with Chromium 158.0.8127.18 on Windows. No connected-browser tool was available; the user explicitly authorized local Playwright installation and execution.

- A local production preview on port 3001 used **read-only GETs** to the confirmed public Vanthara catalog, which returned seven active products. These supplied authentic names, prices, categories and images; no products were invented or inserted. This preview was used because the local Laravel API returns empty collections.
- A separate production preview on port 3000 used the existing local Laravel app on port 8137 for empty/error/not-found and anonymous authorization checks. The final build was restored to the default local configuration. No `.env` or backend configuration was edited.
- The [public brand site](https://vanthara.com) was captured at 393 and 1440px with all non-GET/HEAD browser requests blocked. Two automatic `POST /api/track-visit` calls were blocked, so the comparison did not submit visitor records. Its [public application bundle](https://vanthara.com/static/js/main.480d5ba2.js) and the supplied logo confirmed plum `#7C0046` and green `#1C3F1D`.
- Source audit, API contracts and known backend issues remain documented in [STOREFRONT-AUDIT.md](STOREFRONT-AUDIT.md).

Evidence is saved under `storefront/artifacts/visual-qa/` (ignored build/test output):

| Location | Contents |
| --- | --- |
| `before/` | Seven original homepage viewport-width captures, original mobile catalog and measured font/header observations |
| `after/` | Final screenshots, viewport crops, per-width browser metrics and search success/controlled-failure screenshots |
| `reference/` | Public vanthara.com desktop/mobile comparison captures |
| `local/` | Actual local empty/not-found/API-outage captures and authorization results |
| `results.json`, `playwright-report/index.html` | Latest development-server Playwright result and HTML report |
| `production-results.json` | Archived passing production-build Playwright result |
| `initial-results.json` | Initial development run: eight passed, two failed; see fixes below |
| `accessibility-performance.json` | Scoped rendered CSS contrast and unthrottled local timing/bundle samples |

`*-viewport.png` files are first-viewport crops of the full-page Chromium screenshots. Fixed mobile navigation appears at the original viewport bottom in full-page captures; it stays at the visible viewport bottom during real scrolling.

## Pages and widths tested

All seven widths **360, 393, 430, 768, 1024, 1440 and 1920px** were tested on the production build for:

- Homepage
- Product listing
- Category index
- Real product detail (Rattan table lamp, with three actual gallery images)
- Cart availability page
- Account availability page
- Wishlist availability page
- Published return-policy page

These are 56 page/viewport combinations. About, contact, wholesale, FAQ, shipping, privacy and terms were also captured and checked at 393 and 1440px: 14 more combinations, **70 in total**. There were no detected document-level horizontal overflows, broken rendered product images or unexpected JavaScript/console errors in the core 56-page scan. Mobile navigation was checked below 768px; desktop/tablet layouts above that breakpoint correctly hide it. Sticky header position and mobile navigation target dimensions were asserted in Chromium.

## Confirmed issues and completed fixes

| Finding | Change |
| --- | --- |
| Original mobile secondary text measured 7–8px; search input 11px | Increased readable text sizes and spacing; mobile search/form inputs use 16px; mobile navigation labels use 11px |
| Footer text contrast about 3.07:1, card categories about 3.99:1, helper text about 3.65:1 | Darkened secondary text and applied verified brand colors; selected rendered contrast scan now reports no failures |
| Smaller-screen header labels were `display:none` without explicit accessible names | Added permanent accessible labels and hid decorative icon shapes from assistive technology |
| Header did not stay available during scrolling | Added a sticky header, adequate scroll offsets and a short-landscape exception |
| Category navigation could grow beyond its wide-screen container | Kept horizontal scrolling inside the navigation at every width |
| Product descriptions/names produced variable card content positions | Added consistent card structure, three-line heading slots, size/status slots, flexible pricing areas, stable image ratios and 44px explore controls |
| Tablet used four narrow product columns | Added a three-column tablet grid, preserving two columns on phones and four on desktop |
| Successful empty featured collection occupied a large panel above actual products | Omit that section when other products exist and no featured products were published; retain truthful error/entire-catalog-empty states |
| Single published category looked isolated in a six-column grid | Added a compact category tile layout for a single real category |
| Mobile filters occupied much of the initial screen | Added a native keyboard-operable Filter & sort disclosure; active filters remain visible |
| Mobile product title fell below the gallery | Moved the single semantic product heading above the gallery on mobile, retaining the desktop two-column composition |
| Product gallery was a long stack of images | Added thumbnail selection, previous/next controls, selected-state announcements and a fallback to the first available real image |
| Browser Escape on `type=search` cleared the query before reopening suggestions | Prevented the native clearing action when dismissing suggestions; verified Escape → ArrowDown → Enter opens the selected real product |
| Search suggestions lacked complete keyboard selection semantics | Added combobox/listbox/option semantics and ArrowUp/ArrowDown/Enter support |
| Product details incorrectly said policies were pending even with a live policy page | Linked to current published return-policy information without promising eligibility |
| Root category fetch delayed the shared shell; unfiltered catalog reads were sequential | Stream category links behind Suspense and start independent catalog/category requests together |
| Generic image sizing and lazy initial catalog image | Added breakpoint-aware image sizes and prioritized the first catalog image while keeping below-fold images lazy |
| Mobile navigation had no active destination indication | Added current-route highlighting and `aria-current` semantics |
| Display text inconsistently spelled the brand | Standardized visible UI/metadata to Vanthara while retaining existing filenames and API identifiers |

The pre-existing decorative hero asset was retained and its composition/type scale refined. Real product/category imagery continues to come from the actual API. No generated images substitute for inventory photography.

## Functional browser findings

| Journey | Result |
| --- | --- |
| Homepage → category → product list | Passed with real public-catalog data |
| Search suggestions with keyboard, dismiss/reopen, selected product navigation | Passed |
| Product images, gallery thumbnails and previous control | Passed with the actual three-image product |
| Maximum-price filtering to zero results and Clear all | Passed |
| Search suggestions receiving a controlled 503 | Recoverable message shown; normal full search remains usable. This scenario is deliberately intercepted and is not a claim about a real production outage. |
| Skip link and accessible header shortcuts | Passed |
| Mobile Bag/Account navigation and active destination | Passed |
| Account/cart/wishlist content | Correctly discloses unavailability; no fake session, basket count or transaction success |
| Return policies | Actual API integrated; real local empty state checked separately |
| Anonymous protected API reads | Cart, wishlist, order history, customer and delivery-address GETs reject unauthenticated callers with HTTP 401 |
| Login/register/logout, wishlist/cart mutations, quantity/coupons, checkout, payment states and order history for a customer | **Not implemented/verified end to end.** No customer credentials were supplied, and no state-changing production API requests were made. These are release blockers, not passing journeys. |

No existing modal/drawer checkout/account implementation exists to review. The new filters use a native disclosure; the gallery stays inline. Razorpay was not invoked and no callback or payment mutation was submitted.

## Accessibility, performance and SEO

- Keyboard skip link, icon-link names, gallery controls, search selection, filter disclosure and active mobile route states were exercised in Chromium. Mobile bottom-nav controls meet at least 44×44 CSS pixels; other primary buttons/gallery/product controls are at least 44px high by design.
- The selected desktop CSS contrast scan checked **310 text samples across five pages with zero failures**. It accounts for CSS background compositing but does not sample photo pixels and is not a full WCAG audit or assistive-technology certification.
- Sampled warm local production loads showed eight JavaScript requests, about **142 KiB encoded JavaScript**, and zero observed layout shift in those samples. Timings are saved in the JSON evidence. They are unthrottled localhost measurements, not mobile network/Lighthouse or field Core Web Vitals results.
- Public pages retain semantic headings, canonical metadata where stable, image dimensions/aspect ratios, deferred below-fold media, reduced-motion support and default noindex during development. Product pages remain noindex because the backend has no stable public product locator; randomized encrypted IDs are preserved unchanged in encoded queries.
- No runtime UI library was added. Playwright is a development-only testing dependency.

## Verification results

- Existing automated suite: **25 passed, 0 failed**.
- Strict TypeScript: **passed**.
- Production builds for the read-only public preview and default local configuration: **passed**.
- Final production Playwright suite: **12 passed, 0 failed**, covering 70 layouts plus functional journeys.
- Initial development Playwright run: eight passed, two failed. One was the real Escape-key bug described above; the other was an overly broad test locator matching native selects as well as the search combobox. Both were corrected, and three focused regression tests passed before the full production rerun.
- The repeat development-server run added persistent card-height/price-alignment and mobile-footer-clearance assertions to the main suite. All seven viewport tests passed those assertions, replacing the earlier interrupted one-off geometry probe with reproducible coverage.
- HTTP smoke suite: **16 passed**, including metadata, headers, search, policy and offer pages.
- Actual local checks: **five empty/not-found browser states and five anonymous protected API GETs passed**. The outage check verified search HTTP 503 and a visible unavailable-catalog state; evidence and results are saved in `local/`.
- Dependency installation reported no known vulnerabilities at installation time.

## Files changed

- `storefront/src/app/globals.css`: verified tokens, readable typography, responsive grids/cards, sticky navigation, gallery/filter controls, touch/focus/overflow rules.
- `storefront/src/components/layout.tsx`, `mobile-navigation.tsx`, `search.tsx`: streamed category links, named shortcuts, active routes and corrected keyboard search.
- `storefront/src/components/catalog.tsx`, `catalog-view.tsx`, `product-gallery.tsx`: aligned real product cards, category tiles, image loading, collapsible filters, parallel reads and image selection.
- `storefront/src/app/page.tsx`, `products/[slug]/page.tsx`: empty-featured handling and revised product detail composition/policy text.
- UI brand spelling/metadata across existing app pages and components; API identifiers and filenames remain intact.
- `storefront/playwright.config.ts`, `tests/browser/storefront.spec.ts`, `scripts/browser-*.mjs`: repeatable browser/evidence/contrast/local-state checks.
- `storefront/package.json`, `package-lock.json`, `.gitignore`: Playwright development dependency, browser-test command and ignored generated artifacts.
- `storefront/AGENTS.md`: generated automatically by the installed Next.js development server; its local documentation instruction was followed.
- `storefront/README.md`, `docs/STOREFRONT-AUDIT.md`, this report: commands, evidence and accurate verification/release status.

## Remaining release blockers

1. Customer authentication/session, wishlist/cart mutations, addresses, checkout, coupons and customer order views remain unfinished frontend workflows. The existing availability pages must not be mistaken for a fully functional shop.
2. Backend quote/totals/address/delivery rules, cart validation, stock concurrency, coupon rules, payment lifecycle/ownership/capture/idempotency/webhooks and return authorization require the separately approved backend work identified in the audit. No such business logic was changed here.
3. The local database has no published inventory; authentic product browsing was therefore verified through the separate public read-only preview. Dedicated nonproduction accounts/data and Razorpay test mode are needed for safe transactional acceptance testing.
4. Stable product URLs, approved privacy/terms/shipping/business copy and production indexing remain pending.
5. Chromium viewport testing does not replace physical-device, Firefox, Safari/WebKit, screen-reader and throttled-network QA. No passing field Core Web Vitals claim is made.

The verified browsing UI has no known critical issue within the tested scope. **The store is not ready for transactional production release.** No deployment was performed. See `storefront/README.md` to run the app and repeat the browser checks.

## Requested local Playwright repeat

The subsequent local-browser request was executed without a connected browser session. `npm.cmd ls` confirmed Playwright 1.65.0 was already installed, and Playwright's Chromium executable was present; no package or browser installation was repeated. The existing Next.js development server was started locally and Chromium completed a fresh **12/12 passing run** with screenshots at all requested widths. The populated-catalog tests continued to use public read-only data; the local API was independently confirmed empty, with protected reads returning 401.

The repeat run found no new UI defect requiring a runtime storefront change. Only the browser regression checks and verification documentation were strengthened. The existing 25 tests and TypeScript checks passed again, and the production build was rerun with the default local API configuration. Screenshot files and the latest HTML/JSON browser report were refreshed; archived production results were retained separately.

`card-alignment.json` summarizes the captured browser geometry: zero per-row card-height or price-position spread at each of the seven widths. The normal local production smoke checks passed again (16 HTTP checks plus five browser states and five anonymous protected API reads). Next.js development emitted image-priority advisories for some lazily loaded cards; these were warnings, not browser console errors or broken images. Physical-device and transactional limitations above still apply.
