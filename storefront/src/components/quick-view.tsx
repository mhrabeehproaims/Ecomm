'use client';

import Link from 'next/link';
import { useEffect, useState } from 'react';
import { ArrowUpRight } from 'lucide-react';
import { money, numberOrNull, plainText, productHref, productPrice } from '@/lib/catalog';
import { productSchema, type Product } from '@/lib/contracts';
import { Modal } from './modal';
import { ProductActions } from './product-actions';
import { Media } from './media';
import { mediaUrl } from '@/lib/media';
import { customerApi, CustomerClientError } from '@/lib/customer-client';
import { AuthAlert } from './auth-alert';

export function QuickView({ productId, name, imageOrigin }: { productId: string; name: string; imageOrigin: string }) {
  const [open, setOpen] = useState(false), [product, setProduct] = useState<Product | null>(null), [error, setError] = useState(''), [attempt, setAttempt] = useState(0), [showLogin, setShowLogin] = useState(false), [addMessage, setAddMessage] = useState('');
  async function addToCart() {
    try { const result = await customerApi<{ message?: string }>('cart-add', { product_id: productId, quantity: 1 }); setAddMessage(result.message || 'Added to your bag.'); }
    catch (error) { if (error instanceof CustomerClientError && [401, 403].includes(error.status)) setShowLogin(true); else setAddMessage(error instanceof Error ? error.message : 'Could not add this item.'); }
  }
  useEffect(() => {
    if (!open) return;
    const controller = new AbortController(); setError(''); setProduct(null);
    void (async () => {
      try {
        const response = await fetch(`/api/product?${new URLSearchParams({ id: productId })}`, { cache: 'no-store', signal: controller.signal });
        const body = await response.json();
        if (!response.ok) throw new Error(body.message || 'This piece could not be loaded.');
        setProduct(productSchema.parse(body.product));
      } catch (error) { if (!controller.signal.aborted) setError(error instanceof Error ? error.message : 'Please try again.'); }
    })();
    return () => controller.abort();
  }, [open, productId, attempt]);
  const price = product ? productPrice(product) : null;
  return <><button className="quick-view-trigger" type="button" aria-label={`Quick view ${name}`} onClick={() => { setOpen(true); void addToCart(); }}>Add to cart</button><Modal open={open} onClose={() => setOpen(false)} title="A closer look" variant="center">
    {error ? <div className="quick-view-state" role="alert"><p>{error}</p><button type="button" className="text-link" onClick={() => setAttempt(attempt + 1)}>Try again</button></div> : !product ? <div className="quick-view-state" role="status"><span className="skeleton quick-view-skeleton" />Loading this piece…</div> : <div className="quick-view-content"><Media src={mediaUrl(imageOrigin, product.primary_image, product.image_path)} alt={product.product_name} sizes="(max-width: 767px) 90vw, 420px" /><div className="quick-view-copy"><p className="eyebrow">{product.category || 'Vanthara'}</p><h3>{product.product_name}</h3><p className="quick-view-price">{price === null ? 'Price unavailable' : money(price)}</p><p className="quick-view-description">{plainText(product.description)}</p><ProductActions productId={product.product_id} name={product.product_name} category={product.category} stock={numberOrNull(product.stock)} size={product.size} compact /><Link className="text-link" href={productHref(product)} onClick={() => setOpen(false)}>View full details <ArrowUpRight size={16} /></Link></div></div>}
  </Modal><AuthAlert open={showLogin} onClose={() => setShowLogin(false)} />{addMessage && !open && <span className="sr-only" role="status">{addMessage}</span>}</>;
}
