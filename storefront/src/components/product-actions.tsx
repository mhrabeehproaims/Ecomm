'use client';
import Link from 'next/link';
import { useId, useRef, useState } from 'react';
import { ShoppingBag } from 'lucide-react';
import { customerApi, CustomerClientError } from '@/lib/customer-client';
import { WishlistToggle } from './shopping-state';

export function ProductActions({ productId, stock, size, name = 'Product', category, compact = false }: { productId: string; stock: number | null; size?: string | null; name?: string; category?: string | null; compact?: boolean }) {
  const availableSize = size?.trim();
  const sizeButton = useRef<HTMLButtonElement>(null);
  const sizeId = useId();
  const [sizePrompt, setSizePrompt] = useState(false);
  const [selected, setSelected] = useState(false), [quantity, setQuantity] = useState(1), [busy, setBusy] = useState(false), [message, setMessage] = useState(''), [signIn, setSignIn] = useState(false);
  const available = stock !== null && stock > 0;
  const needsSize = Boolean(availableSize) && !selected;
  async function add() {
    if (!available || busy) return;
    if (needsSize) {
      setSizePrompt(true);
      sizeButton.current?.focus({ preventScroll: true });
      sizeButton.current?.scrollIntoView({ block: 'center', behavior: 'auto' });
      return;
    }
    setBusy(true); setMessage(''); setSignIn(false);
    try { const result = await customerApi<{ message: string }>('cart-add', { product_id: productId, quantity }); setMessage(result.message); }
    catch (error) { setSignIn(error instanceof CustomerClientError && error.status === 401); setMessage(error instanceof Error ? error.message : 'Please try again.'); }
    finally { setBusy(false); }
  }
  return <div className={`purchase-actions ${compact ? 'purchase-actions--compact' : ''}`}>
    {availableSize && <fieldset id={sizeId} className="size-choice"><legend>Size <span>{selected ? availableSize : 'Select your size'}</span></legend><div className="size-options"><button ref={sizeButton} type="button" aria-pressed={selected} disabled={!available} onClick={() => { setSelected(true); setSizePrompt(false); }}>{availableSize}</button></div><p role={sizePrompt ? 'status' : undefined}>{sizePrompt ? 'Choose a size to continue.' : 'Available size for this product.'}</p></fieldset>}
    {available && <div className="purchase-quantity"><label htmlFor={`quantity-${compact ? 'quick' : 'detail'}`}>Quantity</label><input id={`quantity-${compact ? 'quick' : 'detail'}`} type="number" aria-label="Product quantity" min={1} max={Math.min(stock || 1, 99)} value={quantity} onChange={e => setQuantity(Math.min(Math.max(1, stock || 1), 99, Math.max(1, Number(e.target.value) || 1)))} /></div>}
    <div className="purchase-buy-row"><button type="button" className="button button-primary" disabled={busy || !available} aria-busy={busy} aria-controls={needsSize ? sizeId : undefined} onClick={() => void add()}><ShoppingBag size={18} aria-hidden="true" />{busy ? 'Adding…' : !available ? 'Unavailable' : needsSize ? 'Select a size' : 'Add to bag'}</button><WishlistToggle product={{ product_id: productId, product_name: name, category }} /></div>
    {message && <div className="purchase-message" role="status"><p>{message}</p>{signIn ? <Link href="/login?next=%2Fcart" className="text-link">Sign in to continue</Link> : <Link href="/cart" className="text-link">View your bag</Link>}</div>}
  </div>;
}
