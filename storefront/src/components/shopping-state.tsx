'use client';

import { createContext, type ReactNode, useCallback, useContext, useEffect, useRef, useState } from 'react';
import Link from 'next/link';
import { Heart, ShoppingBag } from 'lucide-react';
import { customerApi, CustomerClientError } from '@/lib/customer-client';
import { shoppingKey } from '@/lib/shopping-key';
import { loadShoppingSnapshot, invalidateShoppingSnapshot } from '@/lib/shopping-request';
import type { ShoppingSummary } from '@/lib/shopping-contracts';

type Summary = ShoppingSummary;
type ShoppingState = { summary: Summary | null; status: 'loading' | 'ready' | 'error'; acceptSummary: (summary: Summary) => void };
const ShoppingContext = createContext<ShoppingState>({ summary: null, status: 'loading', acceptSummary: () => {} });

export function ShoppingProvider({ children }: { children: ReactNode }) {
  const [state, setState] = useState<Summary | null>(null);
  const [status, setStatus] = useState<ShoppingState['status']>('loading');
  const acceptSummary = useCallback((summary: Summary) => { setState(summary); setStatus('ready'); }, []);
  const controller = useRef<AbortController | null>(null);
  const refresh = useCallback(async () => {
    controller.current?.abort();
    setStatus('loading');
    const next = new AbortController(); controller.current = next;
    try {
      let data: Summary;
      if (['/cart', '/wishlist'].includes(window.location.pathname)) {
        data = (await loadShoppingSnapshot()).summary;
      } else {
        const response = await fetch('/api/customer/shopping-summary', { cache: 'no-store', signal: next.signal });
        if (!response.ok) throw new Error('Shopping state unavailable');
        data = await response.json();
      }
      if (next.signal.aborted) return;
      if (typeof data.signedIn !== 'boolean' || !Number.isInteger(data.cartCount) || data.cartCount < 0 || !Array.isArray(data.wishlist) || !data.wishlist.every((key: unknown) => typeof key === 'string')) throw new Error('Invalid shopping state');
      setState(data); setStatus('ready');
    } catch (error) {
      if (!next.signal.aborted) {
        if (error instanceof CustomerClientError && [401, 403].includes(error.status)) { setState({ signedIn: false, cartCount: 0, wishlist: [] }); setStatus('ready'); }
        else { setState(null); setStatus('error'); }
      }
    }
  }, []);
  useEffect(() => {
    void refresh();
    const update = () => { if (document.visibilityState === 'visible') void refresh(); };
    const mutated = () => { invalidateShoppingSnapshot(); update(); };
    window.addEventListener('vanthara:customer-updated', mutated);
    window.addEventListener('focus', update);
    return () => { controller.current?.abort(); window.removeEventListener('vanthara:customer-updated', mutated); window.removeEventListener('focus', update); };
  }, [refresh]);
  return <ShoppingContext.Provider value={{ summary: state, status, acceptSummary }}>{children}</ShoppingContext.Provider>;
}

export function useShoppingSummaryUpdate() { return useContext(ShoppingContext).acceptSummary; }

export function BagLink({ compact = false }: { compact?: boolean }) {
  const { summary: state } = useContext(ShoppingContext);
  return <Link href="/cart" className="header-bag" aria-label={`Your shopping bag${state ? `, ${state.cartCount} items` : ''}`}><ShoppingBag size={21} strokeWidth={1.4} aria-hidden="true" />{!compact && <span className="bag-label">Bag</span>}{state && <span className="bag-count">{state.cartCount}</span>}</Link>;
}

export function WishlistToggle({ product, className = '' }: { product: { product_id: string; product_name: string; category?: string | null }; className?: string }) {
  const { summary: state, status } = useContext(ShoppingContext);
  const saved = state?.wishlist.includes(shoppingKey(product)) ?? false;
  const [busy, setBusy] = useState(false), [message, setMessage] = useState(''), [needsLogin, setNeedsLogin] = useState(false);
  return <div className={`wishlist-control ${className}`}><button type="button" className={`icon-button wishlist-toggle ${saved ? 'is-saved' : ''}`} aria-label={status === 'loading' ? `Loading wishlist for ${product.product_name}` : !state ? `Wishlist unavailable for ${product.product_name}` : `${saved ? 'Remove' : 'Save'} ${product.product_name} ${saved ? 'from' : 'to'} wishlist`} title={status === 'error' ? 'Wishlist could not be loaded. Reload the page to try again.' : undefined} aria-busy={busy || status === 'loading'} aria-pressed={saved} disabled={busy || status !== 'ready' || !state} onClick={async () => {
    setBusy(true); setMessage(''); setNeedsLogin(false);
    try { const result = await customerApi<{ message: string }>('wishlist', { product_id: product.product_id }); setMessage(result.message || 'Wishlist updated.'); }
    catch (error) { setNeedsLogin(error instanceof CustomerClientError && error.status === 401); setMessage(error instanceof Error ? error.message : 'Please try again.'); }
    finally { setBusy(false); }
  }}><Heart size={19} strokeWidth={1.4} fill={saved ? 'currentColor' : 'none'} aria-hidden="true" /></button>{message && <div className="wishlist-feedback" role="status">{message}{needsLogin && <Link href="/login?next=%2Fwishlist">Sign in to save</Link>}<button type="button" aria-label="Dismiss wishlist message" onClick={() => setMessage('')}>Dismiss</button></div>}</div>;
}
