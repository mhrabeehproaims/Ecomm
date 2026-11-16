import assert from 'node:assert/strict';
import { test } from 'node:test';
import { wholesaleInput, wholesalePayload } from '../src/lib/wholesale-contracts';

const enquiry = { name: 'Test buyer', email: 'buyer@example.test', phone: '+919000000001', company_name: 'Isolated business', business_type: 'Retailer', message: 'Please share availability for our business.' };

test('wholesale enquiries preserve every form field in the existing Laravel contract', () => {
  const parsed = wholesaleInput.parse({ ...enquiry, status: 'resolved' });
  assert.deepEqual(wholesalePayload(parsed), {
    name: enquiry.name, email: enquiry.email, phone: enquiry.phone, company_name: enquiry.company_name,
    message: 'Type of business: Retailer\n\nPlease share availability for our business.',
  });
  assert.equal('status' in parsed, false);
});

test('wholesale validation respects required fields, phone format and database field lengths', () => {
  assert.equal(wholesaleInput.safeParse(enquiry).success, true);
  for (const changes of [{ name: ' ' }, { email: 'bad' }, { email: 'a'.repeat(95) + '@example.test' },
    { phone: '123' }, { phone: '1234567890123456' }, { company_name: '' }, { business_type: '' },
    { business_type: 'Unknown' }, { message: ' ' }, { message: 'a'.repeat(5001) }]) {
    assert.equal(wholesaleInput.safeParse({ ...enquiry, ...changes }).success, false);
  }
});
