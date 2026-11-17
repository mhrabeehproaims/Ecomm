import { z } from 'zod';

const amount = z.union([z.number(), z.string()]).nullable().optional();
const text = z.string().nullable().optional();
export const productSchema = z.object({
  product_id: z.string().min(1), product_name: z.string().min(1),
  description: text, health_benefit: text, category: text, category_id: text,
  sub_category: text, brand: text, brand_id: z.union([z.string(), z.number()]).nullable().optional(),
  stock: amount, size: text, weight: z.union([z.string(), z.number()]).nullable().optional(), unit: text, package_type: text,
  mrp: amount, retail: amount, wholesale: amount, special_price: amount, primary_price: text,
  primary_image: text, image_path: text, images: z.array(z.string()).optional(),
  Description_images: z.array(z.string()).nullable().optional(),
  offer_price: amount, offer: z.object({ title: text, description: text }).nullable().optional(),
});
export type Product = z.infer<typeof productSchema>;
export const categorySchema = z.object({ id: z.string(), name: z.string(), image: text, image_url: text });
export type Category = z.infer<typeof categorySchema>;
export const brandSchema = z.object({ brand_id: z.union([z.string(), z.number()]), brand_name: z.string(), image: text });
export type Brand = z.infer<typeof brandSchema>;
export const bannerSchema = z.object({
  banner_id: z.union([z.string(), z.number()]), banner_image: text, banner_title: text,
  banner_subtitle: text, button_text: text, button_link: text,
  button_position_type: text, preset_position: text,
  button_x_position: amount, button_y_position: amount,
  button_color: text, button_text_color: text, button_font_size: text, button_shape: text,
  from_date: text, to_date: text, sort_order: amount,
});
export type Banner = z.infer<typeof bannerSchema>;
export const returnPolicySchema = z.object({
  policy_id: z.union([z.string().min(1), z.number()]),
  title: z.string().min(1),
  return_days: z.union([z.number(), z.string().regex(/^\d+$/)]).transform(Number).pipe(z.number().int().nonnegative()),
  return_type: z.enum(['refund', 'exchange', 'refund_or_exchange']),
  conditions: text,
});
export type ReturnPolicy = z.infer<typeof returnPolicySchema>;
export const topSellerSchema = z.object({ product: z.object({
  product_name: z.string(), encrypted_product_id: z.string(), category_id: z.union([z.string(), z.number()]).optional(), catalog_category: text,
}).nullable() });

export const envelope = <T extends z.ZodType>(schema: T) => z.object({ status: z.literal('success'), data: z.array(schema) });
