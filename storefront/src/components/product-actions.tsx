'use client';
import Link from 'next/link';
import { useState } from 'react';
import { Heart, ShoppingBag } from 'lucide-react';
import { customerApi, CustomerClientError } from '@/lib/customer-client';

export function ProductActions({ productId, stock }: { productId: string; stock: number | null }) {
  const [quantity, setQuantity] = useState(1), [busy, setBusy] = useState(false), [message, setMessage] = useState(''), [signIn, setSignIn] = useState(false);
  async function act(wishlist: boolean) {
    setBusy(true); setMessage(''); setSignIn(false);
    try { const result = await customerApi<{ message: string }>(wishlist ? 'wishlist' : 'cart-add', wishlist ? { product_id: productId } : { product_id: productId, quantity }); setMessage(result.message); }
    catch (error) { setSignIn(error instanceof CustomerClientError && error.status === 401); setMessage(error instanceof Error ? error.message : 'Please try again.'); }
    finally { setBusy(false); }
  }
  return <div className="purchase-actions"><label>Quantity<input type="number" aria-label="Product quantity" min={1} max={Math.min(stock || 1, 99)} value={quantity} onChange={e => setQuantity(Math.min(99, Math.max(1, Number(e.target.value) || 1)))} /></label><button className="button button-primary" disabled={busy || !stock || stock < 1} onClick={() => act(false)}><ShoppingBag size={18} />{busy ? 'Please wait…' : 'Add to bag'}</button><button className="button button-outline" disabled={busy} onClick={() => act(true)}><Heart size={18} />Save / unsave</button>{message && <div className="purchase-message" role="status"><p>{message}</p>{signIn ? <Link href="/login" className="text-link">Sign in to continue</Link> : <Link href="/cart" className="text-link">View your bag</Link>}</div>}</div>;
}
