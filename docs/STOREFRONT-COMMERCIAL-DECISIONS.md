# Vanthara commercial approval checklist

No row below is approved by this document. Record the owner's decision, effective date and acceptance evidence before enabling its corresponding production behavior. Checkout and COD remain disabled; local fixture values are not business policy.

| Decision requiring approval | Current code to confirm or replace | Decision / owner |
| --- | --- | --- |
| Delivery areas and fees | India, valid six-digit postcode, explicit state-to-paise rates, optional postcode allowlist; unconfigured areas fail closed. No carrier quoting, weight pricing or free-shipping threshold. | Pending |
| Delivery tax and refund | Inclusive delivery tax requires an explicit configured rate when delivery is charged. Return line refunds currently exclude delivery. | Accountant/owner pending |
| Product tax and invoicing | Catalog prices treated as inclusive; configured GST components must match total rate. Origin defaults Kerala. Seller registration, HSN, billing/recipient details, invoice series and credit-note/amendment requirements need approval. | Accountant/owner pending |
| Base price and offer priority | Product primary-price selector can choose retail/wholesale/special/MRP; active offer is capped at base. Overlapping offer precedence needs an explicit decision. | Pending |
| Coupon terms | One coupon after offers; product subtotal only for minimum/discount, proportional paise allocation, capped at subtotal. Confirm stacking, exclusions, case handling, caps and date boundaries. | Pending |
| Coupon limits after cancellation/refund | Reserved uses consume limits; releasing unpaid reservations frees them. Committed uses are not automatically restored on refund. | Pending |
| Reservation and quote expiry | Ten-minute quote, fifteen-minute reservation; unpaid release restores stock once. Define customer communications and expired-payment response. | Pending |
| Payment capture and retry | Online test orders need at least INR 1. Authorized payments are not treated as captured. Provider capture configuration/operator ownership requires acceptance. | Pending |
| COD | Default off. Local workflow records actual delivered-order collection/remittance evidence; partial collection remains unpaid. Decide serviceability, fees, collection timing, failed delivery, missing cash, receipts and bank proof. | Pending |
| Cancellation | Customer can release an unpaid reserved order. Committed/paid rejection is blocked; no complete paid/COD cancellation/refund/restock workflow is approved. | Pending |
| Return eligibility | Delivered, paid/partially-refunded order, remaining purchased quantity, first delivery date and snapshotted policy windows. Effective window is constrained by active policy and category/subcategory limits. Confirm conditions/exclusions and approval process. | Pending |
| Refund amount and failed payouts | Proportional purchased line total after discount; cumulative rounding prevents excess refunds. Provider refunds require at least INR 1. Confirm tax/delivery refundability, deductions, partial returns, failed/sub-rupee payout handling and COD manual payouts. | Accountant/owner pending |
| Inventory after return | No automatic restock on refund; accepted replacement deducts once. Define inspection, sellable/damaged stock and accounting responsibility. | Pending |
| Late capture and existing refunds | Released-order late capture requires operator review; already-refunded reserved payment pauses fulfillment. Approve refund, reservation/stock disposition and escalation deadlines. | Pending |
| Settlement and disputes | No approved bank feed, statement schema, merchant fees/reserves/adjustments mapping or reconciliation tolerance. Capture and COD receipt are not bank credit. | Finance owner pending |
| Customer promises and release scope | Approve privacy/terms/shipping/returns/contact details, delivery promises, support and account-recovery/identity-verification requirements. No carrier integration exists. | Pending |

Configuration changes must be versioned with an effective date and tested on sample orders. Do not recalculate completed financial records when changing rates, policies or invoice presentation. See [settlement readiness](runbooks/SETTLEMENT-RECONCILIATION.md) and [Razorpay acceptance](runbooks/RAZORPAY-TEST-ACCEPTANCE.md).
