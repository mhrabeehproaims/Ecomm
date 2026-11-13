# Vantara storefront audit and implementation plan

Audited 13 November 2026. This is a source audit, not a claim that every workflow has passed an integration test. No migrations, seeders, customer writes, orders or payments were executed.

## Workspace

```text
app/Http/Controllers/Api/   Customer REST controllers
app/Http/Controllers/      Blade admin, checkout, comments and analytics
app/Http/Resources/        Catalog and purchase-history serializers
app/Http/Middleware/       Optional Sanctum, admin auth, last-seen updates
app/Models/                Catalog, customers, stock, orders, offers and payments
bootstrap/app.php          API registration and middleware aliases
database/migrations/       Catalog/customer/order/payment schema history
resources/views/           Existing Blade admin (preserved)
resources/js/, css/        Laravel Vite assets; no existing React app
routes/api.php             56 registered API routes including resource stubs
routes/web.php             Admin login at /, dashboard at /dashboard
public/assets/logo/        Existing Vantara logo and favicon
tests/                     Laravel example tests only
storefront/                Separate Next.js application added in phase 1
```

Laravel 12, Sanctum 4, PHP 8.2+, MySQL and Razorpay are confirmed by composer and source. Root package.json is only Laravel Vite tooling. The workspace has no Git metadata, so a Git diff/status is unavailable. Existing Laravel configuration and business logic are left intact.

## Confirmed API contracts

All paths below are relative to `/api`. `php artisan route:list --path=api --except-vendor --json` successfully registers them. A registered resource action may still be empty.

| Capability | Routes / authentication | Confirmed implementation and limitations |
| --- | --- | --- |
| Products | GET `api-products`, GET `api-products/{id}`; optional Sanctum | List: `{status,data: ProductResource[]}`; accepts `keyword` and exact `category_name`; latest first, active only, unpaginated. Detail: bare array of ProductWithCommentResource, not an object/envelope. Detail ID is encrypted. Missing detail returns an empty array; invalid ciphertext can cause 500. Detail lacks active-only scope. |
| Categories | GET `api-categories`; public | `{status,data:[{id,name,image,image_url}]}`; encrypted ID, active only. Other resource methods are empty. |
| Subcategories | GET `api-sub-categories`, GET `api-sub-categories/{encrypted-category-id}`; public | `{status,data:[{id,categoy_id,sub_cat_name,image,image_url}]}`. The misspelled `categoy_id` is the actual contract; subcategory ID is numeric. Other resource writes are stubs. |
| Alternate filters | GET `products_by_category/{encrypted-id}`, `products_by_SubCategory/{numeric-id}`, `products_by_Brand/{numeric-id}` | ProductResource envelopes, but no active-only filter. Prefer the active product list for the storefront. |
| Featured | GET `featured-prods`; optional Sanctum | ProductResource envelope. No explicit active scope; frontend matches against the active catalog by unambiguous category/name and retains the active catalog ciphertext. See identity note below. |
| Discovery | GET `random_products`; optional Sanctum | Up to ten ProductResources; no active scope. Not needed for foundation. |
| Best sellers | GET `top-selling`; public | `{status,data:[{product_id,total_sold,product:raw Product}]}`. Raw numeric ID differs from ProductResource. Product model also appends `encrypted_product_id`; normalize that value, never pass numeric IDs to product operations. No active scope. |
| Brands | GET `brands`; public | `{status,data:[{brand_id,brand_name,image,status}],image_path}`. Image path is hardcoded to another host. Foundation builds URLs from the configured Laravel origin. |
| Banners | GET `get-banners`; public | Bare array; `banner_image` is a filename under `storage/BannerImage`; active status is checked, date range is not. Frontend also checks dates, sorts by sort_order, and restricts links to known destinations. Numeric polymorphic reference IDs are not suitable for encrypted detail links. |
| Offers | Part of ProductResource and Product model | `offer_price` is computed server-side; `primary_price` selects mrp/retail/wholesale/special_price. Do not infer a discount when data is absent. |
| Coupons | GET `get-coupons`; public. POST `coupon/validate`; Sanctum | Read response uses `{status,coupons:[]}`, not data. Validation needs coupon_code and client total_amount, returns validity only; does not provide a priced cart/quote. |
| Registration/login | POST `create-customer`, POST `login`; public | Register: first_name, phone, password required; no token returned. Login: mobile (not phone), password; returns token and user. Login deletes all existing customer tokens. No reset-password/OTP endpoints. Login does not reject inactive customers. |
| Profile/logout | GET `show-customer`, POST `update-customer`, POST `logout`, DELETE `customers/delete-account`; Sanctum | Profile read includes deliveryAddress. Review status mass assignment, password-change validation and account-deletion token cleanup before release. |
| Cart | GET/POST `cart`, POST `cart/remove-item`, POST `cart/update-count`; Sanctum | Read returns raw carts with raw product and primary_image relation; mutation product_id must be encrypted. Add requires integer quantity. Update uses `count` and lacks integer/min validation; add does not enforce stock. No guest cart or authoritative quote endpoint. |
| Wishlist | GET/POST `wishlist`; Sanctum | GET ProductResource envelope; POST encrypted product_id toggles membership, returns message Added/Removed with boolean status. show is missing, update/destroy are empty despite registered routes. |
| Addresses | Resource `delivery-address`; Sanctum | Create/update/delete exist. Index/show empty. Use show-customer for read if approved. Update is scoped only by address_id, not customer; ownership fix required before exposing edit. Nullable validated keys are accessed directly. |
| Orders | POST `cart/checkout`, GET `purchase/history`; Sanctum | History: resource envelope plus hardcoded product_image_path. Checkout validates delivery_state, computes subtotal from product/offer, creates order/items, decrements stock and clears cart before payment. No quote endpoint, address binding/snapshot, delivery serviceability quote or dedicated tracking/detail endpoint. |
| Payments | POST `razorpay/payment`, `razorpay/success`, `razorpay/failed`; Sanctum | Initiation checks ownership and derives amount from order. Success verifies signature. Callback handlers need ownership, capture/amount verification, idempotency and state-transition review; failure can overwrite a paid record. No webhook route. Do not activate purchases until reviewed. |
| Comments | GET/POST `order-items/{orderItemId}/comments`; Sanctum | Product detail includes comments; write checks purchase ownership for top-level comment, but replies bypass that check. Read returns full comment/customer models; privacy review required. |
| Returns | POST `sales-return/save`; Sanctum | Accepts refund/replacement. Client supplies prices/quantities; no verified order ownership, returnable quantity/window enforcement or transaction in controller. Do not expose until corrected. |
| Contact / wholesale | POST `customer-enquiry`, POST `wholesale-enquiry`; public | Contact requires name/email/phone/message and dispatches mail event. Wholesale requires name/phone/message, optional company_name/email; persists Pending enquiry. Delivery depends on mail/event configuration. |
| Policies | GET `return-policies`; public | Active records only; `{status,data:[{policy_id,title,return_days,return_type,conditions,...}]}`. Connected to the read-only `/returns` page with strict validation, internal-field removal, plain-text rendering and distinct empty/error states. No API for approved about/privacy/terms/shipping/FAQ copy or newsletter subscriptions. |
| Analytics | POST `track-visit` optional Sanctum; POST `checkout-click` Sanctum | Write endpoints; no analytics calls during audit or foundation browsing. |

### Identity, privacy and SEO

Laravel `encrypt()` is randomized: the same product/category receives a different ciphertext in every response. Ciphertext must remain an opaque string, including `/`, `+`, and `=`. It is not a stable database key. Foundation carries the original ciphertext in a URL-encoded `id` query, alongside a readable name slug. It does not join independently encrypted catalog and featured IDs. Featured/best-selling responses are checked against the active catalog by unique product name (database validates uniqueness per category), with category when available; ambiguous matches are omitted. Stable canonical slugs/UUIDs and a resolve-by-slug endpoint are required before product URLs/sitemap entries can be production-stable. Product detail can only be shown when it matches an active catalog entry. Never persist a numeric ID as a substitute for ciphertext.

Public detail serializes raw comment customer models, which can include email, phone and addresses. Phase 1 intentionally does not render raw comments. A minimal public review resource is needed. OptionalSanctumAuth manually resolves tokens without the usual expiry/ability checks; UpdateLastSeen mutates authenticated GETs. These deserve backend review; public foundation requests do not attach tokens.

## Recommended architecture

Independent `storefront/`: Next.js App Router, React, strict TypeScript, Tailwind 4 and Lucide. Server Components render public pages and call a server-only, fixed-origin Laravel client (no CORS change needed). Runtime Zod schemas validate heterogeneous API responses. Server functions return explicit unavailable vs empty states, never mock products. Reusable product/category/media/layout components share tokens and accessible native controls. A small search client uses an allowlisted same-origin route for debounced suggestions. React Query/Zustand/form libraries/Radix can be introduced when authenticated mutations and complex dialogs justify them; this foundation avoids shipping unused libraries.

Public API cache: short revalidation for homepage/catalog reads, uncached detail to preserve price freshness. No authenticated response is cached. Errors do not masquerade as empty catalog success. Next Image optimizes only the configured Laravel storage host; relative media paths are normalized to it. No backend secrets go into NEXT_PUBLIC variables. No arbitrary API proxy is provided.

## Phased plan and acceptance gates

1. **Foundation (this delivery):** isolated app/config, design system, responsive header/search/category navigation/footer/mobile navigation; actual categories, banners, featured and bestseller sections; public catalog/search/category pages with clearly documented local filtering/pagination; read-only detail/gallery; metadata, robots, conservative sitemap; loading/error/empty states; audit, setup and contract tests. Customer shopping shortcuts clearly explain phase availability. No fake badges, ratings, customer counts, shipping promises or newsletters.
2. **Customer shopping:** implement secure same-origin session boundary (HttpOnly cookie, CSRF/origin validation), mobile/password registration/login/logout, profile, wishlist and cart using inspected payloads; verify with a dedicated nonproduction database. Fix address ownership and cart validation first with separately approved backend changes. Do not store bearer tokens in localStorage.
3. **Checkout/payments:** obtain approval for identified backend business-logic changes; add authoritative quote, server-owned address/delivery/tax/coupon calculations, stock concurrency, pending-payment lifecycle, idempotency, verified capture/webhooks and owned order status. Only then connect Razorpay and returns. Test success, rejection, cancellation, retry and duplicate webhook paths using test keys/data.
4. **Release:** approved policy/business copy, contact/wholesale integration tests, accessibility and keyboard audit, real mobile/browser tests, Core Web Vitals and SEO/stable URLs, cache invalidation, deployment/observability and security review. No production data writes or destructive migrations.

## Deliberate gaps in phase 1

- Backend has no combined brand/price/stock/sort/pagination API. Foundation filters/sorts/paginates the full active response on the server and identifies this in catalog help text; not a scalable substitute for backend pagination. `keyword` and `category_name` are the only forwarded filters. UI must not claim server pagination.
- No stable product locator: product canonical/indexing is withheld; sitemap contains static routes and category-name routes only after successful category reads. An unavailable category API makes sitemap fail rather than publishing an empty success.
- Account/cart/wishlist/checkout are explicitly future phases, with informative landing states, not fabricated sessions, counts or transaction success.
- Legal copy is marked as awaiting business approval; no invented legal, shipping or returns promises. Newsletter omitted because there is no subscription endpoint.
- No production-readiness claim for backend workflows listed above. No existing backend business logic is modified.

## References

Architecture checked against [Next.js installation](https://nextjs.org/docs/app/getting-started/installation) and [route segment/caching documentation](https://nextjs.org/docs/app/api-reference/file-conventions/route-segment-config). Installed versions and exact commands/results are recorded in storefront/README.md after verification.

## Delivery result

Phase 1 foundation implemented in `storefront/` only, plus this audit. Existing Laravel controllers, resources, models, migrations, routes, admin templates, environment, and root npm/composer configuration were not edited. Next.js 16.4.6 production build and strict TypeScript passed; 25 contract/transport/rendering tests and 16 read-only production HTTP smoke checks passed. Homepage special offers use validated backend amounts; `/returns` reads published policies from the real API. Live local collections were empty. API outage behavior was verified. Browser inventory was empty, so visual/device verification remains pending. See `storefront/README.md` for exact setup, files, asset provenance, verification and release limits.
