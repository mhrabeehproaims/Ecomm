import { test } from 'node:test';
import assert from 'node:assert/strict';
import { openSession, sealSession, sessionLifetime } from '../src/lib/session-crypto';
import { addressInput, profileInput, registerInput } from '../src/lib/customer-contracts';
import { deliveryStateCode } from '../src/lib/delivery-states';
import { permitsIsolatedCheckout } from '../src/lib/sandbox-policy';
import { decodedSegment } from '../src/lib/route-segment';
import { safeNextPath } from '../src/lib/customer-client';
const secret = 'unit-test-secret-at-least-32-characters-long';
test('sign-in return URLs allow customer routes and reject redirect tricks', () => {
  assert.equal(safeNextPath('/orders/15'), '/orders/15');
  assert.equal(safeNextPath('/checkout?coupon=TESTSAVE'), '/checkout?coupon=TESTSAVE');
  for (const value of [null, '//evil.example', '/\\evil.example', '/%2f%2fevil.example', '/api/customer/logout', 'https://evil.example', '/%5cevil.example']) assert.equal(safeNextPath(value), '/account');
});
test('category route segments preserve spaces, literal percent signs and Unicode', () => {
  for (const name of ['Isolated test collection', '100% cotton', 'വന്താര']) assert.equal(decodedSegment(encodeURIComponent(name)), name);
  assert.equal(decodedSegment('%invalid'), null);
});
test('customer cookies encrypt bearer tokens, reject tampering and expire', () => {
  const value = sealSession('private-bearer-token', secret, 1000);
  assert.ok(!value.includes('private-bearer-token'));
  assert.equal(openSession(value, secret, 2000), 'private-bearer-token');
  assert.equal(openSession(value, secret, 1000 + sessionLifetime * 1000), null);
  assert.equal(openSession(value.slice(0, -4) + 'AAAA', secret, 2000), null);
  assert.equal(openSession(value, secret + 'other', 2000), null);
});
test('customer write schemas omit privileged fields and enforce required values', () => {
  const data = profileInput.parse({ first_name: 'Test', phone: '9000000011', status: 1, password: 'injected' });
  assert.equal('status' in data, false); assert.equal('password' in data, false);
  assert.equal(registerInput.safeParse({ first_name: 'Test', phone: 'invalid', password: 'short' }).success, false);
  assert.equal(addressInput.safeParse({ address_line1: 'Incomplete' }).success, false);
});
test('delivery states preserve the backend enum contract', () => { assert.equal(deliveryStateCode('Kerala'), 'kerala (KL)'); assert.equal(deliveryStateCode('kerala (KL)'), 'kerala (KL)'); assert.equal(deliveryStateCode('Unknown'), null); });
test('legacy checkout cannot be enabled on production origins or without an isolated marker', () => {
  const marker = { environment: 'isolated-storefront-test', database: 'isolated.sqlite', payments: 'disabled' };
  assert.equal(permitsIsolatedCheckout('https://vanthara.com', true, marker), false);
  assert.equal(permitsIsolatedCheckout('http://127.0.0.1:8138', false, marker), false);
  assert.equal(permitsIsolatedCheckout('http://127.0.0.1:8138', true, null), false);
  assert.equal(permitsIsolatedCheckout('http://127.0.0.1:8138', true, marker), true);
});
