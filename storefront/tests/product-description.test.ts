import assert from 'node:assert/strict';
import { test } from 'node:test';
import { createElement } from 'react';
import { renderToStaticMarkup } from 'react-dom/server';
import { productSchema } from '../src/lib/contracts';
import { ProductDescription } from '../src/components/product-description';

const origin = 'http://localhost:8000';
const product = productSchema.parse({ product_id: 'opaque-encrypted-product-id', product_name: 'Test shirt', image_path: 'http://legacy.example/storage' });

test('the actual Laravel Description_images field survives product parsing', () => {
  const parsed = productSchema.parse({ ...product, Description_images: ['DescriptionImages/front.jpg', 'DescriptionImages/details.png'] });
  assert.deepEqual(parsed.Description_images, ['DescriptionImages/front.jpg', 'DescriptionImages/details.png']);
  assert.equal(productSchema.safeParse({ ...product, Description_images: '<script>' }).success, false);
});

test('detailed description preserves lines, escapes text and renders distinct backend images', () => {
  const html = renderToStaticMarkup(createElement(ProductDescription, { product: { ...product, health_benefit: 'First line\r\nSecond <b>line</b>', Description_images: ['DescriptionImages/front.jpg', 'DescriptionImages/front.jpg', '/storage/DescriptionImages/details.png'] }, imageOrigin: origin }));
  assert.ok(html.includes('Detailed description'));
  assert.ok(html.includes('First line\nSecond line'));
  assert.ok(!html.includes('<b>line</b>'));
  assert.ok(html.includes(`${origin}/storage/DescriptionImages/front.jpg`));
  assert.ok(html.includes(`${origin}/storage/DescriptionImages/details.png`));
  assert.equal((html.match(/class="description-image-button"/g) || []).length, 2);
  assert.ok(!html.includes('legacy.example'));
});

test('images can display without detailed text, while empty or unsafe sections stay absent', () => {
  const html = renderToStaticMarkup(createElement(ProductDescription, { product: { ...product, Description_images: ['DescriptionImages/only.jpg'] }, imageOrigin: origin }));
  assert.ok(html.includes('only.jpg'));
  assert.equal(renderToStaticMarkup(createElement(ProductDescription, { product, imageOrigin: origin })), '');
  assert.equal(renderToStaticMarkup(createElement(ProductDescription, { product: { ...product, Description_images: ['../../private.txt'] }, imageOrigin: origin })), '');
});
