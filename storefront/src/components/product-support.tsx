import Link from 'next/link';
import { ArrowUpRight } from 'lucide-react';
import type { Product } from '@/lib/contracts';
import { catalogHref, money, numberOrNull, productHref, productPrice } from '@/lib/catalog';
import { mediaUrl } from '@/lib/media';
import { Media } from './media';

export function ProductSupport({ product, catalog, imageOrigin }: { product: Product; catalog: Product[]; imageOrigin: string }) {
  const noSize = !product.size?.trim();
  const outOfStock = numberOrNull(product.stock) === 0;
  if (!noSize && !outOfStock) return null;
  const facts = [['Collection', product.category], ['Brand', product.brand]].filter(([, value]) => !!value?.trim());
  const alternatives = outOfStock && product.category?.trim() ? catalog.filter(item => item.category === product.category && item.product_name !== product.product_name && (numberOrNull(item.stock) ?? 0) > 0).slice(0, 2) : [];
  return <aside className="product-support" aria-label="Product help and alternatives">
    {noSize && facts.length > 0 && <dl className="product-quick-facts">{facts.map(([label, value]) => <div key={label}><dt>{label}</dt><dd>{value}</dd></div>)}</dl>}
    {outOfStock ? <>
      <h3>Keep exploring</h3><p>Save this item to your wishlist, or discover available pieces.</p>
      {alternatives.length > 0 && <div className="available-alternatives"><p className="eyebrow">Available in this collection</p>{alternatives.map(item => {
        const price = productPrice(item);
        return <Link href={productHref(item)} className="available-alternative" key={`${item.category}:${item.product_name}`}>
          <Media src={mediaUrl(imageOrigin, item.primary_image, item.image_path)} alt={item.product_name} sizes="64px" />
          <span><strong>{item.product_name}</strong><span>{price === null ? 'View product details' : money(price)}</span></span><ArrowUpRight size={17} aria-hidden="true" />
        </Link>;
      })}</div>}
      <Link className="text-link" href={catalogHref({ category: product.category || '', stock: 'in' })}>Browse available pieces <ArrowUpRight size={14} /></Link>
    </> : <p>Need help choosing this item?</p>}
    <Link className="product-help-link" href="/contact">{noSize ? 'Ask us about sizing or product details' : 'Ask us about this item'} <ArrowUpRight size={13} aria-hidden="true" /></Link>
  </aside>;
}
