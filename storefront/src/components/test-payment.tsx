'use client';
import { useState } from 'react';
import { customerApi } from '@/lib/customer-client';
import type { Order } from '@/lib/customer-contracts';

type GatewayData = { key: string; orderId: string; amount: number; currency: string; customer: { name: string; email: string | null; contact: string } };
type GatewayOptions = { key: string; order_id: string; amount: number; currency: string; name: string; prefill: GatewayData['customer']; handler: (result: { razorpay_payment_id: string; razorpay_order_id: string; razorpay_signature: string }) => Promise<void>; modal: { ondismiss: () => void } };
declare global { interface Window { Razorpay?: new (options: GatewayOptions) => { open: () => void; on: (event: string, handler: (data: { error?: { metadata?: { payment_id?: string; order_id?: string } } }) => void) => void } } }

export function TestPayment({ order, refreshed }: { order: Order; refreshed: () => void }) {
  const [busy, setBusy] = useState(false), [message, setMessage] = useState('');
  return <div className="payment-panel"><p>Payments are available only in an explicitly isolated test environment. Live payments are disabled.</p><button className="button button-outline" disabled={busy} onClick={async () => {
    setBusy(true); setMessage('');
    try {
      const data = await customerApi<GatewayData>('payment-start', { order_id: order.order_id });
      if (!data.key.startsWith('rzp_test_')) throw new Error('Live payment keys are not accepted.');
      if (!window.Razorpay) await new Promise<void>((resolve, reject) => { const script = document.createElement('script'); script.src = 'https://checkout.razorpay.com/v1/checkout.js'; script.onload = () => resolve(); script.onerror = () => reject(new Error('Payment window could not be loaded.')); document.head.appendChild(script); });
      if (!window.Razorpay) throw new Error('Payment window is unavailable.');
      const checkout = new window.Razorpay({ key: data.key, order_id: data.orderId, amount: data.amount, currency: data.currency, name: 'Vanthara — TEST', prefill: data.customer, handler: async result => {
        try { const resultState = await customerApi<{ paid: boolean; payment_status?: string; requires_review?: boolean; message?: string }>('payment-confirm', result); setMessage(resultState.paid ? ['refunded', 'partially_refunded'].includes(resultState.payment_status || '') ? 'A refund is recorded for this payment. Review your order for the current balance.' : resultState.requires_review ? 'Payment requires operator review. Fulfillment is paused; check your order status.' : 'Your test payment is confirmed.' : resultState.message || 'Payment verification is pending. Check your order status.'); refreshed(); }
        catch (error) { setMessage(error instanceof Error ? error.message : 'Backend confirmation is pending.'); }
        finally { setBusy(false); }
      }, modal: { ondismiss: () => { setMessage('Payment was not completed. No success is assumed. Check the order status before retrying.'); setBusy(false); refreshed(); } } });
      checkout.on('payment.failed', async failure => {
        const paymentId = failure.error?.metadata?.payment_id;
        try { if (paymentId) await customerApi('payment-failed', { razorpay_order_id: data.orderId, razorpay_payment_id: paymentId }); }
        catch { /* Order history remains the source of truth if provider verification is interrupted. */ }
        setMessage('Payment was not confirmed. Check the order status before retrying this order.'); setBusy(false); refreshed();
      });
      checkout.open();
    } catch (error) { setMessage(error instanceof Error ? error.message : 'Payment could not be started.'); setBusy(false); }
  }}>{busy ? 'Please wait…' : 'Pay in Razorpay test mode'}</button>{message && <p role="status">{message}</p>}</div>;
}
