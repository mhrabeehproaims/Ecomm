import assert from 'node:assert/strict';
import { test } from 'node:test';
import { shoppingKey } from '../src/lib/shopping-key';
test('wishlist state survives randomized encrypted identifiers without mixing categories', () => {
  assert.equal(shoppingKey({ product_name: 'Shirt', category: 'Clothing' }), shoppingKey({ product_name: 'Shirt', category: 'Clothing' }));
  assert.notEqual(shoppingKey({ product_name: 'Shirt', category: 'Clothing' }), shoppingKey({ product_name: 'Shirt', category: 'Other' }));
});
