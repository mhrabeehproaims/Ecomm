import type { MetadataRoute } from 'next';
import { indexable, siteOrigin } from '@/lib/config';
export default function robots(): MetadataRoute.Robots { return { rules: indexable ? { userAgent: '*', allow: '/', disallow: ['/api/', '/account', '/cart', '/wishlist', '/products/'] } : { userAgent: '*', disallow: '/' }, ...(indexable ? { sitemap: `${siteOrigin}/sitemap.xml` } : {}) }; }
