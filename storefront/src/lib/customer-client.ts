'use client';
export class CustomerClientError extends Error { constructor(message: string, public status: number) { super(message); } }
export async function customerApi<T = Record<string, unknown>>(action: string, payload?: unknown): Promise<T> {
  const response = await fetch(`/api/customer/${action}`, { method: payload === undefined ? 'GET' : 'POST', headers: payload === undefined ? undefined : { 'Content-Type': 'application/json' }, body: payload === undefined ? undefined : JSON.stringify(payload), cache: 'no-store' });
  const body = await response.json();
  if (!response.ok) {
    const details = body.errors ? Object.values(body.errors).flat().filter(Boolean).join(' ') : '';
    throw new CustomerClientError(details || body.message || 'Please try again.', response.status);
  }
  return body as T;
}
export function safeNextPath(value: string | null) {
  if (!value?.startsWith('/') || value.startsWith('//') || value.includes('\\')) return '/account';
  try {
    const target = new URL(value, 'https://storefront.invalid');
    return target.origin === 'https://storefront.invalid' && /^\/(account|cart|wishlist|checkout|orders(?:\/\d+)?)$/.test(target.pathname) ? target.pathname + target.search : '/account';
  } catch { return '/account'; }
}
