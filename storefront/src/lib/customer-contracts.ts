import { z } from 'zod';

const optionalText = z.string().nullable().optional();
const phone = z.string().regex(/^[+]?\d{7,15}$/, 'Use 7–15 digits, with an optional + prefix.');
const email = z.union([z.literal(''), z.email()]).optional().transform(value => value || null);
export const loginInput = z.object({ mobile: phone, password: z.string().min(1).max(128) });
export const registerInput = z.object({ first_name: z.string().trim().min(1).max(100), last_name: z.string().trim().max(100).default(''), phone, email, password: z.string().min(8).max(128) });
export const profileInput = registerInput.omit({ password: true });
export const addressInput = z.object({
  phone, email, address_line1: z.string().trim().min(1).max(255), address_line2: z.string().trim().max(255).default(''),
  city: z.string().trim().min(1).max(100), state: z.string().trim().min(1).max(100),
  country: z.string().trim().min(1).max(100).default('India'), postal_code: z.string().trim().min(3).max(20), is_default: z.boolean().default(false),
});
const id = z.coerce.number().int().positive();
export const addressSchema = z.object({ address_id: id, phone: z.string(), email: optionalText, address_line1: z.string(), address_line2: optionalText, city: z.string(), state: z.string(), country: optionalText, postal_code: z.string(), is_default: z.union([z.boolean(), z.number()]).transform(Boolean) });
export const customerSchema = z.object({ customer_id: id, first_name: z.string(), last_name: optionalText, phone: z.string(), email: optionalText, status: z.coerce.number(), delivery_address: z.array(addressSchema).default([]) });
export type Customer = z.infer<typeof customerSchema>;
export type Address = z.infer<typeof addressSchema>;
export const productId = z.string().min(20).max(4096);
export const quantity = z.number().int().min(1).max(99);
export const quoteSchema = z.object({
  quote_id: z.uuid().optional(), expires_at: z.string().optional(), currency: z.literal('INR'),
  subtotal_minor: z.number().int().nonnegative(), discount_minor: z.number().int().nonnegative(), tax_minor: z.number().int().nonnegative(),
  shipping_minor: z.number().int().nonnegative(), total_minor: z.number().int().nonnegative(), tax_inclusive: z.literal(true),
  items: z.array(z.object({ name: z.string(), quantity: z.number().int().positive(), unit_minor: z.number().int().nonnegative(),
    line_minor: z.number().int().nonnegative(), discount_minor: z.number().int().nonnegative(), total_minor: z.number().int().nonnegative(), tax_minor: z.number().int().nonnegative() })),
});
export type CheckoutQuote = z.infer<typeof quoteSchema>;
export const returnEligibilitySchema = z.object({ order_id: id, delivered_at: z.string().nullable(), items: z.array(z.object({
  order_item_id: id, remaining_quantity: z.number().int().nonnegative(), allowed_types: z.array(z.enum(['refund', 'replacement'])), eligible: z.boolean(),
  deadlines: z.object({ refund: z.string().nullable(), replacement: z.string().nullable() }),
})) });
export type ReturnEligibility = z.infer<typeof returnEligibilitySchema>;
export const trackingSchema = z.object({ order_status: z.string(), tracking_number: z.string().nullable(), carrier_integration: z.null(), tracking_url: z.null(), events: z.array(z.object({ status: z.string(), changed_at: z.string() })) });
export type OrderTracking = z.infer<typeof trackingSchema>;
export const orderItemSchema = z.object({ order_item_id: id, product_id: productId, product_name: z.string(), quantity: z.coerce.number().int().positive(), price: z.coerce.number().nonnegative(), total: z.coerce.number().nonnegative(), is_returned: z.boolean(), return_status: optionalText, primary_image: optionalText });
export const orderSchema = z.object({ order_id: id, total_amount: z.coerce.number().nonnegative(), discount: z.coerce.number().nonnegative(), total_tax_amount: z.coerce.number().nullable(), status: z.string(), payment_method: optionalText, payment_status: optionalText, paid_at: optionalText, items: z.array(orderItemSchema),
  checkout_state: z.string().nullable().optional(), reservation_expires_at: z.string().nullable().optional(), quote: quoteSchema.nullable().optional(), address_snapshot: addressSchema.omit({ is_default: true }).nullable().optional() });
export type Order = z.infer<typeof orderSchema>;
export type CartItem = { id: number; productId: string; name: string; quantity: number; stock: number; price: number | null; image: string | null };
