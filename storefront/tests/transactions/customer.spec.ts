import { test, expect, type Page } from '@playwright/test';
import fs from 'node:fs/promises';
import { execFileSync, spawn } from 'node:child_process';
import type { Order } from '../../src/lib/customer-contracts';
const origin = 'http://127.0.0.1:3140';
const phone = `91${String(Date.now()).slice(-8)}`;
const password = 'IsolatedCustomerPassword123!';
const output = 'artifacts/transactions/screenshots';
const scriptErrors = new WeakMap<Page, string[]>();

test.beforeAll(async ({ request }) => {
  const identity = await request.get('http://127.0.0.1:8138/__storefront-test/identity');
  expect((await identity.json()).environment).toBe('isolated-storefront-test');
  await fs.mkdir(output, { recursive: true });
});
test.beforeEach(async ({ page }) => { page.setDefaultTimeout(15_000); });
test.afterEach(async ({ page }) => {
  expect(scriptErrors.get(page)).toEqual([]);
});
test.beforeEach(async ({ page }) => {
  const errors: string[] = [];
  scriptErrors.set(page, errors);
  page.on('pageerror', error => errors.push(error.message));
});

test('production order and payment gates reject writes without changing order history', async ({ playwright }) => {
  const productionOrigin = 'http://127.0.0.1:3141';
  const server = spawn(process.execPath, ['node_modules/next/dist/bin/next', 'start', '--hostname', '127.0.0.1', '--port', '3141'], {
    windowsHide: true, stdio: 'ignore', env: { ...process.env, LARAVEL_API_ORIGIN: 'http://127.0.0.1:8138', STOREFRONT_URL: productionOrigin, STOREFRONT_SESSION_SECRET: 'isolated-test-session-secret-at-least-32-characters', STOREFRONT_SANDBOX_TRANSACTIONS: 'false' },
  });
  const api = await playwright.request.newContext({ baseURL: productionOrigin, extraHTTPHeaders: { Origin: productionOrigin } });
  try {
    await expect.poll(async () => { try { return (await api.get('/api/customer/capabilities')).status(); } catch { return 0; } }).toBe(200);
    expect((await (await api.get('/api/customer/capabilities')).json()).sandboxCheckout).toBe(false);
    expect((await api.post('/api/customer/login', { data: { mobile: '9000000011', password: 'FixturePassword123!' } })).status()).toBe(200);
    // Laravel emits newly randomized product ciphertext on every history read.
    const stable = (body: { orders: Order[] }) => body.orders.map(order => ({ ...order, items: order.items.map(({ product_id: _ciphertext, ...item }) => item) }));
    const before = stable(await (await api.get('/api/customer/orders')).json());
    const checkout = await api.post('/api/customer/checkout', { data: { address_id: 1, payment_method: 'cod' } });
    expect(checkout.status()).toBe(503);
    expect((await api.post('/api/customer/payment-start', { data: { order_id: 1 } })).status()).toBe(503);
    expect(stable(await (await api.get('/api/customer/orders')).json())).toEqual(before);
  } finally { await api.dispose(); server.kill(); }
});
async function login(page: Page, mobile: string, pass: string) {
  await page.goto('/login');
  await page.getByLabel('Mobile number', { exact: true }).fill(mobile);
  await page.getByLabel('Password', { exact: true }).fill(pass);
  await page.getByRole('button', { name: 'Sign in', exact: true }).click();
  await expect(page).toHaveURL('/account');
  await expect(page.getByRole('heading', { name: 'Your profile' })).toBeVisible();
}
async function screenshot(page: Page, name: string) {
  await page.evaluate(() => { (document.activeElement as HTMLElement | null)?.blur(); window.scrollTo(0, 0); });
  await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
  await page.screenshot({ path: `${output}/${name}.png`, fullPage: true });
  await page.setViewportSize({ width: 1440, height: 1000 });
  await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
  await page.screenshot({ path: `${output}/${name.replace('-393', '-1440')}.png`, fullPage: true });
  await page.setViewportSize({ width: 393, height: 852 });
}

test('isolated registration → profile → addresses → wishlist → cart → coupon → order → logout', async ({ page, context }) => {
  console.log('Journey: registration and login');
  let paymentRequests = 0;
  page.on('request', request => { if (request.url().includes('razorpay.com')) paymentRequests++; });
  await page.goto('/register');
  await page.getByLabel('First name', { exact: true }).fill('Journey');
  await page.getByLabel('Last name', { exact: true }).fill('Customer');
  await page.getByLabel('Mobile number', { exact: true }).fill(phone);
  await page.getByLabel('Password', { exact: true }).fill(password);
  await page.getByRole('button', { name: 'Create account', exact: true }).click();
  await expect(page).toHaveURL('/login?registered=1');
  await page.getByLabel('Mobile number', { exact: true }).fill(phone);
  await page.getByLabel('Password', { exact: true }).fill('incorrect');
  await page.getByRole('button', { name: 'Sign in', exact: true }).click();
  await expect(page.locator('main .form-error')).toContainText('Invalid credentials');
  await login(page, phone, password);
  console.log('Journey: profile and addresses');
  const cookie = (await context.cookies()).find(c => c.name === 'vanthara_session');
  expect(cookie?.httpOnly).toBe(true);
  expect(cookie?.sameSite).toBe('Lax');
  expect(await page.evaluate(() => document.cookie)).not.toContain('vanthara_session');
  await page.getByLabel('First name', { exact: true }).fill('Updated');
  await page.getByRole('button', { name: 'Save profile' }).click();
  await expect(page.getByRole('status')).toContainText('Profile saved');
  await page.getByLabel('Address line 1', { exact: true }).fill('1 Test Lane');
  await page.getByLabel('City', { exact: true }).fill('Kozhikode');
  await page.getByLabel('State', { exact: true }).selectOption('kerala (KL)');
  await page.getByLabel('Postal code', { exact: true }).fill('673001');
  await page.getByRole('button', { name: 'Save address', exact: true }).click();
  await expect(page.locator('.address-card')).toContainText('1 Test Lane');
  await page.getByRole('button', { name: 'Edit address', exact: true }).click();
  await page.getByLabel('Address line 1', { exact: true }).fill('2 Test Lane');
  await page.getByRole('button', { name: 'Save address', exact: true }).click();
  await expect(page.locator('.address-card')).toContainText('2 Test Lane');
  await page.mouse.move(0, 0);
  await expect(page.getByRole('button', { name: 'Save address', exact: true })).toHaveCSS('background-color', 'rgb(124, 0, 70)');
  await screenshot(page, 'account-addresses-393');
  await page.getByLabel('Address line 1', { exact: true }).fill('Temporary test address');
  await page.getByLabel('City', { exact: true }).fill('Kozhikode');
  await page.getByLabel('State', { exact: true }).selectOption('kerala (KL)');
  await page.getByLabel('Postal code', { exact: true }).fill('673001');
  await page.getByRole('button', { name: 'Save address', exact: true }).click();
  const temporaryAddress = page.locator('.address-card').filter({ hasText: 'Temporary test address' });
  await expect(temporaryAddress).toBeVisible();
  await temporaryAddress.getByRole('button', { name: 'Remove address', exact: true }).click();
  await expect(temporaryAddress).toHaveCount(0);
  console.log('Journey: wishlist and bag');
  await page.goto('/products');
  await page.locator('.product-card').filter({ hasText: 'QA Cotton Bag' }).getByRole('link', { name: 'View QA Cotton Bag', exact: true }).click();
  await page.getByRole('button', { name: 'Save / unsave' }).click();
  await expect(page.locator('.purchase-message')).toContainText('wishlist');
  await page.goto('/wishlist');
  await expect(page.locator('.saved-row')).toContainText('QA Cotton Bag');
  await page.getByRole('button', { name: 'Add to bag', exact: true }).click();
  await expect(page.getByRole('status')).toContainText('Added to your bag');
  await page.getByRole('button', { name: 'Remove favourite' }).click();
  await expect(page.getByText('Your wishlist is empty.')).toBeVisible();
  await page.goto('/products');
  await page.locator('.product-card').filter({ hasText: 'QA Wooden Bowl' }).getByRole('link', { name: 'View QA Wooden Bowl', exact: true }).click();
  await page.getByRole('button', { name: 'Add to bag', exact: true }).click();
  await expect(page.locator('.purchase-message')).toContainText('Added to your bag');
  await page.goto('/cart');
  const extraItem = page.locator('.bag-item').filter({ hasText: 'QA Wooden Bowl' });
  await extraItem.getByRole('button', { name: 'Remove item', exact: true }).click();
  await expect(extraItem).toHaveCount(0);
  await page.getByRole('spinbutton', { name: 'Quantity for QA Cotton Bag' }).fill('2');
  await page.getByRole('button', { name: 'Update quantity', exact: true }).click();
  await expect(page.getByRole('status')).toContainText('Bag updated');
  await expect(page.locator('.bag-item > strong')).toContainText('500');
  await page.getByLabel('Coupon code', { exact: true }).fill('INVALID');
  await page.getByRole('button', { name: 'Validate coupon' }).click();
  await expect(page.locator('main .form-error')).toContainText('Invalid coupon');
  await page.getByLabel('Coupon code', { exact: true }).fill('TESTSAVE');
  await page.getByRole('button', { name: 'Validate coupon' }).click();
  await expect(page.getByRole('status').first()).toContainText('Coupon is valid');
  await expect(page.getByRole('link', { name: 'Continue to checkout' })).toHaveAttribute('href', '/checkout?coupon=TESTSAVE');
  await screenshot(page, 'cart-coupon-393');
  console.log('Journey: isolated checkout');
  await page.getByRole('link', { name: 'Continue to checkout' }).click();
  await expect(page.locator('.sandbox-notice')).toBeVisible();
  await expect(page.getByLabel('Coupon code', { exact: true })).toHaveValue('TESTSAVE');
  await page.getByLabel('Payment method', { exact: true }).selectOption('online');
  await expect(page.getByRole('button', { name: 'Place isolated test order' })).toBeDisabled();
  await page.getByLabel('Coupon code', { exact: true }).fill('INVALID');
  await page.getByRole('button', { name: 'Review final quote' }).click();
  await expect(page.locator('main .form-error')).toContainText('Invalid coupon');
  await expect(page.getByRole('button', { name: 'Place isolated test order' })).toBeDisabled();
  await page.getByLabel('Coupon code', { exact: true }).fill('TESTSAVE');
  await page.getByRole('button', { name: 'Review final quote' }).click();
  await expect(page.locator('.verified-quote')).toContainText('475');
  await expect(page.getByText('Your final quote is ready.', { exact: true })).toBeVisible();
  await screenshot(page, 'checkout-sandbox-393');
  await page.route('**/api/customer/checkout', route => route.continue({ postData: JSON.stringify({ ...route.request().postDataJSON(), total_amount: 1, actual_total_amount: 1, payment_status: 'paid' }) }));
  await page.getByRole('button', { name: 'Place isolated test order' }).click();
  await expect(page).toHaveURL(/\/orders\/\d+$/);
  await expect(page.getByText('Order total', { exact: true })).toBeVisible();
  await expect(page.locator('.order-totals')).toContainText('475');
  await expect(page.getByText('Payment: unpaid · online', { exact: true })).toBeVisible();
  await page.getByRole('button', { name: 'Pay in Razorpay test mode' }).click();
  await expect(page.locator('.payment-panel')).toContainText('No payment was initiated');
  expect(paymentRequests).toBe(0);
  await screenshot(page, 'order-payment-unavailable-393');
  const placedOrder = Number(new URL(page.url()).pathname.split('/').pop());
  await page.getByRole('navigation', { name: 'Customer navigation' }).getByRole('link', { name: 'Orders', exact: true }).click();
  await expect(page.getByRole('heading', { name: 'Order history', exact: true })).toBeVisible();
  await expect(page.getByRole('link', { name: `Order #${placedOrder}`, exact: true })).toBeVisible();
  await expect(page.getByRole('link', { name: 'Order #1', exact: true })).toHaveCount(0);
  await page.goto('/orders/1');
  await expect(page.getByText('This order was not found in your account.')).toBeVisible();
  await page.goto('/cart');
  // The bag is retained until capture is verified. Cancel the unpaid reservation safely.
  await expect(page.locator('.bag-item')).toContainText('QA Cotton Bag');
  await page.goto(`/orders/${placedOrder}`);
  await page.getByRole('button', { name: 'Cancel unpaid reservation' }).click();
  await expect(page.getByText('Reservation cancelled. Your bag is saved.', { exact: true })).toBeVisible();
  await expect(page.getByText('Cancel unpaid reservation', { exact: true })).toHaveCount(0);
  await page.goto('/cart');
  await page.getByRole('button', { name: 'Remove item', exact: true }).click();
  await expect(page.getByText('Your bag is empty.')).toBeVisible();
  await page.getByRole('button', { name: 'Sign out', exact: true }).click();
  await expect(page).toHaveURL('/login');
  await page.goto('/orders');
  await expect(page).toHaveURL(/\/login\?next=/);
});

test('Razorpay UI handles mocked pending capture, cancellation and failure without claiming payment', async ({ page }) => {
  const fixture = JSON.parse(execFileSync('php', ['../tests/Storefront/pending-fixture.php'], { encoding: 'utf8' })) as { phone: string; password: string; order_id: number; amount: number };
  await page.addInitScript(`window.Razorpay = class {
    constructor(options) { this.options = options; this.handlers = {}; }
    on(event, handler) { this.handlers[event] = handler; }
    open() { const phase = window.__testPaymentPhase = (window.__testPaymentPhase || 0) + 1;
      if (phase === 1) this.options.handler({razorpay_order_id:'order_UIFixture',razorpay_payment_id:'pay_UIFixture',razorpay_signature:'0'.repeat(64)});
      else if (phase === 2) this.options.modal.ondismiss();
      else this.handlers['payment.failed']({error:{metadata:{payment_id:'pay_UIFixture'}}}); }
  };`);
  let providerCalls = 0;
  page.on('request', request => { if (request.url().includes('razorpay.com')) providerCalls++; });
  await page.route('**/api/customer/payment-start', route => route.fulfill({ json: { key: 'rzp_test_ui_fixture', orderId: 'order_UIFixture', amount: fixture.amount, currency: 'INR', customer: { name: 'Test', email: null, contact: fixture.phone } } }));
  await page.route('**/api/customer/payment-confirm', route => route.fulfill({ status: 202, json: { paid: false, message: 'Capture pending. Check order status.' } }));
  await page.route('**/api/customer/payment-failed', route => route.fulfill({ status: 202, json: { paid: false } }));
  await login(page, fixture.phone, fixture.password); await page.goto(`/orders/${fixture.order_id}`);
  const pay = page.getByRole('button', { name: 'Pay in Razorpay test mode' });
  await pay.click(); await expect(page.locator('.payment-panel')).toContainText('Capture pending');
  await expect(page.getByText('Payment: unpaid · online', { exact: true })).toBeVisible();
  await screenshot(page, 'mocked-payment-pending-393');
  await pay.click(); await expect(page.locator('.payment-panel')).toContainText('Payment was not completed');
  await pay.click(); await expect(page.locator('.payment-panel')).toContainText('Payment was not confirmed');
  expect(providerCalls).toBe(0);
  await page.getByRole('button', { name: 'Cancel unpaid reservation' }).click();
  await expect(page.getByText('Reservation cancelled. Your bag is saved.', { exact: true })).toBeVisible();
});

test('Laravel login throttling persists across independent HTTP requests', async ({ request }) => {
  const mobile = `95${String(Date.now()).slice(-8)}`;
  for (let i = 0; i < 10; i++) expect((await request.post('http://127.0.0.1:8138/api/login', { data: { mobile, password: 'invalid' } })).status()).toBe(401);
  expect((await request.post('http://127.0.0.1:8138/api/login', { data: { mobile, password: 'invalid' } })).status()).toBe(429);
});

test('owned delivered order return request and request security', async ({ page, request }) => {
  const fixture = JSON.parse(execFileSync('php', ['../tests/Storefront/delivered-fixture.php'], { encoding: 'utf8' })) as { phone: string; password: string; order_id: number };
  console.log('Return journey: sign in');
  await login(page, fixture.phone, fixture.password);
  console.log('Return journey: inspect delivered order');
  await page.goto(`/orders/${fixture.order_id}`);
  await expect(page.getByRole('heading', { name: 'Request a return', exact: true })).toBeVisible();
  await page.getByLabel('Reason', { exact: true }).fill('Sample item is not suitable.');
  await page.getByLabel('Product condition', { exact: true }).selectOption('unused');
  await page.getByRole('button', { name: 'Submit return request' }).click();
  await expect(page.getByRole('status')).toContainText('Return request submitted for review');
  await expect(page.getByText('Return: Requested', { exact: true })).toBeVisible();
  await screenshot(page, 'return-request-393');
  const wrongOrigin = await request.post('/api/customer/profile', { headers: { Origin: 'https://untrusted.example' }, data: { first_name: 'Rejected', phone: '9000000011' } });
  expect(wrongOrigin.status()).toBe(403);
  const anonymous = await request.get('/api/customer/orders');
  expect(anonymous.status()).toBe(401);
  const forged = await page.request.post(`${origin}/api/customer/address-save`, { headers: { Origin: origin }, data: { address_id: 9999, phone: '9000000011', address_line1: 'No access', address_line2: '', city: 'Test', state: 'kerala (KL)', country: 'India', postal_code: '673001', email: '', is_default: false } });
  expect(forged.status()).toBe(404);
});
