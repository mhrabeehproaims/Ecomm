import assert from 'node:assert/strict';
import { test } from 'node:test';
import { createElement } from 'react';
import { renderToStaticMarkup } from 'react-dom/server';
import { productSchema, type Product } from '../src/lib/contracts';
import { ProductSupport } from '../src/components/product-support';

const product = productSchema.parse({ product_id: 'opaque-id', product_name: 'Pants', category: 'Clothing', brand: 'Linen', stock: 0 });
const render = (item: Product, catalog: Product[] = []) => renderToStaticMarkup(createElement(ProductSupport, { product: item, catalog, imageOrigin: 'http://localhost:8000' }));

test('missing size uses verified facts and help without invented size information', () => {
  const html = render({ ...product, stock: 3 });
  assert.ok(html.includes('Clothing') && html.includes('Linen'));
  assert.ok(html.includes('Ask us about sizing or product details'));
  assert.ok(html.includes('href="/contact"'));
  assert.ok(!html.includes('Available in this collection'));
  assert.ok(!html.includes('One size'));
});

test('out-of-stock alternatives preserve real links/prices and exclude unavailable or unrelated products', () => {
  const catalog = [product,
    { ...product, product_id: 'encrypted/shirt+id', product_name: 'Available shirt', stock: 2, primary_price: 'retail', retail: 1100 },
    { ...product, product_name: 'Unavailable shirt', stock: 0 },
    { ...product, product_name: 'Unknown stock', stock: null },
    { ...product, product_name: 'Other collection', category: 'Other', stock: 3 }];
  const html = render(product, catalog);
  assert.ok(html.includes('Available shirt'));
  assert.ok(html.includes('id=encrypted%2Fshirt%2Bid'));
  assert.ok(html.includes('1,100.00'));
  for (const name of ['Unavailable shirt', 'Unknown stock', 'Other collection']) assert.ok(!html.includes(name));
  assert.ok(html.includes('stock=in'));
});

test('no recommendations are fabricated and fully specified products need no fallback panel', () => {
  assert.ok(!render(product).includes('available-alternative"'));
  assert.ok(render(product).includes('Browse available pieces'));
  assert.equal(render({ ...product, size: 'Medium', stock: 5 }), '');
  assert.equal(render({ ...product, size: 'Medium', stock: null }), '');
  assert.ok(!render({ ...product, category: null }, [{ ...product, category: null, product_name: 'Uncategorized item', stock: 3 }]).includes('Uncategorized item'));
});
