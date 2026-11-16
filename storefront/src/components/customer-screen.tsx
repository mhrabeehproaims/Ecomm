'use client';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useEffect, useRef, useState } from 'react';
import { loadShoppingSnapshot } from '@/lib/shopping-request';
import { useShoppingSummaryUpdate } from './shopping-state';
import { customerApi, CustomerClientError } from '@/lib/customer-client';
import { money, productHref, productPrice } from '@/lib/catalog';
import { mediaUrl } from '@/lib/media';
import { Media } from './media';
import type { Address, CartItem, Customer, Order } from '@/lib/customer-contracts';
import type { Product } from '@/lib/contracts';
import { TestPayment } from './test-payment';
import { SecureCheckout } from './secure-checkout';
import { OrderSupport } from './order-support';
import { CartQuantity } from './cart-quantity';
import { deliveryStates, deliveryStateCode } from '@/lib/delivery-states';

type Kind = 'account' | 'cart' | 'wishlist' | 'checkout' | 'orders';
const titles: Record<Kind, string> = { account: 'Your own little corner.', cart: 'Your shopping bag.', wishlist: 'Your favourites, gathered.', checkout: 'A few final details.', orders: 'Your orders.' };

export function CustomerScreen({ kind, orderId, imageOrigin = '' }: { kind: Kind; orderId?: number; imageOrigin?: string }) {
  const router = useRouter();
  const acceptSummary = useShoppingSummaryUpdate();
  const loadSequence = useRef(0);
  const [customer, setCustomer] = useState<Customer | null>(null), [items, setItems] = useState<CartItem[]>([]), [products, setProducts] = useState<Product[]>([]), [orders, setOrders] = useState<Order[]>([]);
  const [loading, setLoading] = useState(true), [busy, setBusy] = useState(false), [message, setMessage] = useState(''), [error, setError] = useState(''), [editing, setEditing] = useState<Address | null>(null), [sandbox, setSandbox] = useState(false), [coupon, setCoupon] = useState('');
  async function load() {
    const sequence = ++loadSequence.current;
    setError('');
    try {
      if (kind === 'cart' || kind === 'wishlist') {
        const snapshot = await loadShoppingSnapshot();
        if (sequence !== loadSequence.current) return;
        setCustomer(snapshot.customer); setItems(snapshot.items); setProducts(snapshot.products); acceptSummary(snapshot.summary);
        return;
      }
      const session = await customerApi<{ customer: Customer }>('session'); setCustomer(session.customer);
      if (kind === 'checkout') setItems((await customerApi<{ items: CartItem[] }>('cart')).items);
      if (kind === 'orders') setOrders((await customerApi<{ orders: Order[] }>('orders')).orders);
      if (kind === 'checkout' || kind === 'orders') setSandbox((await customerApi<{ sandboxCheckout: boolean }>('capabilities')).sandboxCheckout);
    } catch (e) { if (sequence !== loadSequence.current || (e instanceof Error && e.name === 'AbortError')) return; if (e instanceof CustomerClientError && e.status === 401) router.replace(`/login?next=${encodeURIComponent(window.location.pathname)}`); else setError(e instanceof Error ? e.message : 'Please try again.'); }
    finally { if (sequence === loadSequence.current) setLoading(false); }
  }
  useEffect(() => { setLoading(true); void load(); return () => { loadSequence.current++; }; }, [kind, orderId]); // In-flight reads are shared; settled private data is never cached.
  async function mutate(action: string, payload: unknown) {
    setBusy(true); setError(''); setMessage('');
    try { const result = await customerApi<{ message?: string }>(action, payload); setMessage(result.message || 'Saved.'); if (action.startsWith('cart-')) setCoupon(''); if (action !== 'coupon') await load(); return true; }
    catch (e) { setError(e instanceof Error ? e.message : 'Please try again.'); return false; }
    finally { setBusy(false); }
  }
  const total = items.some(item => item.price === null) ? null : items.reduce((sum, item) => sum + item.price! * item.quantity, 0);
  const order = orders.find(o => o.order_id === orderId);
  return <div className="container customer-page"><div className="customer-heading"><p className="eyebrow">Your Vanthara</p><h1>{orderId ? `Order #${orderId}` : titles[kind]}</h1></div>
    <nav className="account-tabs" aria-label="Customer navigation"><Link href="/account">Profile & addresses</Link><Link href="/wishlist">Wishlist</Link><Link href="/cart">Bag</Link><Link href="/orders">Orders</Link><button disabled={busy} onClick={async () => { if (await mutate('logout', {})) { setCustomer(null); window.location.replace('/login'); } }}>Sign out</button></nav>
    {error && <p role="alert" className="form-error">{error}</p>}{message && <p role="status" className="form-success">{message}</p>}
    {loading ? <p role="status" className="customer-loading">Loading your account…</p> : !customer ? <p><Link className="text-link" href="/login">Sign in to continue</Link></p> : <>
    {kind === 'account' && <div className="account-columns"><section className="customer-panel"><h2>Your profile</h2><form className="customer-form" onSubmit={e => { e.preventDefault(); void mutate('profile', Object.fromEntries(new FormData(e.currentTarget))); }}><label>First name<input name="first_name" defaultValue={customer.first_name} required maxLength={100} /></label><label>Last name<input name="last_name" defaultValue={customer.last_name || ''} maxLength={100} /></label><label>Mobile number<input name="phone" type="tel" defaultValue={customer.phone} required pattern="[+]?[0-9]{7,15}" /></label><label>Email<input name="email" type="email" defaultValue={customer.email || ''} /></label><button className="button button-primary" disabled={busy}>Save profile</button></form></section>
    <section className="customer-panel"><h2>Delivery addresses</h2><div className="address-list">{customer.delivery_address.map(address => <article className="address-card" key={address.address_id}><p><strong>{address.is_default ? 'Default address' : address.city}</strong></p><p>{address.address_line1}{address.address_line2 ? `, ${address.address_line2}` : ''}<br />{address.city}, {address.state} {address.postal_code}<br />{address.country} · {address.phone}</p><div className="inline-actions"><button disabled={busy} onClick={() => setEditing(address)}>Edit address</button><button disabled={busy} onClick={() => void mutate('address-delete', { address_id: address.address_id })}>Remove address</button></div></article>)}{!customer.delivery_address.length && <p>No saved addresses yet.</p>}</div>
    <h3>{editing ? 'Edit delivery address' : 'Add a delivery address'}</h3><form key={editing?.address_id || 'new'} className="customer-form" onSubmit={async e => { e.preventDefault(); const form = e.currentTarget; const data = Object.fromEntries(new FormData(form)); if (await mutate('address-save', { ...data, is_default: data.is_default === 'on', ...(editing ? { address_id: editing.address_id } : {}) })) { setEditing(null); form.reset(); } }}>
      <label>Address line 1<input name="address_line1" required defaultValue={editing?.address_line1 || ''} /></label><label>Address line 2<input name="address_line2" defaultValue={editing?.address_line2 || ''} /></label><div className="form-columns"><label>City<input name="city" required defaultValue={editing?.city || ''} /></label><label>State<select aria-label="State" name="state" required defaultValue={deliveryStateCode(editing?.state || '') || ''}><option value="">Choose a state</option>{deliveryStates.map(([label, code]) => <option key={code} value={code}>{label}</option>)}</select></label><label>Country<input name="country" required defaultValue={editing?.country || 'India'} /></label><label>Postal code<input name="postal_code" required defaultValue={editing?.postal_code || ''} /></label></div><label>Delivery phone<input name="phone" type="tel" required pattern="[+]?[0-9]{7,15}" defaultValue={editing?.phone || customer.phone} /></label><label>Delivery email (optional)<input name="email" type="email" defaultValue={editing?.email || ''} /></label><label className="checkbox-label"><input name="is_default" type="checkbox" defaultChecked={editing?.is_default || false} />Use as default address</label><div className="inline-actions"><button className="button button-primary" disabled={busy}>Save address</button>{editing && <button type="button" onClick={() => setEditing(null)}>Cancel edit</button>}</div></form></section></div>}

    {kind === 'wishlist' && <section className="customer-panel"><h2>Saved pieces</h2>{products.length ? <div className="saved-grid">{products.map(product => <article className="saved-fashion-card" key={`${product.category}:${product.product_name}`}><Link className="saved-image" href={productHref(product)}><Media src={mediaUrl(imageOrigin, product.primary_image, product.image_path)} alt={product.product_name} sizes="(max-width: 767px) 44vw, 300px" /></Link><p className="eyebrow">{product.category}</p><h3><Link href={productHref(product)}>{product.product_name}</Link></h3><p>{productPrice(product) === null ? 'Price unavailable' : money(productPrice(product)!)}</p>{product.size && <p>Size: {product.size}</p>}<div className="inline-actions">{!product.size && <button disabled={busy} onClick={() => void mutate('cart-add', { product_id: product.product_id, quantity: 1 })}>Add to bag</button>}<Link className="text-link" href={productHref(product)}>{product.size ? 'Choose size' : 'View piece'}</Link><button disabled={busy} onClick={() => void mutate('wishlist', { product_id: product.product_id })}>Remove favourite</button></div></article>)}</div> : <p>Your wishlist is empty. <Link href="/products" className="text-link">Find a favourite</Link></p>}</section>}
    {kind === 'cart' && <div className="shopping-columns"><section className="customer-panel"><h2>{kind === 'cart' ? 'In your bag' : 'Review your items'}</h2>{items.length ? items.map(item => <article className="bag-item" key={item.id}>{item.image && <img src={item.image} alt={item.name} width={88} height={88} loading="lazy" />}<div className="bag-item-copy"><Link href={productHref({ product_id: item.productId, product_name: item.name })}><h3>{item.name}</h3></Link><p>{item.price === null ? 'Price unavailable' : `${money(item.price)} each`}</p>{kind === 'cart' ? <><CartQuantity name={item.name} quantity={item.quantity} stock={item.stock} busy={busy} save={quantity => void mutate('cart-update', { item_id: item.id, quantity })} /><button className="remove-item" disabled={busy} onClick={() => void mutate('cart-remove', { item_id: item.id })}>Remove item</button></> : <p>Quantity: {item.quantity}</p>}</div><strong>{item.price === null ? '—' : money(item.price * item.quantity)}</strong></article>) : <p>Your bag is empty. <Link className="text-link" href="/products">Explore the collection</Link></p>}</section>
    <aside className="customer-panel order-summary"><h2>Order summary</h2><dl><div><dt>Estimated product subtotal</dt><dd>{total === null ? 'Unavailable' : money(total)}</dd></div><div><dt>Delivery & final taxes</dt><dd>Not yet quoted</dd></div></dl><p className="form-help">The subtotal uses current catalog prices. It is not an authoritative checkout quote.</p>{items.length > 0 && <><form className="customer-form coupon-form" onSubmit={async e => { e.preventDefault(); const code = String(new FormData(e.currentTarget).get('coupon_code') || ''); if (await mutate('coupon', { coupon_code: code })) setCoupon(code); else setCoupon(''); }}><label>Coupon code<input name="coupon_code" maxLength={100} /></label><button className="button button-outline" disabled={busy}>Validate coupon</button></form>{coupon && <p role="status">{coupon} validated. Final discount awaits checkout confirmation.</p>}{kind === 'cart' && <Link href={coupon ? `/checkout?coupon=${encodeURIComponent(coupon)}` : '/checkout'} className="button button-primary">Continue to checkout</Link>}</>}</aside>

    </div>}

    {kind === 'checkout' && <SecureCheckout customer={customer} items={items} enabled={sandbox} />}

    {kind === 'orders' && (orderId ? order ? <section className="customer-panel"><div className="order-heading"><h2>Order #{order.order_id}</h2><span className="status-badge">{order.status === 'ontransist' ? 'In transit' : order.status}</span></div><p>Payment: {order.payment_status || 'Not recorded'} · {order.payment_method || 'Not recorded'}</p><p className="form-help">This is your latest order status. Detailed shipment tracking and delivery estimates are not available yet.</p><div className="saved-list">{order.items.map(item => <article className="saved-row" key={item.order_item_id}><Link href={productHref({ product_id: item.product_id, product_name: item.product_name })}>{item.product_name}</Link><span>{item.quantity} items · {money(item.total)} total</span>{item.return_status && <p>Return: {item.return_status}</p>}</article>)}</div><dl className="order-totals"><div><dt>Order total</dt><dd>{money(order.total_amount)}</dd></div><div><dt>Discount</dt><dd>{money(order.discount)}</dd></div><div><dt>Included tax</dt><dd>{order.total_tax_amount === null ? 'Not provided' : money(order.total_tax_amount)}</dd></div></dl>
      {sandbox && order.checkout_state === 'reserved' && order.payment_method === 'online' && order.payment_status !== 'paid' && <TestPayment order={order} refreshed={() => void load()} />}
      <OrderSupport order={order} refreshed={load} />
      </section> : <p role="status">This order was not found in your account.</p> : <section className="customer-panel"><h2>Order history</h2>{orders.length ? [...orders].sort((a, b) => b.order_id - a.order_id).map(o => <article className="order-row" key={o.order_id}><Link href={`/orders/${o.order_id}`}>Order #{o.order_id}</Link><span className="status-badge">{o.status}</span><strong>{money(o.total_amount)}</strong><span>{o.payment_status || 'Payment not recorded'}</span></article>) : <p>You haven’t placed an order yet.</p>}</section>)}
    </>}
  </div>;
}
