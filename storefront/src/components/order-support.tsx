'use client';
import { useEffect, useRef, useState } from 'react';
import { customerApi } from '@/lib/customer-client';
import type { Order, OrderTracking, ReturnEligibility } from '@/lib/customer-contracts';

export function OrderSupport({ order, refreshed }: { order: Order; refreshed: () => Promise<void> }) {
  const [eligibility, setEligibility] = useState<ReturnEligibility | null>(null), [tracking, setTracking] = useState<OrderTracking | null>(null);
  const [error, setError] = useState(''), [message, setMessage] = useState(''), [busy, setBusy] = useState(false);
  useEffect(() => {
    let active = true; setError('');
    void customerApi<{ eligibility: ReturnEligibility; tracking: OrderTracking }>(`order-support?order_id=${order.order_id}`)
      .then(data => { if (active) { setEligibility(data.eligibility); setTracking(data.tracking); } }).catch(e => { if (active) setError(e instanceof Error ? e.message : 'Order support is unavailable.'); });
    return () => { active = false; };
  }, [order]);
  const saved = async () => { setMessage('Return request submitted for review. A refund or replacement is not yet approved.'); await refreshed(); };
  return <div className="order-support">{message && <p role="status" className="form-success">{message}</p>}{error && <p role="alert" className="form-error">{error}</p>}
    {order.checkout_state === 'refund_required' && <p className="phase-notice">Payment arrived after this reservation was released. Your order will not be fulfilled automatically; refund review is required.</p>}
    {order.checkout_state === 'reserved' && <div className="reservation-panel"><p>Items are reserved until {order.reservation_expires_at || 'the recorded expiry'}. If payment is interrupted, retry this order before creating another.</p><button className="button button-outline" disabled={busy} onClick={async () => {
      setBusy(true); setError(''); try { await customerApi('order-cancel', { order_id: order.order_id }); setMessage('Reservation cancelled. Your bag is saved.'); await refreshed(); } catch (e) { setError(e instanceof Error ? e.message : 'Cancellation could not be confirmed.'); } finally { setBusy(false); }
    }}>Cancel unpaid reservation</button></div>}
    {order.address_snapshot && <section className="order-address"><h3>Delivery address</h3><p>{order.address_snapshot.address_line1}{order.address_snapshot.address_line2 ? `, ${order.address_snapshot.address_line2}` : ''}<br />{order.address_snapshot.city}, {order.address_snapshot.state} {order.address_snapshot.postal_code}</p></section>}
    {tracking && <section className="order-tracking"><h3>Order updates</h3>{tracking.tracking_number && <p>Tracking reference: {tracking.tracking_number}</p>}<ol>{tracking.events.map((event, i) => <li key={`${event.changed_at}-${i}`}><strong>{event.status === 'ontransist' ? 'In transit' : event.status}</strong><span>{event.changed_at}</span></li>)}</ol><p className="form-help">Carrier tracking updates are not connected. These are the recorded Vanthara order updates.</p></section>}
    {order.status === 'delivered' && <section className="return-request"><h3>Request a return</h3><p className="form-help">Eligibility depends on your recorded delivery date, payment, policy and remaining purchased quantity. Requests require review.</p>{!eligibility ? <p>Checking return eligibility…</p> : eligibility.items.some(i => i.eligible) ? <ReturnForm order={order} eligibility={eligibility} submitted={saved} /> : <p>No items are currently eligible for another return request.</p>}</section>}
  </div>;
}

function ReturnForm({ order, eligibility, submitted }: { order: Order; eligibility: ReturnEligibility; submitted: () => Promise<void> }) {
  const choices = eligibility.items.filter(i => i.eligible);
  const [selected, setSelected] = useState(choices[0].order_item_id), [type, setType] = useState(choices[0].allowed_types[0]);
  const [busy, setBusy] = useState(false), [error, setError] = useState('');
  const key = useRef('');
  const rule = choices.find(i => i.order_item_id === selected) || choices[0];
  return <form className="customer-form" onSubmit={async e => {
    e.preventDefault(); setBusy(true); setError(''); if (!key.current) key.current = crypto.randomUUID();
    const data = Object.fromEntries(new FormData(e.currentTarget));
    try { await customerApi('return', { ...data, order_id: order.order_id, order_item_id: selected, return_type: type, quantity: Number(data.quantity), request_key: key.current }); await submitted(); }
    catch (e) { setError(e instanceof Error ? e.message : 'Request could not be confirmed.'); } finally { setBusy(false); }
  }}><fieldset disabled={busy}><legend className="sr-only">Return details</legend><label>Order item<select aria-label="Order item" value={selected} onChange={e => { const id = Number(e.target.value); setSelected(id); setType(choices.find(i => i.order_item_id === id)!.allowed_types[0]); key.current = ''; }}>{choices.map(i => <option value={i.order_item_id} key={i.order_item_id}>{order.items.find(item => item.order_item_id === i.order_item_id)?.product_name}</option>)}</select></label>
    <label>Return quantity<input name="quantity" type="number" min={1} max={rule.remaining_quantity} defaultValue={1} required onChange={() => { key.current = ''; }} /></label>
    <label>Return type<select aria-label="Return type" value={type} onChange={e => { setType(e.target.value as typeof type); key.current = ''; }}>{rule.allowed_types.map(t => <option key={t} value={t}>{t === 'refund' ? 'Refund request' : 'Replacement request'}</option>)}</select></label>
    <p className="form-help">Policy deadline: {rule.deadlines[type] || 'Unavailable'}</p>
    <label>Reason<textarea name="return_reason" minLength={5} maxLength={1000} required onChange={() => { key.current = ''; }} /></label>
    <label>Product condition<select aria-label="Product condition" name="product_condition" onChange={() => { key.current = ''; }}><option value="unused">Unused</option><option value="damaged">Damaged</option><option value="opened">Opened</option><option value="other">Other</option></select></label>
    <button className="button button-outline">{busy ? 'Submitting…' : 'Submit return request'}</button></fieldset>{error && <p role="alert" className="form-error">{error}</p>}</form>;
}
