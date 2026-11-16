# Admin customer cart items

Open `/carts` or **Customer → Cart Items**, beside **Wishlisted Items**, with the existing admin web login.

The existing cart route was present but absent from the menu. Its legacy filters used inconsistent data shapes and a missing product-filter view. The route now uses one paginated, filterable Blade listing of the actual `carts` records, joined to customers, products, sizes and primary images. Customer names, contact details, quantity, saved unit price, availability, added date and update date are displayed. Empty and missing-relation states are handled. Inactive products remain visible for admin inspection.

Customer/product filters use GET parameters, support either/both/neither selection, retain state across pages and offer Clear filters. The old `POST /get_carts_byCustomer` route remains available and redirects to the equivalent listing. `GET /carts/{id}` checkout-event inspection and checkout-event recording are unchanged. Cart membership queries avoid cached customer/product lists so new customer cart entries can appear on refresh.

This page reads existing cart data; it does not modify carts, stock, prices, orders or payments. Saved cart prices are explicitly distinguished from a fresh checkout quote. No database migration or new customer-facing API was added. The existing admin authentication middleware applies, and the response uses `private, no-store`. An anonymous visitor or a customer API token does not gain admin-page access.

Files changed: `app/Http/Controllers/CheckoutEventController.php`, `resources/views/Cart/carts.blade.php`, `resources/views/layouts/app.blade.php`. A small guard in `public/assets/js/plugin.js` prevents the existing theme initializer from accessing removed stylesheet IDs; static stylesheet URLs and admin routes are preserved. Test coverage lives in `tests/Feature/AdminCartItemsTest.php`, `tests/Storefront/admin-cart-fixture.php`, `tests/Storefront/admin-cart-router.php` and `storefront/scripts/admin-cart-browser.mjs`.

Verification: all **74 Laravel tests / 514 assertions passed**, including five new cart tests for access control, quantities/saved prices, customer/product/empty filters, pagination/legacy rows and actual storefront cart API changes appearing in admin. Chromium also verified a real isolated admin login, cart rows, filters/reset, empty state and 393px layout, with no script exceptions. Screenshots and browser results: `storefront/artifacts/admin-carts/`. External requests were blocked in browser verification. Sample cart/admin records were created only in the separate `admin-cart-browser-20261116a` SQLite test database; no normal customer/admin records were changed.

A pre-edit backup is retained at `storage/framework/testing/backups/admin-cart-20261116-171423/`. Production checkout settings, credentials and Laravel APP_KEY were preserved.
