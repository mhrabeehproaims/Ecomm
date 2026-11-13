import assert from 'node:assert/strict';
import { test } from 'node:test';
import { createElement } from 'react';
import { renderToStaticMarkup } from 'react-dom/server';
import { ReturnPolicies } from '../src/components/return-policies';
import { envelope, returnPolicySchema } from '../src/lib/contracts';

// Synthetic policy data is confined to contract/security tests, never storefront inventory.
const fixture = { policy_id: 1, title: 'Contract fixture policy', return_days: '7', return_type: 'refund_or_exchange', conditions: '<p>Keep the original packaging.</p>' };

test('published policy contract accepts Laravel integer strings and drops internal fields', () => {
  const parsed = returnPolicySchema.parse({ ...fixture, created_by: 42, created_at: 'private' });
  assert.equal(parsed.return_days, 7);
  assert.equal('created_by' in parsed, false);
  assert.equal('created_at' in parsed, false);
  assert.deepEqual(envelope(returnPolicySchema).parse({ status: 'success', data: [] }).data, []);
});

test('policy contract rejects unknown return types and invalid return windows', () => {
  for (const return_days of [-1, ' ', null, 'seven', 1.5]) assert.equal(returnPolicySchema.safeParse({ ...fixture, return_days }).success, false);
  assert.equal(returnPolicySchema.safeParse({ ...fixture, return_type: 'unapproved-type' }).success, false);
});

test('policy rendering preserves published values without executing supplied HTML', () => {
  const policy = returnPolicySchema.parse({ ...fixture, title: '<img src=x onerror=alert(1)>', conditions: '<p>Keep the packaging.</p><script>alert(1)</script>' });
  const html = renderToStaticMarkup(createElement(ReturnPolicies, { result: { ok: true, data: { status: 'success', data: [policy] } } }));
  assert.ok(html.includes('7 days'));
  assert.ok(html.includes('Refund or exchange'));
  assert.ok(html.includes('Keep the packaging.'));
  assert.ok(!html.includes('<img'));
  assert.ok(!html.includes('<script>'));
  assert.ok(!html.includes('No return policies have been published'));
});

test('empty policy collections differ from API failures without inventing terms', () => {
  const empty = renderToStaticMarkup(createElement(ReturnPolicies, { result: { ok: true, data: { status: 'success', data: [] } } }));
  const failure = renderToStaticMarkup(createElement(ReturnPolicies, { result: { ok: false, kind: 'unavailable' } }));
  assert.ok(empty.includes('No return policies have been published yet.'));
  assert.ok(empty.includes('No return window is promised'));
  assert.ok(failure.includes('We couldn’t load the current policies.'));
  assert.ok(!failure.includes('No return policies have been published yet.'));
});
