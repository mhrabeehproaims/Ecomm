import Link from 'next/link';
import { notFound } from 'next/navigation';
import { ArrowLeft, ArrowRight } from 'lucide-react';
import { getCategories, getProducts } from '@/lib/api';
import { catalogHref, filterProducts } from '@/lib/catalog';
import { CatalogState, ProductGrid } from './catalog';
import { CatalogFilters } from './catalog-filters';

export type CatalogQuery = Record<string, string | string[] | undefined>;
export function queryText(params: CatalogQuery, key: string) { return (typeof params[key] === 'string' ? params[key] : '').trim().slice(0, 120); }

export async function CatalogView({ params }: { params: CatalogQuery }) {
  const category = queryText(params, 'category'), keyword = queryText(params, 'keyword');
  const categoriesRequest = getCategories();
  const productsRequest = category ? null : getProducts(keyword);
  const categories = await categoriesRequest;
  if (category && categories.ok && !categories.data.data.some(c => c.name === category)) notFound();
  const result = category && !categories.ok ? { ok: false as const, kind: 'unavailable' as const } : await (productsRequest || getProducts(keyword, category));
  const products = result.ok ? result.data.data : [];
  const filters = { size: queryText(params, 'size'), min: queryText(params, 'min'), brand: queryText(params, 'brand'), subcategory: queryText(params, 'subcategory'), stock: queryText(params, 'stock'), max: queryText(params, 'max'), sort: queryText(params, 'sort'), collection: queryText(params, 'collection') };
  const filtered = filterProducts(products, filters);
  const brandNames = [...new Set(products.map(p => p.brand).filter((s): s is string => !!s))].sort();
  const subcategoryNames = [...new Set(products.map(p => p.sub_category).filter((s): s is string => !!s))].sort();
  const pages = Math.max(1, Math.ceil(filtered.length / 12));
  const page = Math.min(pages, Math.max(1, Math.floor(Number(queryText(params, 'page')) || 1)));
  const pageProducts = filtered.slice((page - 1) * 12, page * 12);
  const nextPage = (n: number) => catalogHref({ category, keyword, ...filters, page: String(n) });
  return <div className="container catalog-page">
    <nav className="breadcrumbs" aria-label="Breadcrumb"><Link href="/">Home</Link><span aria-hidden="true">/</span><span>Collection</span>{category && <><span aria-hidden="true">/</span><span>{category}</span></>}</nav>
    <div className="page-heading"><p className="eyebrow">The Vanthara wardrobe</p><h1>{keyword ? `Results for “${keyword}”` : category || (filters.collection === 'offers' ? 'A little more value.' : 'All pieces.')}</h1><p>Take a closer look. There’s a little more to discover.</p></div>
    <CatalogFilters current={{ category, keyword, ...filters }} categories={categories.ok ? categories.data.data.map(c => c.name) : []} brands={brandNames} subcategories={subcategoryNames} sizes={[...new Set(products.map(p => p.size).filter((s): s is string => !!s))].sort()} count={result.ok ? filtered.length : null} />
    {!result.ok ? <CatalogState error /> : pageProducts.length ? <ProductGrid products={pageProducts} eagerFirst /> : <CatalogState title={products.length ? 'Nothing here just yet.' : undefined} description={products.length ? 'Try another search or clear your filters to explore more of the collection.' : undefined} />}
    {pages > 1 && <nav className="pagination" aria-label="Catalog pages">{page > 1 ? <Link href={nextPage(page - 1)}><ArrowLeft size={16} />Previous</Link> : <span />}<span aria-current="page">Page {page} of {pages}</span>{page < pages ? <Link href={nextPage(page + 1)}>Next<ArrowRight size={16} /></Link> : <span />}</nav>}
  </div>;
}
