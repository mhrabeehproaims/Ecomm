# Laravel customer API changes awaiting approval

> Approved for local implementation on 13 November 2026. The text below preserves the original proposal and pre-approval status. The patch was reviewed and applied after a source backup; the final expanded implementation is documented in [the checkout report](../STOREFRONT-CHECKOUT-IMPLEMENTATION.md). Production activation is still not approved. The original patch generator is historical and should not be rerun against the revised controllers.

Prepared 13 November 2026. **No application PHP, migrations, admin code or production data was changed.** The user explicitly requires approval before Laravel business-logic changes. This document and `customer-api-hardening.patch` make that work reviewable.

## Prepared hardening patch

`customer-api-hardening.patch` proposes changes to six existing files:

| File | Proposed behavior |
| --- | --- |
| `app/Http/Controllers/Api/AuthController.php` | Reject inactive customers before issuing tokens. |
| `app/Http/Controllers/Api/DeliveryAddressController.php` | Scope address updates to the authenticated customer; safely default omitted nullable fields. |
| `app/Http/Controllers/Api/CartController.php` | Validate integer positive quantities and active product/stock limits. |
| `app/Http/Controllers/Api/PaymentController.php` | Bind success/failure to the customer's order; fetch provider payment and check capture, order, currency and amount; prevent failure callbacks downgrading paid orders. |
| `app/Http/Controllers/Api/SalesReturnController.php` | Require owned delivered order/items; derive purchase prices from the order; restrict cumulative non-rejected request quantities. |
| `app/Http/Resources/OrderHistoryResource.php` | Use the already-loaded return relationship instead of an unnecessary table-wide existence query. |

Proposed PHP passed syntax checks; `git apply --check docs/approvals/customer-api-hardening.patch` passes. Source hashes confirm the six actual files remain unchanged. The patch has **not** been applied or behaviorally tested. Generate it with `node storefront/scripts/prepare-backend-review.mjs`; inspect before applying. This patch alone does not make checkout production-ready.

## Checkout contract to implement after approval

1. Add an authenticated `POST /api/checkout/quote` accepting only an owned `address_id`, optional `coupon_code` and payment method. Re-read active products, current prices, quantities, tax, coupon eligibility/usage and delivery serviceability on the server. Return an opaque short-lived quote ID, item breakdown, subtotal, discount, tax, shipping and payable INR amount. Reject unavailable stock and unsupported addresses. Do not accept browser totals.
2. Complete order creation using an opaque quote ID and an idempotency key. Persist an immutable owned delivery-address snapshot and calculation breakdown. Reject expired/changed quotes with a re-quote response. Atomically lock stock/coupon usage, create items and reserve inventory; define reservation expiry/restoration before enabling online orders. A retry must return the same order instead of creating another. Use additive migrations only, reviewed against a staging copy before deployment.
3. Complete payment initiation for an owned payable order with a unique provider-order mapping, immutable amount/currency and retry-safe pending state. Test-only keys stay isolated from live configuration. Never replace a successful state with pending/failed.
4. Verify signed callbacks **and signed provider webhooks**, fetch captured payment/order, verify amount/currency/ownership, and apply one atomic idempotent transition. Handle duplicate/out-of-order notifications, cancellation, capture pending, expiry and retries. Clear cart and commit/release reservations according to the approved lifecycle. Never trust frontend success alone.
5. Expand owned order history/detail with placed/delivered timestamps, address snapshot and authoritative totals. Add carrier tracking data/events only when there is a real carrier source; no simulated tracking.
6. Return a server-derived return-eligibility decision, remaining quantity and deadline based on recorded delivery time and policy. Atomically enforce the same decision on submission, derive refundable paid amounts from order/discount/tax allocation, and expose owned return status. Refund execution remains a separate approved workflow; no automatic payout is proposed by this frontend.
7. Enforce authorization and durable rate limits directly in Laravel for all customer routes, including profile field allowlists and inactive sessions. Frontend guards do not protect direct API callers. Add negative ownership, inventory race, coupon race, duplicate order/payment/webhook, unpaid-return and eligibility tests in the isolated database.

The frontend will consume these contracts after implementation; production checkout remains disabled until the contracts and Razorpay test acceptance scenarios pass. Business decisions still needed: shipping/serviceability rules, COD availability, reservation expiry, coupon discount/tax allocation, return windows/exclusions and approved policy copy. No production migration, payment, refund, stock adjustment or admin behavior change is authorized by this proposal.
