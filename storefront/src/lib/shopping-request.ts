'use client';
import { customerApi } from './customer-client';
import type { ShoppingSnapshot } from './shopping-contracts';

let active: { promise: Promise<ShoppingSnapshot>; controller: AbortController } | null = null;

// Join only a currently running request. Settled private data is never cached here.
export function loadShoppingSnapshot(): Promise<ShoppingSnapshot> {
  if (active) return active.promise;
  const controller = new AbortController();
  const promise = customerApi<ShoppingSnapshot>('shopping-state', undefined, controller.signal);
  const entry = { promise, controller };
  active = entry;
  const cleanup = () => { if (active === entry) active = null; };
  void promise.then(cleanup, cleanup);
  return promise;
}

export function invalidateShoppingSnapshot() {
  const previous = active;
  active = null;
  previous?.controller.abort();
}
