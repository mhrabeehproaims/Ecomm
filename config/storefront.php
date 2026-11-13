<?php

return [
    // This implementation has no production activation switch. Local/test only.
    'test_checkout' => (bool) env('STOREFRONT_TEST_CHECKOUT', false),
    'cod_enabled' => (bool) env('STOREFRONT_TEST_COD', false),
    'quote_minutes' => 10,
    'reservation_minutes' => 15,
    'origin_state' => 'kerala (KL)',
    // Explicit state => tax-inclusive shipping charge in paise; no default free shipping.
    'shipping_minor' => json_decode(env('STOREFRONT_TEST_SHIPPING_MINOR', '{}'), true) ?: [],
    'shipping_tax_basis_points' => env('STOREFRONT_TEST_SHIPPING_TAX_BPS'),
    'postal_codes' => array_values(array_filter(explode(',', env('STOREFRONT_TEST_POSTAL_CODES', '')))),
    'razorpay_key' => env('STOREFRONT_RAZORPAY_TEST_KEY', ''),
    'razorpay_secret' => env('STOREFRONT_RAZORPAY_TEST_SECRET', ''),
    'webhook_secret' => env('STOREFRONT_RAZORPAY_WEBHOOK_SECRET', ''),
];
