import assert from 'node:assert/strict';
import { test } from 'node:test';
import { contactInput } from '../src/lib/contact-contracts';
import { footerInformation, footerServices, mapHref, publicContact, publicPageAlias } from '../src/lib/public-site';

test('footer destinations keep the source URLs and approved contact targets', () => {
  assert.deepEqual(footerInformation.map(link => link.href), ['/about', '/stories', '/contact', '/faq', '/account']);
  assert.deepEqual(footerServices.map(link => link.href), ['/shipping', '/policy', '/privacy-policy', '/terms-conditions', '/wholesale-enquiry']);
  assert.equal(new URL(mapHref).searchParams.get('query'), publicContact.address);
  assert.equal(publicContact.email, 'vantara@gmail.com');
  assert.equal(publicContact.phone, '9074709600');
});

test('legacy public aliases reject object prototype properties and preserve existing links', () => {
  assert.equal(publicPageAlias('privacy'), '/privacy-policy');
  assert.equal(publicPageAlias('terms'), '/terms-conditions');
  for (const value of ['constructor', '__proto__', 'toString', 'unknown']) assert.equal(publicPageAlias(value), undefined);
});

test('contact submissions follow the existing Laravel limits and discard privileged fields', () => {
  const enquiry = { name: 'Test buyer', email: 'buyer@example.test', phone: '+919000000001', message: 'An isolated test message.' };
  assert.deepEqual(contactInput.parse({ ...enquiry, status: 'resolved', recipient: 'attacker@example.test' }), enquiry);
  for (const changes of [{ name: '' }, { email: 'invalid' }, { phone: '123' }, { phone: '1234567890123456' }, { message: '' }, { message: 'x'.repeat(1001) }]) {
    assert.equal(contactInput.safeParse({ ...enquiry, ...changes }).success, false);
  }
});
