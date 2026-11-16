# Product detail spacing and missing information

Verified locally on 16 November 2026. No Laravel, product records, stock, prices, publication status or checkout configuration changed.

## Cause and correction

The desktop gallery spans both product grid rows. Automatic row sizing stretched the heading row to fill the gallery height. The observed title-to-information gaps were approximately 185px for Pants and 50px for Premium Shirt. Defining the heading row as `min-content` keeps this gap at 24px at 768, 1024, 1440 and 1920px, regardless of missing size information. Mobile retains the intentional title, gallery, information order.

Products missing a size show their actual collection and brand where available, plus a contact link for sizing/product questions. Out-of-stock products offer wishlist guidance, an in-stock collection filter and up to two actual available products from that collection. Products without an identified collection receive no invented same-collection recommendations. Prices, links and images come from the existing catalog; encrypted identifiers remain intact. Empty descriptions are omitted instead of rendering generic filler. Quantity controls are hidden when the item cannot be purchased.

## Files

- `storefront/src/app/products/[slug]/page.tsx`
- `storefront/src/app/fashion.css`
- `storefront/src/components/product-actions.tsx`
- `storefront/src/components/product-support.tsx`
- `storefront/tests/product-support.test.ts`
- `storefront/scripts/product-empty-details-browser.mjs`

Pre-change file backup: `storage/framework/testing/backups/product-empty-details-20261116-175419`.

## Verification

- Frontend suite: 50 tests passed.
- TypeScript and Next.js production build passed.
- Real Chromium browser verification against the local storefront and Laravel catalog at 360, 393, 430, 768, 1024, 1440 and 1920px: no horizontal overflow or browser script errors.
- Checked Pants without a size/out of stock and Premium Shirt with a real Medium size/in stock. Confirmed unavailable quantity controls are absent, selection still enables Add to bag, and an alternative link opens the correct real product.
- Browser automation blocks non-GET/HEAD API requests; no shopping or product data was written.
- Screenshots and measured results: `storefront/artifacts/product-empty-details/`. Mobile heading-to-information measurements include the intentional intervening gallery; desktop measurements are all 24px.

Re-run: `npm.cmd test`, `npm.cmd run typecheck`, `npm.cmd run build`, and `node scripts/product-empty-details-browser.mjs` from `storefront` with the local servers running.
