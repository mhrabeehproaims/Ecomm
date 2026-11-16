import 'server-only';
import { z } from 'zod';
import { productSchema } from './contracts';
import { customerSchema } from './customer-contracts';
import { customerRequest, CustomerError } from './customer-server';
import { parseCustomerCart } from './customer-cart';
import { shoppingKey } from './shopping-key';
import type { ShoppingSnapshot } from './shopping-contracts';

export async function customerShopping(token: string): Promise<ShoppingSnapshot> {
  const body = await customerRequest('shopping-state', token);
  const customer = customerSchema.safeParse(body.customer);
  const products = z.array(productSchema).safeParse(body.wishlist);
  if (!customer.success || !products.success) throw new CustomerError(502, 'Your shopping information could not be loaded. Please try again.');
  if (customer.data.status !== 1) throw new CustomerError(403, 'This account is inactive. Please contact support.');
  const items = parseCustomerCart(body.cart);
  return { customer: customer.data, items, products: products.data, summary: { signedIn: true, cartCount: items.reduce((sum, line) => sum + line.quantity, 0), wishlist: products.data.map(shoppingKey) } };
}
