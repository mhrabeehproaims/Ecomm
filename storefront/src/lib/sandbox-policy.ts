export function permitsIsolatedCheckout(origin: string, enabled: boolean, identity: unknown): boolean {
  if (!enabled || !['127.0.0.1', 'localhost'].includes(new URL(origin).hostname)) return false;
  const marker = identity as Record<string, unknown> | null;
  return marker?.environment === 'isolated-storefront-test' && marker.database === 'isolated.sqlite' && (marker.payments === 'disabled' || marker.payments === 'test-only');
}
