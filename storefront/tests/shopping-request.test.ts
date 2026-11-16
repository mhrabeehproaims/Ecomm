import assert from 'node:assert/strict';
import { test } from 'node:test';
import { loadShoppingSnapshot, invalidateShoppingSnapshot } from '../src/lib/shopping-request';

const snapshot = { customer: { customer_id: 1, first_name: 'Test', phone: '9000000000', status: 1, delivery_address: [] }, items: [], products: [], summary: { signedIn: true, cartCount: 0, wishlist: [] } };

test('header and page join one in-flight private read, but settled results are never cached', async () => {
  const original = globalThis.fetch;
  let release!: (response: Response) => void;
  let calls = 0;
  invalidateShoppingSnapshot();
  globalThis.fetch = async (_input, options) => { calls++; assert.equal(options?.cache, 'no-store'); return new Promise<Response>(resolve => { release = resolve; }); };
  try {
    const page = loadShoppingSnapshot(), header = loadShoppingSnapshot();
    assert.equal(page, header); assert.equal(calls, 1);
    release(Response.json(snapshot)); await page;
    const fresh = loadShoppingSnapshot(); assert.equal(calls, 2);
    release(Response.json({ ...snapshot, summary: { ...snapshot.summary, cartCount: 2 } }));
    assert.equal((await fresh).summary.cartCount, 2);
  } finally { invalidateShoppingSnapshot(); globalThis.fetch = original; }
});

test('mutation invalidation aborts the old private read and creates a fresh request', async () => {
  const original = globalThis.fetch;
  let calls = 0;
  invalidateShoppingSnapshot();
  globalThis.fetch = async (_input, options) => {
    calls++;
    if (calls > 1) return Response.json(snapshot);
    return new Promise<Response>((_resolve, reject) => { options?.signal?.addEventListener('abort', () => reject(new DOMException('Read invalidated', 'AbortError')), { once: true }); });
  };
  try {
    const rejected = assert.rejects(loadShoppingSnapshot(), { name: 'AbortError' });
    invalidateShoppingSnapshot(); await rejected;
    assert.deepEqual(await loadShoppingSnapshot(), snapshot); assert.equal(calls, 2);
  } finally { invalidateShoppingSnapshot(); globalThis.fetch = original; }
});
