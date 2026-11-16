import type { Product } from '@/lib/contracts';
import { plainText } from '@/lib/catalog';
import { mediaUrl } from '@/lib/media';
import { DescriptionImages } from './description-images';

export function ProductDescription({ product, imageOrigin }: { product: Product; imageOrigin: string }) {
  const text = (product.health_benefit || '').split(/\r\n?|\n/).map(line => plainText(line)).join('\n').trim();
  const images = [...new Set((product.Description_images || []).map(path => mediaUrl(imageOrigin, path, product.image_path)).filter((url): url is string => !!url))];
  if (!text && !images.length) return null;
  return <section className="product-description-section" aria-labelledby="product-description-title">
    <h2 id="product-description-title">Detailed description</h2>
    {text && <p className="product-description-text">{text}</p>}
    {images.length > 0 && <DescriptionImages images={images} name={product.product_name} />}
  </section>;
}
