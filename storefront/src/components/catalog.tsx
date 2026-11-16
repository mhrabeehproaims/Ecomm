import Link from 'next/link';
import { ArrowRight, ArrowUpRight, PackageOpen, RefreshCw } from 'lucide-react';
import type { Category, Product } from '@/lib/contracts';
import { apiOrigin } from '@/lib/config';
import { money, numberOrNull, productHref, productPrice } from '@/lib/catalog';
import { mediaUrl } from '@/lib/media';
import { Media } from './media';
import { WishlistToggle } from './shopping-state';
import { QuickView } from './quick-view';

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
    <div className="product-image-area"><Link href={href} prefetch={false} className="product-picture" aria-label={`View ${product.product_name}`}><Media src={mediaUrl(apiOrigin, product.primary_image, product.image_path)} alt={product.product_name} priority={priority} sizes="(max-width: 599px) 46vw, (max-width: 1023px) 30vw, (max-width: 1359px) 23vw, 330px" />{discount > 0 && <span className="product-badge">{discount}% less than MRP</span>}</Link><WishlistToggle product={product} /><QuickView productId={product.product_id} name={product.product_name} imageOrigin={apiOrigin} /></div>
    <div className="product-copy"><p className="product-category">{product.category || product.brand || 'Vanthara collection'}</p><h3><Link href={href} prefetch={false} title={product.product_name}>{product.product_name}</Link></h3><p className="product-size">{[product.size, product.brand].filter(Boolean).join(' · ') || 'Discover this piece'}</p><div className="product-bottom"><div className="price">{price !== null ? money(price) : 'Price unavailable'}{price !== null && mrp !== null && mrp > price && <del>{money(mrp)}</del>}</div><Link href={href} prefetch={false} className="product-open" aria-label={`Explore ${product.product_name}`}><ArrowUpRight size={20} /></Link></div><p className={`stock-label ${stock !== null && stock > 0 ? 'available' : ''}`}>{stock === null ? 'Availability not provided' : stock > 0 ? 'In stock' : 'Currently unavailable'}</p></div>
  </article>;
}
export function ProductGrid({ products, eagerFirst = false, rail = false }: { products: Product[]; eagerFirst?: boolean; rail?: boolean }) { return <div className={`product-grid ${rail ? 'product-grid--rail' : ''}`} data-count={products.length} {...(rail ? { tabIndex: 0, role: 'region', 'aria-label': 'Popular pieces; scroll for more' } : {})}>{products.map((p, index) => <ProductCard key={p.product_id} product={p} priority={eagerFirst && index === 0} />)}</div>; }

export function CategoryGrid({ categories }: { categories: Category[] }) {
  return <div className={`category-grid ${categories.length === 1 ? 'single-category' : ''}`}>{categories.map(c => <Link href={`/categories/${encodeURIComponent(c.name)}`} className="category-card" key={c.name}><Media src={mediaUrl(apiOrigin, c.image, c.image_url) || (/clothing|apparel|fashion|shirt/i.test(c.name) ? '/fashion/editorial-campaign.webp' : null)} alt={`${c.name} collection`} sizes="(max-width: 767px) 90vw, 46vw" /><div><p className="eyebrow">Explore the collection</p><h3>{c.name}</h3><span className="category-action">Discover more <ArrowUpRight size={16} aria-hidden="true" /></span></div></Link>)}</div>;
}

export function CatalogState({ error = false, compact = false, title, description }: { error?: boolean; compact?: boolean; title?: string; description?: string }) {
  return <div className={`catalog-state ${compact ? 'compact' : ''}`} role={error ? 'alert' : 'status'}><div className="state-icon">{error ? <RefreshCw size={25} strokeWidth={1.25} /> : <PackageOpen size={29} strokeWidth={1.1} />}</div><div><h3>{title || (error ? 'The collection is taking a moment.' : 'Good things are on their way.')}</h3><p>{description || (error ? 'We couldn’t reach this part of the catalog. Please try again in a moment.' : 'There are no products in this collection yet. Come back soon for new discoveries.')}</p></div>{error ? <a href="" className="text-link">Try again <RefreshCw size={14} /></a> : <Link href="/products" className="text-link">Explore the catalog <ArrowRight size={16} /></Link>}</div>;
}
