import type { Product } from './contracts';

export type ApiResult<T> = { ok: true; data: T } | { ok: false; kind: 'unavailable' | 'invalid' | 'not-found' };

export function numberOrNull(value: unknown): number | null {
  if ((typeof value !== 'string' && typeof value !== 'number') || (typeof value === 'string' && !value.trim())) return null;
  const n = Number(value);
  return Number.isFinite(n) && n >= 0 ? n : null;
}

export function productPrice(product: Product): number | null {
  const offer = numberOrNull(product.offer_price);
  if (offer !== null) return offer;
  const field = product.primary_price;
  const base = field === 'retail' ? product.retail : field === 'wholesale' ? product.wholesale : field === 'special_price' ? product.special_price : product.mrp;
  // Missing selected price is unknown, never invent a sale price from a different field.
  return numberOrNull(base);
}

export function hasOffer(product: Product): boolean {
  return numberOrNull(product.offer_price) !== null;
}

export function money(value: number): string {
  return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 2 }).format(value);
}
export function plainText(value?: string | null): string {
  return (value || '').replace(/<[^>]*>/g, ' ').replace(/&nbsp;/gi, ' ').replace(/&amp;/gi, '&').replace(/\s+/g, ' ').trim();
}
export function slug(value: string): string {
  return value.normalize('NFKD').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || 'product';
}
export function productHref(product: Pick<Product, 'product_name' | 'product_id'>): string {
  return `/products/${slug(product.product_name)}?${new URLSearchParams({ id: product.product_id })}`;
}
export function catalogHref(params: Record<string, string> = {}): string {
  const query = new URLSearchParams(Object.entries(params).filter(([, value]) => value));
  return `/products${query.size ? `?${query}` : ''}`;
}

// Encryption is randomized. Match public recommendation records to the active list
// by category/name, and omit ambiguous matches. Always return the active resource ID.
export function activeRecommendations(records: { product_name: string; category?: string | null }[], active: Product[]): Product[] {
  const seen = new Set<string>();
  return records.flatMap(record => {
    const matches = active.filter(p => p.product_name === record.product_name && (!record.category || p.category === record.category));
    if (matches.length !== 1) return [];
    const key = `${matches[0].category}\u0000${matches[0].product_name}`;
    if (seen.has(key)) return [];
    seen.add(key);
    return [matches[0]];
  });
}

export function filterProducts(products: Product[], filters: { brand?: string; subcategory?: string; stock?: string; max?: string; sort?: string; collection?: string }): Product[] {
  const max = numberOrNull(filters.max);
  const result = products.filter(product => {
    const price = productPrice(product);
    return (!filters.brand || product.brand === filters.brand)
      && (!filters.subcategory || product.sub_category === filters.subcategory)
      && (filters.stock !== 'in' || (numberOrNull(product.stock) ?? 0) > 0)
      && (max === null || (price !== null && price <= max))
      && (filters.collection !== 'offers' || hasOffer(product));
  });
  if (filters.sort === 'price-asc' || filters.sort === 'price-desc') {
    const direction = filters.sort === 'price-asc' ? 1 : -1;
    result.sort((a, b) => {
      const pa = productPrice(a), pb = productPrice(b);
      if (pa === null) return pb === null ? 0 : 1;
      if (pb === null) return -1;
      return (pa - pb) * direction;
    });
  } else if (filters.sort === 'name') result.sort((a, b) => a.product_name.localeCompare(b.product_name));
  return result;
}

export function safeBannerLink(value?: string | null): string {
  // Admin links may target a different legacy storefront. Avoid unverified redirects.
  if (!value || !/^\/(products|categories)(\/|\?|$)/.test(value) || value.includes('\\')) return '/products';
  return value;
}
export function activeBanners<T extends { from_date?: string | null; to_date?: string | null; sort_order?: unknown }>(banners: T[], now = new Date()): T[] {
  const date = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Kolkata', year: 'numeric', month: '2-digit', day: '2-digit' }).format(now);
  return banners.filter(b => (!b.from_date || b.from_date.slice(0, 10) <= date) && (!b.to_date || b.to_date.slice(0, 10) >= date))
    .sort((a, b) => (Number(a.sort_order) || 0) - (Number(b.sort_order) || 0));
}
