import 'server-only';
import { cache } from 'react';
import { z } from 'zod';
import { apiOrigin } from './config';
import { bannerSchema, brandSchema, categorySchema, envelope, productSchema, returnPolicySchema, topSellerSchema } from './contracts';
import { requestCatalog } from './transport';

export const laravelGet = <T>(path: string, schema: z.ZodType<T>, fresh = false) => requestCatalog(apiOrigin, path, schema, fresh);

export const getCategories = cache(() => laravelGet('api-categories', envelope(categorySchema)));
export const getBrands = cache(() => laravelGet('brands', envelope(brandSchema)));
export const getBanners = cache(() => laravelGet('get-banners', z.array(bannerSchema)));
export const getFeatured = cache(() => laravelGet('featured-prods', envelope(productSchema)));
export const getTopSellers = cache(() => laravelGet('top-selling', envelope(topSellerSchema)));
export const getReturnPolicies = cache(() => laravelGet('return-policies', envelope(returnPolicySchema)));
export const getProducts = cache((keyword = '', category = '', fresh = false) => {
  const query = new URLSearchParams();
  if (keyword) query.set('keyword', keyword);
  if (category) query.set('category_name', category);
  return laravelGet(`api-products${query.size ? `?${query}` : ''}`, envelope(productSchema), fresh);
});
export const getProduct = cache((id: string) => laravelGet(`api-products/${encodeURIComponent(id)}`, z.array(productSchema), true));
