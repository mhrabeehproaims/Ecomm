import 'server-only';

export const apiOrigin = new URL(process.env.LARAVEL_API_ORIGIN || 'http://127.0.0.1:8137').origin;
export const siteOrigin = new URL(process.env.STOREFRONT_URL || 'http://127.0.0.1:3000').origin;
export const indexable = process.env.STOREFRONT_INDEXABLE === 'true';
