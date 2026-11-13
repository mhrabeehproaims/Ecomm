import Link from 'next/link';
import { ArrowRight, ArrowUpRight, PackageOpen, RefreshCw } from 'lucide-react';
import type { Category, Product } from '@/lib/contracts';
import { apiOrigin } from '@/lib/config';
import { money, numberOrNull, productHref, productPrice } from '@/lib/catalog';
import { mediaUrl } from '@/lib/media';
import { Media } from './media';

export function SectionHeading({ eyebrow, title, href, link = 'Explore all' }: { eyebrow: string; title: string; href?: string; link?: string }) {
  return <div className="section-heading"><div><p className="eyebrow">{eyebrow}</p><h2>{title}</h2></div>{href && <Link className="text-link" href={href}>{link}<ArrowUpRight size={17} /></Link>}</div>;
}

export function ProductCard({ product, priority = false }: { product: Product; priority?: boolean }) {
  const price = productPrice(product);
  const stock = numberOrNull(product.stock);
  const mrp = numberOrNull(product.mrp);
  const discount = price !== null && mrp !== null && mrp > price ? Math.round((1 - price / mrp) * 100) : 0;
  const href = productHref(product);
  return <article className="product-card">
    <Link href={href} prefetch={false} className="product-picture" aria-label={`View ${product.product_name}`}><Media src={mediaUrl(apiOrigin, product.primary_image, product.image_path)} alt={product.product_name} priority={priority} sizes="(max-width: 599px) 46vw, (max-width: 1023px) 30vw, (max-width: 1359px) 23vw, 302px" />{discount > 0 && <span className="product-badge">{discount}% less than MRP</span>}</Link>
    <div className="product-copy"><p className="product-category">{product.brand || product.category || 'Vanthara collection'}</p><h3><Link href={href} prefetch={false} title={product.product_name}>{product.product_name}</Link></h3><p className="product-size">{[product.weight, product.unit, product.size].filter(Boolean).join(' · ') || product.package_type || 'See product details'}</p><div className="product-bottom"><div className="price">{price !== null ? money(price) : 'Price unavailable'}{price !== null && mrp !== null && mrp > price && <del>{money(mrp)}</del>}</div><Link href={href} prefetch={false} className="product-open" aria-label={`Explore ${product.product_name}`}><ArrowUpRight size={20} /></Link></div><p className={`stock-label ${stock !== null && stock > 0 ? 'available' : ''}`}>{stock === null ? 'Availability not provided' : stock > 0 ? 'In stock' : 'Currently unavailable'}</p></div>
  </article>;
}
export function ProductGrid({ products, eagerFirst = false }: { products: Product[]; eagerFirst?: boolean }) { return <div className="product-grid">{products.map((p, index) => <ProductCard key={`${p.category}:${p.product_name}`} product={p} priority={eagerFirst && index === 0} />)}</div>; }

export function CategoryGrid({ categories }: { categories: Category[] }) {
  return <div className={`category-grid ${categories.length === 1 ? 'single-category' : ''}`}>{categories.map((c, i) => <Link href={`/categories/${encodeURIComponent(c.name)}`} className={`category-card tone-${i % 4}`} key={c.name}><Media src={mediaUrl(apiOrigin, c.image, c.image_url)} alt={c.name} sizes="(max-width: 767px) 42vw, 180px" /><div><h3>{c.name}</h3><ArrowUpRight size={17} aria-hidden="true" /></div></Link>)}</div>;
}

export function CatalogState({ error = false, compact = false, title, description }: { error?: boolean; compact?: boolean; title?: string; description?: string }) {
  return <div className={`catalog-state ${compact ? 'compact' : ''}`} role={error ? 'alert' : 'status'}><div className="state-icon">{error ? <RefreshCw size={25} strokeWidth={1.25} /> : <PackageOpen size={29} strokeWidth={1.1} />}</div><div><h3>{title || (error ? 'The collection is taking a moment.' : 'Good things are on their way.')}</h3><p>{description || (error ? 'We couldn’t reach this part of the catalog. Please try again in a moment.' : 'There are no products in this collection yet. Come back soon for new discoveries.')}</p></div>{error ? <a href="" className="text-link">Try again <RefreshCw size={14} /></a> : <Link href="/products" className="text-link">Explore the catalog <ArrowRight size={16} /></Link>}</div>;
}
