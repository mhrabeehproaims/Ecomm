import assert from 'node:assert/strict';
import { test } from 'node:test';
import { customerOriginAllowed } from '../src/lib/customer-origin';

test('customer writes accept the configured origin in development and production', () => {
  for (const development of [false, true]) {
    assert.equal(customerOriginAllowed('https://shop.example', 'https://shop.example', development), true);
    assert.equal(customerOriginAllowed('http://127.0.0.1:3000', 'http://127.0.0.1:3000', development), true);
  }
});

test('development accepts localhost and IPv4 loopback aliases on the same protocol and port', () => {
  assert.equal(customerOriginAllowed('http://localhost:3000', 'http://127.0.0.1:3000', true), true);
  assert.equal(customerOriginAllowed('http://127.0.0.1:3000', 'http://localhost:3000', true), true);
});

test('development rejects missing, forged, differently hosted or differently addressed origins', () => {
  for (const origin of [null, '', 'null', 'invalid', 'http://evil.example', 'http://localhost.evil.example:3000',
    'http://localhost:3001', 'https://localhost:3000', 'http://localhost:3000/path', 'http://localhost:3000/',
    'http://user@localhost:3000', 'http://localhost:3000#fragment', 'http://localhost:3000?query',
    'http://localhost:3000 http://evil.example']) {
    assert.equal(customerOriginAllowed(origin, 'http://127.0.0.1:3000', true), false, String(origin));
  }
  assert.equal(customerOriginAllowed('http://localhost:3000', 'https://shop.example', true), false);
});

test('production never accepts loopback aliases or unrelated public origins', () => {
  assert.equal(customerOriginAllowed('http://localhost:3000', 'http://127.0.0.1:3000', false), false);
  assert.equal(customerOriginAllowed('https://evil.example', 'https://shop.example', false), false);
  assert.equal(customerOriginAllowed('https://shop.example.evil.example', 'https://shop.example', false), false);
});
