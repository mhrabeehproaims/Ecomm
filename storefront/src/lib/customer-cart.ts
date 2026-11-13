import 'server-only';
import { z } from 'zod';
import { apiOrigin } from './config';
import { numberOrNull, productPrice } from './catalog';
import { mediaUrl } from './media';
import { customerRequest, CustomerError } from './customer-server';
import type { CartItem } from './customer-contracts';

const rawCart = z.array(z.object({ cart_id: z.coerce.number().int().positive(), quantity: z.coerce.number().int().positive(), product: z.object({ encrypted_product_id: z.string(), product_name: z.string(), stock: z.coerce.number(), primary_price: z.string().nullable().optional(), mrp: z.union([z.number(), z.string()]).nullable(), retail: z.union([z.number(), z.string()]).nullable().optional(), wholesale: z.union([z.number(), z.string()]).nullable().optional(), special_price: z.union([z.number(), z.string()]).nullable().optional(), offer_price: z.union([z.number(), z.string()]).nullable().optional(), primary_image: z.object({ image_path: z.string() }).nullable().optional() }).nullable() }));

export async function customerCart(token: string): Promise<CartItem[]> {
  const response = await customerRequest('cart', token);
  const parsed = rawCart.safeParse(response.data);
  if (!parsed.success) throw new CustomerError(502, 'Your bag could not be loaded.');
  if (parsed.data.some(item => !item.product)) throw new CustomerError(409, 'A product in your bag is no longer available. Please contact support.');
  return parsed.data.map(item => {
    const p = item.product!;
    return { id: item.cart_id, productId: p.encrypted_product_id, name: p.product_name, quantity: item.quantity, stock: p.stock, price: productPrice({ ...p, product_id: p.encrypted_product_id, primary_image: p.primary_image?.image_path }), image: mediaUrl(apiOrigin, p.primary_image?.image_path) };
  });
}
export function estimatedSubtotal(items: CartItem[]) {
  if (!items.length) throw new CustomerError(422, 'Your bag is empty.');
  if (items.some(item => item.price === null)) throw new CustomerError(409, 'A product price is unavailable. Please try again later.');
  return Math.round(items.reduce((sum, item) => sum + (numberOrNull(item.price) ?? 0) * item.quantity, 0) * 100) / 100;
}
