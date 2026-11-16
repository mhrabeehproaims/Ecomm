# Vanthara footer and public content

Verified against the rendered public website on 16 November 2026. The live site requires JavaScript; Chromium was used to inspect the actual footer, follow its destinations, open every About tab and expand every FAQ answer. No live forms were submitted.

## Destinations

| Live footer label | Source URL | App destination and behavior |
| --- | --- | --- |
| About Us | https://vanthara.com/about | `/about`: introduction, vision, distinguishing features, commitment, published benefits and customer reviews |
| Our Stories | https://vanthara.com/stories | `/stories`: explicit empty state; the source renders its not-found page |
| Contact Us | https://vanthara.com/contact | `/contact`: approved contact details, map-search link and enquiry form |
| FAQ | https://vanthara.com/faq | `/faq`: all ten questions and expanded answers |
| My Account | https://vanthara.com/account | `/account`: existing protected customer functionality preserved |
| Shipping | https://vanthara.com/shipping | `/shipping`: all seven published sections and estimates |
| Return & Refund | https://vanthara.com/policy | `/policy`: existing Laravel published-policy integration; live source currently shows no policies |
| Privacy Policy | https://vanthara.com/privacy-policy | `/privacy-policy`: all eight published sections |
| Terms & Conditions | https://vanthara.com/terms-conditions | `/terms-conditions`: all ten published sections |
| Wholesale Enquiry | https://vanthara.com/wholesale-enquiry | `/wholesale-enquiry`: existing working wholesale form, with the source business-type options |

Existing `/wholesale` and `/returns` remain functional. `/privacy`, `/terms` and `/terms-of-service` redirect to the corresponding canonical policy pages. Newsletter Terms of Service and Privacy Policy links now point to real local pages instead of `#`.

Public text is checked in as structured strings in `storefront/src/content/vanthara-public.ts`; no source HTML or JavaScript is executed or injected. About uses the existing local brand image. Social SVGs are local copies of the source footer icons with event/script attributes excluded. Reviews are the public source's text, not newly generated testimonials or verified-purchase claims.

## Approved contact details and corrected targets

The owner selected the footer contact details for consistent use throughout the app:

- Address: Old Vythiri, Vythiri, Kunnathidavaka, Kerala 673576.
- Phone: `9074709600`; clickable target `tel:+919074709600`.
- Email: `vantara@gmail.com`; clickable target `mailto:vantara@gmail.com`.
- Directions: Google Maps address search using the exact approved postal address. An exact verified business pin was not published by the source footer.

The source footer's email text incorrectly linked to `themesflat@gmail.com`; that mismatch was corrected. Contact Us and the FAQ support answer now use the owner's selected details instead of the source's inconsistent addresses. Published contact-page opening hours were retained.

## Source gaps and commercial inconsistencies

- **Stories:** source `/stories` renders “Oops! Page Not Found” despite HTTP 200. The app provides a clearly labelled empty state and About link; no articles were invented.
- **Social profiles:** all five source links target `#`. Icons are displayed as unavailable, noninteractive items with accessible labels. Real profile URLs are still required.
- **Newsletter:** the source controls have no submission handler, and the Laravel application has no subscription endpoint. The matching UI is explicitly disabled with an availability note. No consent or email is collected and no subscription is claimed. A service, opt-in storage, delivery/unsubscribe workflow and appropriate approval are required before activation.
- The live About benefits advertise 14-day returns, while FAQ text asks customers with damaged/incorrect items to contact support within three days and the live policy page lists no policies.
- The live Shipping page offers selected international delivery, while the FAQ says India only. Express delivery and general free-shipping/support claims also need business confirmation.

Published copy was reproduced as requested. These statements did not change Laravel shipping calculations, return eligibility, tax rules, carrier integrations, orders, payments or checkout availability. Business-owner reconciliation of the source's conflicting commercial statements remains necessary before production ordering.

The footer uses INR, matching the app and published Terms, rather than the source footer's USD template label. Existing checkout and payment activation safeguards remain unchanged.

## Contact integration and verification boundary

`POST /api/contact` is a same-origin Next route that validates field lengths/types, bounds request size and forwards only name, email, phone and message to the existing Laravel `POST /api/customer-enquiry` endpoint. Loading, validation, failure recovery, success and form reset are implemented. The existing Laravel listener and its internal delivery recipient were preserved.

Laravel contact tests use `Mail::fake()` and an isolated in-memory database. Browser UI tests intercept every write and simulate failure/success responses; no POST reaches a backend during those UI tests. Automatic approval review rejected a proposed browser submission because it could reach the normal mail-enabled server. The safer split verification was completed instead. **End-to-end SMTP or live email delivery was not tested.** No external email was sent.

## Verification

- Laravel: **69 tests, 448 assertions passed**, including contact mail payload and validation tests.
- Frontend: **40 tests passed**, including URL aliases, approved contact targets and contact validation.
- TypeScript: passed.
- Next production build: passed; final build uses the normal local Laravel configuration at `http://localhost:8000`.
- Chromium: clicked the actual footer links; verified all ten destinations, three legacy redirects, unknown/prototype route handling, protected account access, About keyboard tabs and all FAQ answers.
- Chromium: compared all **25 policy sections / 95 nonempty policy lines**, all four About tabs and ten FAQ answers with the captured source.
- Footer widths **360, 393, 430, 768, 1024, 1440 and 1920px** passed overflow checks. Nine public destinations were also checked at desktop/mobile widths.
- Browser navigation produced no page errors. No real enquiries, customer records, orders, payments or production data were changed by testing.

Artifacts: `storefront/artifacts/footer-qa/` contains screenshots, navigation results, copied-content comparison and intercepted contact-form results. `storefront/artifacts/footer-reference/` contains the read-only source snapshots. The footer-only screenshots hide the sticky header, mobile navigation and development overlays during capture so they show the whole footer clearly; product behavior is unchanged.

## Files

Modified:

- `storefront/src/components/layout.tsx`
- `storefront/src/app/layout.tsx`
- `storefront/src/app/[page]/page.tsx`
- `storefront/src/app/returns/page.tsx`
- `storefront/src/lib/wholesale-contracts.ts`

Created:

- `storefront/src/content/vanthara-public.ts`
- `storefront/src/lib/public-site.ts`
- `storefront/src/lib/contact-contracts.ts`
- `storefront/src/components/about-tabs.tsx`
- `storefront/src/components/contact-form.tsx`
- `storefront/src/components/public-information.tsx`
- `storefront/src/app/api/contact/route.ts`
- `storefront/src/app/public-information.css`
- Five SVG icons in `storefront/public/social/`
- `storefront/tests/public-pages.test.ts`
- `tests/Feature/StorefrontContactTest.php`
- This report

No migrations, dependencies or Laravel business-logic changes were required. Pre-change source backups are in `storage/framework/testing/backups/footer-content-20261116/`. Existing environment files, APP_KEY, Laravel admin workflows and unrelated uncommitted work were preserved.
