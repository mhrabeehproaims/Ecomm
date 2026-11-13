'use client';
import Link from 'next/link';
import { useRef, useState } from 'react';
import { useRouter } from 'next/navigation';
import { customerApi, CustomerClientError } from '@/lib/customer-client';
import { money } from '@/lib/catalog';
import type { CartItem, CheckoutQuote, Customer, Order } from '@/lib/customer-contracts';

export function SecureCheckout({ customer, items, enabled }: { customer: Customer; items: CartItem[]; enabled: boolean }) {
  const router = useRouter();
  const [address, setAddress] = useState(customer.delivery_address.find(a => a.is_default)?.address_id || customer.delivery_address[0]?.address_id || 0);
  const [coupon, setCoupon] = useState(() => typeof window === 'undefined' ? '' : new URLSearchParams(window.location.search).get('coupon') || '');
  const [method, setMethod] = useState<'online' | 'cod'>('online');
  const [quote, setQuote] = useState<CheckoutQuote | null>(null), [busy, setBusy] = useState(false), [error, setError] = useState('');
  const key = useRef('');
  const changed = () => { setQuote(null); key.current = ''; setError(''); };
  const minor = (amount: number) => money(amount / 100);
  return <div className="shopping-columns secure-checkout"><section className="customer-panel"><h2>Delivery & payment</h2>
    {enabled ? <p className="sandbox-notice">ISOLATED TEST CHECKOUT — sample data and test payments only. Production checkout is disabled.</p> : <p className="phase-notice" role="status">Checkout is temporarily unavailable. Your bag is saved.</p>}
    {!items.length ? <p>Your bag is empty. <Link className="text-link" href="/products">Explore the collection</Link></p> : !customer.delivery_address.length ? <Link className="text-link" href="/account">Add a delivery address</Link> : <form id="secure-checkout-form" className="customer-form" onSubmit={async e => {
      e.preventDefault(); if (!quote?.quote_id || !key.current || busy) return;
      setBusy(true); setError('');
      try {
        const result = await customerApi<{ order: Order }>('checkout', { quote_id: quote.quote_id, idempotency_key: key.current });
        router.push(`/orders/${result.order.order_id}`);
      } catch (e) {
        setError(e instanceof Error ? e.message : 'Check order history before retrying.');
        if (e instanceof CustomerClientError && [409, 422].includes(e.status)) setQuote(null);
      } finally { setBusy(false); }
    }}>
      <fieldset disabled={busy || !enabled}><legend className="sr-only">Choose delivery and payment</legend>
        <label>Delivery address<select aria-label="Delivery address" value={address} onChange={e => { setAddress(Number(e.target.value)); changed(); }}>{customer.delivery_address.map(a => <option key={a.address_id} value={a.address_id}>{a.address_line1}, {a.city} {a.postal_code}</option>)}</select></label>
        <label>Payment method<select aria-label="Payment method" value={method} onChange={e => { setMethod(e.target.value as 'online' | 'cod'); changed(); }}><option value="online">Razorpay (test mode only)</option><option value="cod">Cash on delivery (subject to availability)</option></select></label>
        <label>Coupon code<input name="coupon_code" maxLength={100} value={coupon} onChange={e => { setCoupon(e.target.value); changed(); }} /></label>
        <button type="button" className="button button-outline" disabled={!items.length} onClick={async () => {
          setBusy(true); setError(''); setQuote(null);
          try {
            const result = await customerApi<{ quote: CheckoutQuote }>('quote', { address_id: address, coupon_code: coupon, payment_method: method });
            key.current = crypto.randomUUID(); setQuote(result.quote);
          } catch (e) { setError(e instanceof Error ? e.message : 'A quote could not be prepared.'); } finally { setBusy(false); }
        }}>{busy ? 'Please wait…' : 'Review final quote'}</button>
      </fieldset>
    </form>}
    {error && <p className="form-error" role="alert">{error}</p>}
    <p className="form-help">An online order reserves items for 15 minutes. A successful payment must be verified before the order is confirmed. <Link href="/orders" className="text-link">Check your orders</Link> before retrying an interrupted submission.</p>
  </section><aside className="customer-panel order-summary"><h2>Review your order</h2>
    {quote ? <div className="verified-quote"><p className="form-success" role="status">Your final quote is ready.</p>{quote.items.map((item, i) => <div className="quote-item" key={`${item.name}-${i}`}><strong>{item.name}</strong><p>{item.quantity} × {minor(item.unit_minor)} <span>{minor(item.total_minor)}</span></p>{item.discount_minor > 0 && <p>Item discount {minor(item.discount_minor)}</p>}</div>)}<dl>
      <div><dt>Products</dt><dd>{minor(quote.subtotal_minor)}</dd></div><div><dt>Coupon discount</dt><dd>−{minor(quote.discount_minor)}</dd></div>
      <div><dt>Delivery</dt><dd>{minor(quote.shipping_minor)}</dd></div><div><dt>Included tax</dt><dd>{minor(quote.tax_minor)}</dd></div>
      <div className="quote-payable"><dt>Total payable</dt><dd>{minor(quote.total_minor)}</dd></div></dl><p className="form-help">Prices include tax. Valid until {quote.expires_at ? new Date(quote.expires_at).toLocaleTimeString() : 'the quoted expiry'}. Changes to your bag, address or prices require a new quote.</p></div>
      : <><p>Review a final quote for your chosen address before placing an order.</p>{items.map(item => <p key={item.id}>{item.quantity} × {item.name}</p>)}</>}
    <button form="secure-checkout-form" type="submit" className="button button-primary" disabled={!enabled || busy || !quote || !items.length}>{busy ? 'Submitting once…' : 'Place isolated test order'}</button>
  </aside></div>;
}
