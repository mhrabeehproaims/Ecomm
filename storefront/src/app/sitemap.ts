import type { MetadataRoute } from 'next';
import { getCategories } from '@/lib/api';
import { siteOrigin } from '@/lib/config';

export const dynamic = 'force-dynamic';
export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const categories = await getCategories();
  if (!categories.ok) throw new Error('Category sitemap is temporarily unavailable');
  return ['/', '/products', '/categories', ...categories.data.data.map(c => `/categories/${encodeURIComponent(c.name)}`)].map(path => ({ url: `${siteOrigin}${path}` }));
}
