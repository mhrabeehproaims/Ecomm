import 'server-only';
import { cookies } from 'next/headers';
import { z } from 'zod';
import { apiOrigin } from './config';
import { customerSchema, orderSchema } from './customer-contracts';
import { openSession, sessionCookie } from './session-crypto';
import { permitsIsolatedCheckout } from './sandbox-policy';

export class CustomerError extends Error { constructor(public status: number, message: string, public errors?: Record<string, string[]>) { super(message); } }
export function sessionSecret() { const value = process.env.STOREFRONT_SESSION_SECRET || ''; if (value.length < 32) throw new CustomerError(503, 'Customer sessions are not configured.'); return value; }
export async function tokenFromCookie() { return openSession((await cookies()).get(sessionCookie)?.value, sessionSecret()); }

export async function customerRequest(path: string, token: string | null, method = 'GET', payload?: unknown): Promise<Record<string, unknown>> {
  let response: Response;
  try {
    response = await fetch(`${apiOrigin}/api/${path}`, { method, headers: { Accept: 'application/json', ...(payload ? { 'Content-Type': 'application/json' } : {}), ...(token ? { Authorization: `Bearer ${token}` } : {}) }, body: payload ? JSON.stringify(payload) : undefined, cache: 'no-store', redirect: 'error', signal: AbortSignal.timeout(12_000) });
  } catch { throw new CustomerError(503, 'We couldn’t reach the shop. Please try again.'); }
  let body: Record<string, unknown>;
  try { body = await response.json(); } catch { throw new CustomerError(502, 'The shop returned an unexpected response.'); }
  if (!response.ok || body.status === 'error' || body.status === 'failed' || body.status === false) {
    const publicUnavailable = new Set(['Razorpay test credentials are not configured. No payment was initiated.', 'Payment provider verification is unavailable. Check order status before retrying.', 'The payment provider response could not be confirmed. Check order status; another payment order was not created.']);
    const safe = typeof body.message === 'string' && (response.status < 500 || publicUnavailable.has(body.message)) ? body.message : response.status === 401 ? 'Please sign in again.' : 'The shop could not complete this request.';
    const errors = z.record(z.string(), z.array(z.string())).safeParse(body.errors);
    throw new CustomerError(response.status >= 400 ? response.status : 422, safe, errors.success ? errors.data : undefined);
  }
  return body;
}
export async function currentCustomer(token: string) {
  const response = await customerRequest('show-customer', token);
  const parsed = customerSchema.safeParse(response.data);
  if (!parsed.success) throw new CustomerError(502, 'Customer information is temporarily unavailable.');
  if (parsed.data.status !== 1) throw new CustomerError(403, 'This account is inactive. Please contact support.');
  return parsed.data;
}
export async function customerOrders(token: string) {
  const response = await customerRequest('purchase/history', token);
  const parsed = z.array(orderSchema).safeParse(response.data);
  if (!parsed.success) throw new CustomerError(502, 'Order information is temporarily unavailable.');
  return parsed.data;
}

export async function isolatedTransactionsEnabled() {
  if (process.env.STOREFRONT_SANDBOX_TRANSACTIONS !== 'true' || !['127.0.0.1', 'localhost'].includes(new URL(apiOrigin).hostname)) return false;
  try {
    const response = await fetch(`${apiOrigin}/__storefront-test/identity`, { cache: 'no-store', signal: AbortSignal.timeout(5000) });
    const body = await response.json();
    if (!permitsIsolatedCheckout(apiOrigin, true, body)) return false;
    const capabilities = await customerRequest('checkout/capabilities', null);
    return capabilities.checkoutEnabled === true && capabilities.testMode === true;
  } catch { return false; }
}
