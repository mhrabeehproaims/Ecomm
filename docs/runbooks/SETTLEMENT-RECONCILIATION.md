# Settlement reconciliation readiness

**No bank settlement has been verified.** Current application records distinguish server-priced orders, provider attempts/captures, refunds, COD collection and COD remittance. FinancialLedger always returns `bank_settlement_verified=false`. That remains unchanged.

## What exists and what is missing

| Evidence | Present implementation | Bank-verification value |
| --- | --- | --- |
| Orders/checkouts/payment attempts | Immutable accepted totals and payment/refund identifiers; owned and idempotent verification | Establishes what the customer owes and verified provider capture, not bank credit |
| Provider refund records and financial entries | Pending/processed/failed refund observations and append-only capture/refund entries | Establishes observed refund balance; provider/merchant reconciliation still needs acceptance |
| COD collection/remittance entries | Operator, unique receipt/evidence and balance controls | Records asserted cash/deposit evidence; neither receipt text nor status proves a bank entry |
| Merchant settlements | No settlement table, matching service or subscribed settlement handler | Missing |
| Razorpay SDK capability | Installed SDK exposes settlement reads and combined reconciliation reports; the application PaymentGateway interface does not implement them | Possible provider-side source after account access/format acceptance; no API was called |
| Bank feed or statement import | No bank connector, authorized account, approved CSV schema or import workflow | Missing |

Razorpay documents [settlement API/report capabilities](https://raw.githubusercontent.com/razorpay/markdown-docs/master/api/settlements.md) and [Dashboard reconciliation reports/UTR](https://github.com/razorpay/markdown-docs/blob/master/payments/settlements/faqs.md). These can supply settlement identifiers, provider allocations and transfer references. Independent merchant bank evidence is still needed to verify credit to the approved account. [Test Mode moves no real money](https://raw.githubusercontent.com/razorpay/markdown-docs/master/payments/dashboard/test-live-modes.md); synthetic settlement fixtures cannot prove real bank receipt.

## Safe local improvement

Added `tests/Storefront/financial-evidence.php`, a read-only sample-data report. It refuses normal application databases, uses SQLite query_only or a read-only MySQL transaction, and never calls a payment provider. It compares accepted totals, attempts, capture/refund ledger totals and COD receipts/remittances; missing/mismatched evidence is reported rather than repaired. Only orders linked to storefront_checkouts are analyzed; the excluded legacy count is reported for separate review. It is not an audited accounting export or a replacement for all legacy reconciliation.

```powershell
# Repository root; use an existing sample run, never a production database.
$env:STOREFRONT_TEST_RUN='remediation-20261116'
php tests/Storefront/financial-evidence.php
```

For an existing task-owned MySQL schema, set the exact `STOREFRONT_MYSQL_TEST_DATABASE` name as well. Its helper verifies port 3339 and the known separate datadir. The report does not initialize a database. Clear this variable before a SQLite report. No customer identity, credential, raw provider payload or bank account number is exported.

The local SQLite run reviewed 9 secured sample orders, excluded 4 legacy fixture orders, found 0 internal evidence mismatches and preserved the database-file hash. The read-only MySQL run reviewed 8 sample orders and flagged1 expected fixture gap: a COD order manually marked paid for the replacement-race setup without collection evidence. The helper did not invent a receipt or modify that record. Both runs made 0 provider requests and updated 0 records. These conclusions are restricted to synthetic samples; they do not establish production financial accuracy or settlement.

## Controlled matching design awaiting approval

Choose the provider report/API format, approved merchant account and independent bank source first. For an initial controlled statement import, obtain an owner-approved redacted sample/schema; do not guess column names, amount signs, timezone, UTR format or whether a row represents a deposit versus a fee. No import or new accounting schema was implemented without these inputs.

The reviewed workflow should preserve original evidence immutably with a content hash, account alias, currency, statement period and source provenance. Parse into a preview/quarantine layer; reject ambiguous dates, noninteger paise, wrong accounts/currencies, duplicate source rows and malformed/truncated files. Reimporting identical evidence must be idempotent. Never execute spreadsheet formulas or accept arbitrary file paths from an upload.

Match three distinct layers: accepted orders and verified capture/refund evidence; provider settlement allocations including documented fees, taxes, holds, disputes, adjustments and period timing; actual bank credits using approved account/UTR/value-date evidence. Do not equate gross captures minus refunds with the bank deposit or invent a fee/rounding tolerance. Partial, split, delayed and reversed deposits require explicit handling. Preserve unmatched items and conflicting evidence for review.

Only an authorized finance reviewer should confirm a match after the approved equality/tolerance and provenance checks; corrections should append evidence, not rewrite completed orders or original statements. Define separation of duties, least-privilege ingestion access, encrypted storage, retention, reversal behavior, alerting, aging and signed reconciliation reports before building the write path. An on-demand settlement/payout API must not be used as a read-only reconciliation operation.

Required acceptance includes exact/duplicate/partial/delayed/fee-adjusted/reversed deposits, unmatched provider or bank rows, wrong currency/account, provider downtime, failed/refunded payments, out-of-order evidence and COD partial collection/remittance. These are design/fixture cases until actual approved evidence sources are supplied. There is no automatic settlement confirmation switch in this preparation.
