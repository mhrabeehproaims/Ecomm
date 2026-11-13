import Link from 'next/link';
import { notFound } from 'next/navigation';
import { SlidersHorizontal, ArrowLeft, ArrowRight } from 'lucide-react';
import { getCategories, getProducts } from '@/lib/api';
import { catalogHref, filterProducts } from '@/lib/catalog';
import { CatalogState, ProductGrid } from './catalog';

export type CatalogQuery = Record<string, string | string[] | undefined>;
export function queryText(params: CatalogQuery, key: string) { return (typeof params[key] === 'string' ? params[key] : '').trim().slice(0, 120); }

export async function CatalogView({ params }: { params: CatalogQuery }) {
  const category = queryText(params, 'category'), keyword = queryText(params, 'keyword');
  const categories = await getCategories();
  if (category && categories.ok && !categories.data.data.some(c => c.name === category)) notFound();
  const result = category && !categories.ok ? { ok: false as const, kind: 'unavailable' as const } : await getProducts(keyword, category);
  const products = result.ok ? result.data.data : [];
  const filters = { brand: queryText(params, 'brand'), subcategory: queryText(params, 'subcategory'), stock: queryText(params, 'stock'), max: queryText(params, 'max'), sort: queryText(params, 'sort'), collection: queryText(params, 'collection') };
  const filtered = filterProducts(products, filters);
  const brandNames = [...new Set(products.map(p => p.brand).filter((s): s is string => !!s))].sort();
  const subcategoryNames = [...new Set(products.map(p => p.sub_category).filter((s): s is string => !!s))].sort();
  const pages = Math.max(1, Math.ceil(filtered.length / 12));
  const page = Math.min(pages, Math.max(1, Math.floor(Number(queryText(params, 'page')) || 1)));
  const pageProducts = filtered.slice((page - 1) * 12, page * 12);
  const nextPage = (n: number) => catalogHref({ category, keyword, ...filters, page: String(n) });
  return <div className="container catalog-page">
    <nav className="breadcrumbs" aria-label="Breadcrumb"><Link href="/">Home</Link><span aria-hidden="true">/</span><span>Collection</span>{category && <><span aria-hidden="true">/</span><span>{category}</span></>}</nav>
    <div className="page-heading"><p className="eyebrow">Find your next favourite</p><h1>{keyword ? `Results for “${keyword}”` : category || (filters.collection === 'offers' ? 'A little more value.' : 'The everyday collection.')}</h1><p>Take a closer look. There’s a little more to discover.</p></div>
    <form action="/products" className="catalog-filters">
      <div className="filters-title"><SlidersHorizontal size={17} /><strong>Refine your discovery</strong></div>
      {keyword && <input type="hidden" name="keyword" value={keyword} />}
      {filters.collection && <input type="hidden" name="collection" value={filters.collection} />}
      <div className="filter-fields"><label>Category<select name="category" defaultValue={category}><option value="">All categories</option>{categories.ok && categories.data.data.map(c => <option key={c.name}>{c.name}</option>)}</select></label>
      <label>Brand<select name="brand" defaultValue={filters.brand}><option value="">All brands</option>{brandNames.map(name => <option key={name}>{name}</option>)}</select></label>
      {subcategoryNames.length > 0 && <label>Subcategory<select name="subcategory" defaultValue={filters.subcategory}><option value="">All subcategories</option>{subcategoryNames.map(name => <option key={name}>{name}</option>)}</select></label>}
      <label>Availability<select name="stock" defaultValue={filters.stock}><option value="">All products</option><option value="in">In stock</option></select></label>
      <label>Maximum price (₹)<input type="number" min="0" step="0.01" name="max" defaultValue={filters.max} placeholder="Any price" /></label>
      <label>Sort by<select name="sort" defaultValue={filters.sort}><option value="">Newest first</option><option value="price-asc">Price: low to high</option><option value="price-desc">Price: high to low</option><option value="name">Name: A to Z</option></select></label>
      <button className="button button-primary" type="submit">Apply filters</button><Link className="filter-clear" href="/products">Clear all</Link></div>
    </form>
    <div className="catalog-results-header"><span>{result.ok ? `${filtered.length} ${filtered.length === 1 ? 'discovery' : 'discoveries'}` : 'Catalog unavailable'}</span><details><summary>About these filters</summary><p>Search and category use the catalog API. Brand, subcategory, price, availability, sorting and page controls apply to the full catalog response loaded on our server.</p></details></div>
    {!result.ok ? <CatalogState error /> : pageProducts.length ? <ProductGrid products={pageProducts} /> : <CatalogState title={products.length ? 'Nothing here just yet.' : undefined} description={products.length ? 'Try another search or clear your filters to explore more of the collection.' : undefined} />}
    {pages > 1 && <nav className="pagination" aria-label="Catalog pages">{page > 1 ? <Link href={nextPage(page - 1)}><ArrowLeft size={16} />Previous</Link> : <span />}<span aria-current="page">Page {page} of {pages}</span>{page < pages ? <Link href={nextPage(page + 1)}>Next<ArrowRight size={16} /></Link> : <span />}</nav>}
  </div>;
}
