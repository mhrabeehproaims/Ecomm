// Read-only HTTP checks against an already-running storefront, never database writes.
import assert from 'node:assert/strict';
const origin = process.env.SMOKE_ORIGIN || 'http://127.0.0.1:3000';
const checks = [
  ['/', 'A little more care.'],
  ['/products', 'The everyday collection.'],
  ['/products?collection=offers', 'A little more value.'],
  ['/categories', 'Find your kind of everyday.'],
  ['/account', 'Customer accounts are coming'],
  ['/wishlist', 'Saved discoveries are coming'],
  ['/cart', 'Online shopping is coming'],
  ['/shipping', 'approved delivery policy'],
  ['/returns', 'Current return information from Vantara.'],
  ['/privacy', 'Awaiting approved business copy.'],
  ['/terms', 'Awaiting approved business copy.'],
  ['/sitemap.xml', '<urlset'],
  ['/robots.txt', 'User-Agent: *'],
];
for (const [path, expected] of checks) {
  const response = await fetch(`${origin}${path}`);
  assert.equal(response.status, 200, path);
  const html = await response.text();
  assert.ok(html.includes(expected), `${path}: expected rendered content`);
  if (path === '/') {
    assert.ok(html.includes('rel="canonical"'), 'home canonical');
    assert.ok(html.includes('noindex'), 'development indexing disabled');
    assert.equal(response.headers.get('x-content-type-options'), 'nosniff');
  }
  console.log(`PASS ${path}`);
}
const search = await fetch(`${origin}/api/search?q=smoke-test-unique-keyword`);
assert.equal(search.status, 200);
assert.ok(Array.isArray((await search.json()).items));
console.log('PASS same-origin search contract');
const hero = await fetch(`${origin}/hero.webp`);
assert.equal(hero.status, 200);
assert.equal(hero.headers.get('content-type'), 'image/webp');
console.log('PASS local hero asset');
const unsafe = await fetch(`${origin}/products?keyword=${encodeURIComponent('<script>smoke-test</script>')}`);
const unsafeHtml = await unsafe.text();
assert.ok(!unsafeHtml.includes('<script>smoke-test</script>'), 'search term must be HTML escaped');
console.log('PASS escaped search input');
console.log('16 read-only HTTP checks passed.');
