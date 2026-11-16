import assert from 'node:assert/strict';
import { test } from 'node:test';
import { activeBanners, activeRecommendations, filterProducts, hasOffer, numberOrNull, plainText, productHref, productPrice, safeBannerLink } from '../src/lib/catalog';
import { envelope, productSchema } from '../src/lib/contracts';
import { mediaUrl } from '../src/lib/media';

// Synthetic fixtures stay in tests; storefront rendering never imports them.
const product = productSchema.parse({ product_id: 'opaque/encrypted+value==', product_name: 'Contract fixture', category: 'Test category', primary_price: 'retail', mrp: '150.00', retail: '100.00', stock: '2' });

test('encrypted IDs round-trip through product URLs without numeric coercion', () => {
  const href = productHref(product);
  assert.equal(new URL(href, 'https://example.test').searchParams.get('id'), product.product_id);
  assert.ok(href.startsWith('/products/contract-fixture?'));
  assert.match(href, /%2F/);
});
test('only encrypted string IDs are accepted by the ProductResource contract', () => {
  assert.equal(productSchema.safeParse({ ...product, product_id: 17 }).success, false);
});
test('backend selected price and offer price win, including a genuine zero price', () => {
  assert.equal(productPrice(product), 100);
  assert.equal(productPrice({ ...product, offer_price: '75.50' }), 75.5);
  assert.equal(productPrice({ ...product, offer_price: 0 }), 0);
  assert.equal(productPrice({ ...product, primary_price: 'wholesale', wholesale: null }), null);
  assert.equal(productPrice({ ...product, primary_price: 'mrp' }), 150);
});
test('invalid or missing prices do not become free products', () => {
  for (const value of [null, undefined, '', '  ', true, false, -1, 'NaN', Infinity]) assert.equal(numberOrNull(value), null);
});
test('recommendations preserve active encrypted IDs and omit ambiguous names', () => {
  const same = { ...product, product_id: 'different-randomized-ciphertext' };
  assert.equal(activeRecommendations([same], [product])[0].product_id, product.product_id);
  assert.deepEqual(activeRecommendations([same], [product, { ...product, category: 'Other' }]), [product]);
  assert.deepEqual(activeRecommendations([{ product_name: product.product_name }], [product, { ...product, category: 'Other' }]), []);
  assert.deepEqual(activeRecommendations([{ product_name: 'Unpublished' }], [product]), []);
});
test('recommendations deduplicate repeated featured records', () => {
  assert.equal(activeRecommendations([product, product], [product]).length, 1);
});
test('stock/brand/price filters and sorting apply to actual values', () => {
  const lower = { ...product, product_name: 'Lower', brand: 'Fixture brand', retail: 50, stock: 0 };
  const higher = { ...product, product_name: 'Higher', brand: 'Fixture brand', retail: 200 };
  assert.deepEqual(filterProducts([higher, lower], { max: '100' }), [lower]);
  assert.deepEqual(filterProducts([higher, lower], { stock: 'in' }), [higher]);
  assert.deepEqual(filterProducts([higher, lower], { sort: 'price-asc' }), [lower, higher]);
  assert.deepEqual(filterProducts([higher, lower], { brand: 'Unknown' }), []);
  assert.deepEqual(filterProducts([product, { ...higher, offer_price: 0 }], { collection: 'offers' }), [{ ...higher, offer_price: 0 }]);
});
test('unknown prices sort after known prices in either direction', () => {
  const unknown = { ...product, retail: null };
  assert.equal(filterProducts([unknown, product], { sort: 'price-asc' })[0], product);
  assert.equal(filterProducts([unknown, product], { sort: 'price-desc' })[0], product);
});

test('offer collections require a valid backend amount and preserve zero offers', () => {
  const freeOffer = { ...product, offer_price: '0' };
  assert.equal(hasOffer(freeOffer), true);
  const invalid = [null, undefined, '', ' ', 'invalid', -1].map(offer_price => ({ ...product, offer_price }));
  assert.ok(invalid.every(p => !hasOffer(p)));
  assert.deepEqual(filterProducts([...invalid, freeOffer], { collection: 'offers' }), [freeOffer]);
});
test('empty success differs from malformed/error responses', () => {
  assert.equal(envelope(productSchema).safeParse({ status: 'success', data: [] }).success, true);
  assert.equal(envelope(productSchema).safeParse({ status: 'error', data: [] }).success, false);
  assert.equal(envelope(productSchema).safeParse({ status: 'success', data: {} }).success, false);
});
test('raw comments and customer details are removed by the public schema', () => {
  const parsed = productSchema.parse({ ...product, comments: [{ customer: { phone: 'private', email: 'private' } }] });
  assert.equal('comments' in parsed, false);
});
test('media uses configured backend origin and expected storage folders', () => {
  const origin = 'http://127.0.0.1:8137';
  assert.equal(mediaUrl(origin, 'photo.jpg', 'https://legacy.test/storage/categoryImage'), `${origin}/storage/categoryImage/photo.jpg`);
  assert.equal(mediaUrl(origin, 'banner.webp', null, 'BannerImage'), `${origin}/storage/BannerImage/banner.webp`);
  assert.equal(mediaUrl(origin, '/storage/products/a.jpg'), `${origin}/storage/products/a.jpg`);
  assert.equal(mediaUrl(origin, 'https://untrusted.test/storage/a.jpg'), `${origin}/storage/a.jpg`);
  assert.equal(mediaUrl(origin, '../../private.txt'), null);
  assert.equal(mediaUrl(origin, 'https://untrusted.test/private.txt'), null);
  assert.equal(mediaUrl(origin, null), null);
});
test('promotional links cannot redirect to offsite or executable URLs', () => {
  for (const url of ['javascript:alert(1)', '//example.test/products', 'https://example.test/products', '/account', '/products\\evil']) assert.equal(safeBannerLink(url), '/products');
  assert.equal(safeBannerLink('/products?category=Test'), '/products?category=Test');
});
test('banners observe Indian business date boundaries and sort order', () => {
  const banners = [{ from_date: '2026-11-13', sort_order: 2 }, { to_date: '2026-11-12', sort_order: 1 }, { from_date: '2026-11-14' }, { sort_order: 0 }];
  assert.deepEqual(activeBanners(banners, new Date('2026-11-12T20:00:00Z')), [banners[3], banners[0]]);
});
test('HTML descriptions are rendered as plain text', () => {
  assert.equal(plainText('<p>One &amp; two</p><script>alert(1)</script>'), 'One & two alert(1)');
});

test('fashion size and price-range filters never invent sizes or treat missing prices as zero', () => {
  const medium = { ...product, size: 'Medium', retail: 100 };
  const small = { ...product, size: 'Small', retail: 50 };
  const unknown = { ...product, size: 'Medium', retail: null };
  assert.deepEqual(filterProducts([medium, small, unknown], { size: 'Medium', min: '80', max: '120' }), [medium]);
  assert.deepEqual(filterProducts([medium, small], { size: 'XL' }), []);
  assert.deepEqual(filterProducts([medium, small], { min: '120', max: '80' }), []);
});
