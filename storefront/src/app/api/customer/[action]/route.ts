import { NextRequest, NextResponse } from 'next/server';
import { z } from 'zod';
import { apiOrigin, siteOrigin } from '@/lib/config';
import { customerShopping } from '@/lib/customer-shopping';
import { customerOriginAllowed } from '@/lib/customer-origin';
import { getProduct, getProducts } from '@/lib/api';
import { activeRecommendations } from '@/lib/catalog';
import { productSchema } from '@/lib/contracts';
import { addressInput, customerSchema, loginInput, productId, profileInput, quantity, registerInput, quoteSchema, returnEligibilitySchema, trackingSchema } from '@/lib/customer-contracts';
import { customerCart, estimatedSubtotal } from '@/lib/customer-cart';
import { currentCustomer, customerOrders, customerRequest, CustomerError, isolatedTransactionsEnabled, sessionSecret, tokenFromCookie } from '@/lib/customer-server';
import { openSession, sealSession, sessionCookie, sessionLifetime } from '@/lib/session-crypto';
import { deliveryStateCode } from '@/lib/delivery-states';

type Context = { params: Promise<{ action: string }> };
const privateHeaders = { 'Cache-Control': 'private, no-store', Vary: 'Cookie' };
const json = (body: unknown, status = 200) => NextResponse.json(body, { status, headers: privateHeaders });
const id = z.number().int().positive();
const attempts = new Map<string, { count: number; until: number }>();
function throttle(mobile: string) {
  const now = Date.now(), previous = attempts.get(mobile);
  if (previous && previous.until > now && previous.count >= 10) throw new CustomerError(429, 'Too many sign-in attempts. Please wait a minute.');
  if (attempts.size > 1000) attempts.clear();
  attempts.set(mobile, previous && previous.until > now ? { ...previous, count: previous.count + 1 } : { count: 1, until: now + 60_000 });
}
function failure(error: unknown) {
  if (error instanceof z.ZodError) return json({ message: 'Please check the form.', errors: z.flattenError(error).fieldErrors }, 422);
  if (error instanceof CustomerError) return json({ message: error.message, errors: error.errors }, error.status);
  return json({ message: 'Something interrupted this request. Please try again.' }, 500);
}
async function auth() {
  const token = await tokenFromCookie();
  if (!token) throw new CustomerError(401, 'Please sign in to continue.');
  return { token, customer: await currentCustomer(token) };
}
async function sandbox() { if (!await isolatedTransactionsEnabled()) throw new CustomerError(503, 'Test checkout and payments are unavailable. Production activation is disabled.'); }

export async function GET(request: NextRequest, context: Context) {
  try {
    const { action } = await context.params;
    if (action === 'shopping-state') {
      const token = await tokenFromCookie();
      if (!token) throw new CustomerError(401, 'Please sign in to continue.');
      return json(await customerShopping(token));
    }
    if (action === 'shopping-summary') {
      const token = await tokenFromCookie();
      if (!token) return json({ signedIn: false, cartCount: 0, wishlist: [] });
      try {
        return json((await customerShopping(token)).summary);
      } catch (error) {
        if (error instanceof CustomerError && [401, 403].includes(error.status)) return json({ signedIn: false, cartCount: 0, wishlist: [] });
        throw error;
      }
    }
    if (action === 'capabilities') {
      const enabled = await isolatedTransactionsEnabled();
      const capabilities = enabled ? await customerRequest('checkout/capabilities', null) : {};
      return json({ sandboxCheckout: enabled, paymentTestConfigured: enabled && capabilities.paymentTestConfigured === true, codEnabled: enabled && capabilities.codEnabled === true });
    }
    const { token, customer } = await auth();
    if (action === 'document') {
      const orderId = id.parse(Number(request.nextUrl.searchParams.get('order_id')));
      const kind = z.enum(['invoice', 'packing-slip']).parse(request.nextUrl.searchParams.get('kind') || 'invoice');
      const document = await fetch(`${apiOrigin}/api/orders/${orderId}/documents/${kind}`, { headers: { Authorization: `Bearer ${token}`, Accept: 'application/pdf' }, cache: 'no-store', redirect: 'error', signal: AbortSignal.timeout(12_000) });
      if (!document.ok || !document.headers.get('content-type')?.startsWith('application/pdf')) throw new CustomerError(404, 'Document not available.');
      return new NextResponse(await document.arrayBuffer(), { headers: { ...privateHeaders, 'Content-Type': 'application/pdf', 'Content-Disposition': `attachment; filename="${kind}-${orderId}.pdf"`, 'X-Content-Type-Options': 'nosniff' } });
    }
    if (action === 'session') return json({ customer });
    if (action === 'cart') return json({ items: await customerCart(token) });
    if (action === 'wishlist') {
      const result = await customerRequest('wishlist', token);
      return json({ products: z.array(productSchema).parse(result.data) });
    }
    if (action === 'orders') return json({ orders: await customerOrders(token) });
    if (action === 'order-support') {
      const orderId = id.parse(Number(request.nextUrl.searchParams.get('order_id')));
      const [returns, tracking] = await Promise.all([customerRequest(`orders/${orderId}/return-eligibility`, token), customerRequest(`orders/${orderId}/tracking`, token)]);
      return json({ eligibility: returnEligibilitySchema.parse(returns.eligibility), tracking: trackingSchema.parse(tracking.tracking) });
    }
    return json({ message: 'Unknown customer operation.' }, 404);
  } catch (error) { return failure(error); }
}

export async function POST(request: NextRequest, context: Context) {
  try {
    if (!customerOriginAllowed(request.headers.get('origin'), siteOrigin, process.env.NODE_ENV === 'development') || !request.headers.get('content-type')?.startsWith('application/json')) return json({ message: 'This request could not be verified.' }, 403);
    const text = await request.text();
    if (text.length > 16_384) return json({ message: 'The request is too large.' }, 413);
    let input: unknown;
    try { input = JSON.parse(text); } catch { return json({ message: 'Invalid request body.' }, 400); }
    const { action } = await context.params;
    if (action === 'register') {
      sessionSecret();
      const data = registerInput.parse(input); throttle(data.phone);
      await customerRequest('create-customer', null, 'POST', data);
      return json({ message: 'Account created. You can now sign in.' }, 201);
    }
    if (action === 'login') {
      const secret = sessionSecret();
      const data = loginInput.parse(input); throttle(data.mobile);
      const result = await customerRequest('login', null, 'POST', data);
      if (typeof result.token !== 'string') throw new CustomerError(502, 'The shop did not establish a session.');
      const customer = customerSchema.parse(result.user);
      if (customer.status !== 1) { await customerRequest('logout', result.token, 'POST', {}); throw new CustomerError(403, 'This account is inactive.'); }
      const response = json({ customer });
      response.cookies.set(sessionCookie, sealSession(result.token, secret), { httpOnly: true, secure: new URL(siteOrigin).protocol === 'https:', sameSite: 'lax', path: '/', maxAge: sessionLifetime });
      return response;
    }
    if (action === 'logout') {
      const token = await tokenFromCookie();
      let failed = false;
      if (token) { try { await customerRequest('logout', token, 'POST', {}); } catch { failed = true; } }
      const response = json({ message: failed ? 'Signed out on this device. Server token revocation could not be confirmed.' : 'You have signed out.' });
      response.cookies.set(sessionCookie, '', { httpOnly: true, secure: new URL(siteOrigin).protocol === 'https:', sameSite: 'lax', path: '/', maxAge: 0 });
      response.cookies.set('vanthara_payment', '', { httpOnly: true, path: '/', maxAge: 0 });
      return response;
    }
    const { token, customer } = await auth();
    if (action === 'profile') { await customerRequest('update-customer', token, 'POST', profileInput.parse(input)); return json({ customer: await currentCustomer(token), message: 'Profile saved.' }); }
    if (action === 'address-save') {
      const data = addressInput.extend({ address_id: id.optional() }).parse(input);
      if (data.address_id && !customer.delivery_address.some(a => a.address_id === data.address_id)) throw new CustomerError(404, 'Address not found.');
      const { address_id, ...address } = data;
      await customerRequest(address_id ? `delivery-address/${address_id}` : 'delivery-address', token, address_id ? 'PUT' : 'POST', address);
      return json({ customer: await currentCustomer(token), message: 'Address saved.' });
    }
    if (action === 'address-delete') {
      const { address_id } = z.object({ address_id: id }).parse(input);
      if (!customer.delivery_address.some(a => a.address_id === address_id)) throw new CustomerError(404, 'Address not found.');
      await customerRequest(`delivery-address/${address_id}`, token, 'DELETE');
      return json({ customer: await currentCustomer(token), message: 'Address removed.' });
    }
    if (action === 'wishlist') {
      const data = z.object({ product_id: productId }).parse(input);
      const result = await customerRequest('wishlist', token, 'POST', data);
      return json({ message: result.message });
    }
    if (action === 'cart-add') {
      const data = z.object({ product_id: productId, quantity }).parse(input);
      const [detail, catalog, cart] = await Promise.all([getProduct(data.product_id), getProducts('', '', true), customerCart(token)]);
      if (!detail.ok || !catalog.ok) throw new CustomerError(503, 'Product availability could not be checked.');
      const p = detail.data[0];
      if (!p || !activeRecommendations([p], catalog.data.data).length) throw new CustomerError(404, 'This product is not available.');
      let existing = 0;
      for (const line of cart.filter(item => item.name === p.product_name)) {
        const check = await getProduct(line.productId);
        if (!check.ok || !check.data[0]) throw new CustomerError(503, 'Your bag could not be checked.');
        if (check.data[0].category === p.category) existing += line.quantity;
      }
      if (existing + data.quantity > Number(p.stock) || existing + data.quantity > 99) throw new CustomerError(422, 'The requested quantity is not available.');
      await customerRequest('cart', token, 'POST', data);
      return json({ message: 'Added to your bag.', items: await customerCart(token) });
    }
    if (action === 'cart-update' || action === 'cart-remove') {
      const data = z.object({ item_id: id, quantity: action === 'cart-update' ? quantity : quantity.optional() }).parse(input);
      const line = (await customerCart(token)).find(item => item.id === data.item_id);
      if (!line) throw new CustomerError(404, 'Bag item not found.');
      if (action === 'cart-update' && data.quantity! > line.stock) throw new CustomerError(422, 'The requested quantity is not available.');
      await customerRequest(action === 'cart-update' ? 'cart/update-count' : 'cart/remove-item', token, 'POST', { product_id: line.productId, ...(action === 'cart-update' ? { count: data.quantity } : {}) });
      return json({ items: await customerCart(token), message: 'Bag updated.' });
    }
    if (action === 'coupon') {
      const { coupon_code } = z.object({ coupon_code: z.string().trim().min(1).max(100) }).parse(input);
      const result = await customerRequest('coupon/validate', token, 'POST', { coupon_code });
      return json({ message: 'Coupon is valid. Review the full delivery quote at checkout.', coupon_code, discount_minor: z.number().int().nonnegative().parse(result.discount_minor) });
    }
    if (action === 'quote') {
      await sandbox();
      const data = z.object({ address_id: id, coupon_code: z.string().trim().max(100).default(''), payment_method: z.enum(['cod', 'online']) }).parse(input);
      const result = await customerRequest('checkout/quote', token, 'POST', data);
      return json({ quote: quoteSchema.extend({ quote_id: z.uuid(), expires_at: z.string() }).parse(result.quote) });
    }
    if (action === 'checkout') {
      await sandbox();
      const data = z.object({ quote_id: z.uuid(), idempotency_key: z.uuid() }).parse(input);
      const result = await customerRequest('checkout/orders', token, 'POST', data);
      const order_id = z.object({ order_id: id }).parse(result.order).order_id;
      const order = (await customerOrders(token)).find(o => o.order_id === order_id);
      if (!order) throw new CustomerError(502, 'The order response could not be confirmed. Check your order history before retrying.');
      return json({ order, message: 'Test order recorded. Online payment awaits verified capture.' }, 201);
    }
    if (action === 'order-cancel') {
      await sandbox();
      const { order_id } = z.object({ order_id: id }).parse(input);
      await customerRequest(`orders/${order_id}/cancel`, token, 'POST', {});
      return json({ message: 'Reservation cancelled. Your bag is saved.' });
    }
    if (action === 'return') {
      const data = z.object({ order_id: id, order_item_id: id, quantity, return_type: z.enum(['refund', 'replacement']), return_reason: z.string().trim().min(5).max(1000), product_condition: z.enum(['damaged', 'unused', 'opened', 'other']), request_key: z.uuid() }).parse(input);
      const order = (await customerOrders(token)).find(o => o.order_id === data.order_id);
      const item = order?.items.find(i => i.order_item_id === data.order_item_id);
      if (!order || !item) throw new CustomerError(404, 'Order item not found.');
      await customerRequest('sales-return/save', token, 'POST', { order_id: order.order_id, items: [{ product_id: item.product_id, order_item_id: item.order_item_id, quantity: data.quantity }], return_type: data.return_type, return_reason: data.return_reason, product_condition: data.product_condition, remarks: '', request_key: data.request_key });
      return json({ message: 'Return request submitted for review. A refund or replacement is not yet approved.' });
    }
    if (action === 'payment-start') {
      await sandbox();
      const { order_id } = z.object({ order_id: id }).parse(input);
      const order = (await customerOrders(token)).find(o => o.order_id === order_id);
      if (!order || order.checkout_state !== 'reserved' || ['paid', 'refunded', 'partially_refunded'].includes(order.payment_status || '')) throw new CustomerError(422, 'This order is not payable.');
      const result = await customerRequest('razorpay/payment', token, 'POST', { order_id });
      const key = z.string().regex(/^rzp_test_/).parse(result.razorpayKey);
      const providerId = z.string().min(1).parse(result.razorpayOrderId);
      if (process.env.STOREFRONT_RAZORPAY_TEST_KEY && process.env.STOREFRONT_RAZORPAY_TEST_KEY !== key) throw new CustomerError(502, 'Payment configuration mismatch.');
      const amount = Math.round(order.total_amount * 100);
      if (result.amount !== undefined && Number(result.amount) !== amount) throw new CustomerError(502, 'Payment amount mismatch.');
      const response = json({ key, orderId: providerId, amount, currency: 'INR', customer: { name: customer.first_name, email: customer.email, contact: customer.phone } });
      response.cookies.set('vanthara_payment', sealSession(JSON.stringify({ order_id, providerId, customer: customer.customer_id }), sessionSecret()), { httpOnly: true, secure: new URL(siteOrigin).protocol === 'https:', sameSite: 'lax', path: '/', maxAge: 900 });
      return response;
    }
    if (action === 'payment-confirm' || action === 'payment-failed') {
      await sandbox();
      const data = z.object({ razorpay_payment_id: z.string().min(1).max(100), razorpay_order_id: z.string().min(1).max(100), razorpay_signature: action === 'payment-confirm' ? z.string().regex(/^[a-f0-9]{64}$/) : z.string().optional() }).parse(input);
      const contextValue = openSession(request.cookies.get('vanthara_payment')?.value, sessionSecret());
      if (!contextValue) throw new CustomerError(403, 'Payment session expired.');
      const binding = z.object({ order_id: id, providerId: z.string(), customer: id }).parse(JSON.parse(contextValue));
      if (binding.customer !== customer.customer_id || binding.providerId !== data.razorpay_order_id) throw new CustomerError(403, 'Payment does not belong to this session.');
      const verified = await customerRequest(action === 'payment-confirm' ? 'razorpay/success' : 'razorpay/failed', token, 'POST', data);
      if (verified.paid !== true) return json({ paid: false, message: verified.message || 'Payment is pending. Check your order status before retrying.' }, 202);
      const order = (await customerOrders(token)).find(o => o.order_id === binding.order_id);
      if (!order || !['paid', 'refunded', 'partially_refunded'].includes(order.payment_status || '')) throw new CustomerError(409, 'Payment has not been confirmed by the backend.');
      const response = json({ order, paid: true, payment_status: order.payment_status, requires_review: verified.requires_review === true });
      response.cookies.set('vanthara_payment', '', { httpOnly: true, secure: new URL(siteOrigin).protocol === 'https:', sameSite: 'lax', path: '/', maxAge: 0 });
      return response;
    }
    return json({ message: 'Unknown customer operation.' }, 404);
  } catch (error) { return failure(error); }
}
