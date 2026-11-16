# Vanthara fashion data: separate approval proposal

Date: 16 November 2026. This document proposes future work. **No migrations, schema changes or inventory edits were made for the fashion redesign.**

## Verified present model

The existing Laravel product resource supplies an encrypted product identifier, category/subcategory, brand, one optional size relation, product-level stock, selected selling-price field, optional offer/MRP, description and multiple images on the detail response. Admin-created active products use the same public catalog. The current local catalog has Clothing / Shirt and one Medium product.

There is no verified color/variant relation, variant stock, independent public SKU, fabric/composition, fit, care, measurements, size chart, model measurements or outfit association in this contract. HSN is a tax classification, not an apparel SKU. The redesign therefore omits those unsupported attributes and filters. A product with a size requires that actual size to be selected before adding its existing encrypted product ID and quantity. This is a single-size product confirmation, not a variant inventory system.

## Minimal future changes requiring approval

| Capability | Proposed minimum | Compatibility / safeguard |
| --- | --- | --- |
| Multiple sizes/colors | Product variants with immutable IDs, parent product ID, explicit size/color references, unique SKU and active state | Add optional resources/fields; preserve existing encrypted parent IDs and unsized products. Do not split historical order lines silently. |
| Variant availability | Stock and reservation rows keyed to the actual variant | Extend authoritative price/stock validation, row locking, reservation expiry, returns and payment recovery together. Never accept browser stock or prices. |
| Apparel information | Nullable composition, fit and care text with admin validation | Publish only entered, approved facts; no generated material or sustainability claims. |
| Size guidance | Versioned size chart with measurement names, units and garment/body distinction; optional product chart relation | Business supplies measured values. Snapshot the applicable size/variant description on new order lines. |
| Photography | Optional variant image assignments and explicit display ordering | Keep existing product image links and storage behavior compatible. Require real product photography. |
| Model information | Optional approved model measurements and worn size | Display only supplied information; campaign models do not establish product sizing. |
| Complete the look | Explicit editorial look and product associations with active/public visibility checks | An editorial photograph alone must never imply the depicted outfit is available. |

Before implementation, agree on SKU uniqueness, size vocabulary/units, whether prices differ by variant, stock migration/reconciliation, legacy-product behavior, exchange treatment and refund calculations. Then prepare a reversible local migration and admin/API changes for approval. Test encrypted identifier handling, ownership, variant-level concurrent checkout, price changes and historical snapshots in isolated databases before any production rollout.

## Intentionally inactive now

Color swatches, XS–XL ranges, unavailable variant combinations, fabricated size guides and shoppable looks are not rendered. The homepage does not invent best sellers or featured products when their real endpoints return empty results. Production checkout remains disabled under the existing release controls.
