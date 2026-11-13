import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import { ArrowUpRight, Info, PackageOpen } from 'lucide-react';
import { getProduct, getProducts } from '@/lib/api';
import { apiOrigin } from '@/lib/config';
import { activeRecommendations, catalogHref, money, numberOrNull, plainText, productPrice } from '@/lib/catalog';
import { mediaUrl } from '@/lib/media';
import { CatalogState, ProductGrid, SectionHeading } from '@/components/catalog';
import { Media } from '@/components/media';

type Props = { params: Promise<{ slug: string }>; searchParams: Promise<{ id?: string | string[] }> };
async function load(searchParams: Props['searchParams']) {
  const { id } = await searchParams;
  if (typeof id !== 'string' || id.length < 20 || id.length > 4096) return null;
  const [detail, catalog] = await Promise.all([getProduct(id), getProducts('', '', true)]);
  if (!detail.ok || !catalog.ok) return { error: true as const };
  const product = detail.data[0];
  if (!product || !activeRecommendations([product], catalog.data.data).length) return null;
  return { error: false as const, product, catalog: catalog.data.data };
}
export async function generateMetadata({ searchParams }: Props): Promise<Metadata> {
  const result = await load(searchParams);
  return { title: result && !result.error ? result.product.product_name : 'Product details', description: result && !result.error ? plainText(result.product.description).slice(0, 155) || `Explore ${result.product.product_name} at Vantara.` : undefined, robots: { index: false, follow: true } };
}
export default async function ProductPage({ searchParams }: Props) {
  const result = await load(searchParams);
  if (!result) notFound();
  if (result.error) return <div className="container section"><CatalogState error /></div>;
  const { product, catalog } = result;
  const price = productPrice(product), mrp = numberOrNull(product.mrp), stock = numberOrNull(product.stock);
  const images = [...new Set([product.primary_image, ...(product.images || [])].filter((p): p is string => !!p))];
  const related = catalog.filter(p => p.category === product.category && p.product_name !== product.product_name).slice(0, 4);
  const specs = [['Brand', product.brand], ['Category', product.category], ['Subcategory', product.sub_category], ['Weight', product.weight], ['Unit', product.unit], ['Size', product.size], ['Packaging', product.package_type]].filter(([, value]) => value !== null && value !== undefined && value !== '');
  const image = mediaUrl(apiOrigin, product.primary_image, product.image_path);
  const structured = { '@context': 'https://schema.org', '@type': 'Product', name: product.product_name, description: plainText(product.description) || undefined, image: image ? [image] : undefined, brand: product.brand ? { '@type': 'Brand', name: product.brand } : undefined, ...(price !== null ? { offers: { '@type': 'Offer', priceCurrency: 'INR', price, availability: stock !== null ? stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' : undefined } } : {}) };
  return <div className="container product-page">
    <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(structured).replace(/</g, '\\u003c') }} />
    <nav className="breadcrumbs" aria-label="Breadcrumb"><Link href="/">Home</Link><span>/</span><Link href="/products">Collection</Link>{product.category && <><span>/</span><Link href={catalogHref({ category: product.category })}>{product.category}</Link></>}</nav>
    <div className="product-detail"><div className="product-gallery"><Media src={image} alt={product.product_name} priority sizes="(max-width: 700px) 100vw, 50vw" />{images.length > 1 && <div className="gallery-extras">{images.filter(i => i !== product.primary_image).map((path, i) => <Media key={path} src={mediaUrl(apiOrigin, path, product.image_path)} alt={`${product.product_name}, view ${i + 2}`} sizes="(max-width: 700px) 45vw, 22vw" />)}</div>}</div>
    <div className="product-information"><p className="eyebrow">{product.brand || 'The Vantara collection'}</p><h1>{product.product_name}</h1><p className="detail-description">{plainText(product.description) || 'Explore the product specifications below.'}</p><div className="detail-price">{price !== null ? money(price) : 'Price unavailable'}{mrp !== null && price !== null && mrp > price && <del>MRP {money(mrp)}</del>}</div>{product.offer?.title && <p className="offer-note">{product.offer.title}</p>}<p className={`detail-stock ${stock && stock > 0 ? 'available' : ''}`}><PackageOpen size={17} />{stock === null ? 'Availability not provided' : stock > 0 ? `${stock} available` : 'Currently out of stock'}</p>
    <div className="phase-notice"><Info size={19} /><div><strong>Explore now. Shopping is coming next.</strong><p>Online cart and checkout are not available in this storefront yet.</p></div></div>
    <dl className="specifications">{specs.map(([name, value]) => <div key={String(name)}><dt>{name}</dt><dd>{value}</dd></div>)}</dl>
    {product.health_benefit && <details className="detail-disclosure"><summary>More product information</summary><p>{plainText(product.health_benefit)}</p></details>}
    <details className="detail-disclosure"><summary>Delivery & returns</summary><p>Delivery charges, serviceable locations and return eligibility will be shown when online ordering becomes available. Approved policies are pending.</p><Link className="text-link" href="/returns">Return information <ArrowUpRight size={14} /></Link></details>
    </div></div>{related.length > 0 && <section className="section"><SectionHeading eyebrow="Keep exploring" title="A few more discoveries." /><ProductGrid products={related} /></section>}
  </div>;
}
