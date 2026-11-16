import Image from 'next/image';
import Link from 'next/link';
import { ArrowRight, ArrowUpRight } from 'lucide-react';
import { getBanners, getBrands, getCategories, getFeatured, getProducts, getTopSellers } from '@/lib/api';
import { apiOrigin } from '@/lib/config';
import { activeBanners, activeRecommendations, hasOffer, safeBannerLink } from '@/lib/catalog';
import { mediaUrl } from '@/lib/media';
import { CatalogState, CategoryGrid, ProductGrid, SectionHeading } from '@/components/catalog';
import { Media } from '@/components/media';

export const metadata = { title: 'Vanthara — A considered point of view.', alternates: { canonical: '/' } };
export default async function HomePage() {
  const [categories, products, featured, top, banners, brands] = await Promise.all([getCategories(), getProducts(), getFeatured(), getTopSellers(), getBanners(), getBrands()]);
  const active = products.ok ? products.data.data : [];
  const featuredProducts = featured.ok && products.ok ? activeRecommendations(featured.data.data, active).slice(0, 4) : [];
  const topProducts = top.ok && products.ok ? activeRecommendations(top.data.data.flatMap(item => item.product ? [{ product_name: item.product.product_name, category: item.product.catalog_category }] : []), active).slice(0, 8) : [];
  const offers = active.filter(hasOffer).slice(0, 4);
  const promotions = banners.ok ? activeBanners(banners.data) : [];
  const campaign = promotions.find(banner => mediaUrl(apiOrigin, banner.banner_image, null, 'BannerImage'));
  const campaignImage = campaign ? mediaUrl(apiOrigin, campaign.banner_image, null, 'BannerImage') : null;
  return <div className="fashion-home">
    <section className={`fashion-campaign ${campaignImage ? 'fashion-campaign--admin' : ''}`} aria-labelledby="campaign-title">
      <div className="fashion-campaign-image">{campaignImage ? <Media src={campaignImage} alt={campaign?.banner_title || 'The Vanthara collection'} priority sizes="100vw" /> : <Image src="/fashion/hero-campaign.webp" alt="The Vanthara editorial: contemporary clothing in warm architectural light" fill priority sizes="100vw" />}</div>
      <div className="container fashion-campaign-inner"><div className="fashion-campaign-copy"><p className="eyebrow">The wardrobe edit</p><h1 id="campaign-title">{campaign?.banner_title || <>An everyday<br /><em>point of view.</em></>}</h1><p>{campaign?.banner_subtitle || 'A fresh perspective on getting dressed. Discover the Vanthara collection.'}</p><Link className="button button-primary" href={campaign ? safeBannerLink(campaign.button_link) : '/products'}>{campaign?.button_text || 'Shop the collection'}<ArrowUpRight size={16} /></Link></div><span className="campaign-signature">VANTHARA / THE EDIT</span></div>
    </section>
    <div className="fashion-manifesto"><span>Clothing. A little more considered.</span><span className="manifesto-line" aria-hidden="true" /><Link href="/products?sort=newest">Discover your next piece <ArrowRight size={14} /></Link></div>
    <section className="container section fashion-arrivals" id="new-arrivals"><SectionHeading eyebrow="Fresh perspective" title="New arrivals." href="/products?sort=newest" link="View the collection" />{!products.ok ? <CatalogState error /> : active.length ? <ProductGrid products={active.slice(0, 4)} eagerFirst /> : <CatalogState title="The next chapter is coming." description="Our latest pieces will appear here as they join the collection." />}</section>
    <section className="container section fashion-collections" id="categories"><SectionHeading eyebrow="Your wardrobe starts here" title="Find your point of view." href="/categories" link="All collections" />{categories.ok && categories.data.data.length ? <CategoryGrid categories={categories.data.data.slice(0, 4)} /> : <CatalogState compact error={!categories.ok} title="More to discover, soon." description="Explore all available pieces in the collection." />}</section>
    <section className="editorial-split"><div className="editorial-split-image"><Image src="/fashion/editorial-campaign.webp" alt="A Vanthara fashion editorial in deep green and warm ivory" fill sizes="(max-width: 767px) 100vw, 50vw" /></div><div className="editorial-split-copy"><p className="eyebrow">A different kind of everyday</p><h2>Make room<br />for <em>yourself.</em></h2><p>A new perspective. An unexpected pairing. Find the pieces that feel like you.</p><Link className="text-link" href="/products">Explore the edit <ArrowUpRight size={17} /></Link><span className="editorial-number" aria-hidden="true">V.</span></div></section>
    {topProducts.length > 0 && <section className="container section fashion-popular"><SectionHeading eyebrow="Most-loved pieces" title="In good company." href="/products" link="Explore all pieces" /><ProductGrid products={topProducts} rail /></section>}
    {featuredProducts.length > 0 && <section className="container section featured-section"><SectionHeading eyebrow="Selected from the collection" title="The Vanthara edit." href="/products" /><ProductGrid products={featuredProducts} /></section>}
    {offers.length > 0 && <section className="container section"><SectionHeading eyebrow="From the current collection" title="A little more to love." href="/products?collection=offers" link="View offers" /><ProductGrid products={offers} /></section>}
    {promotions.filter(banner => banner !== campaign).slice(0, 2).map(banner => <section key={String(banner.banner_id)} className="container section fashion-admin-banner"><Media src={mediaUrl(apiOrigin, banner.banner_image, null, 'BannerImage')} alt={banner.banner_title || 'Vanthara collection'} sizes="90vw" /><div><h2>{banner.banner_title}</h2><p>{banner.banner_subtitle}</p><Link className="text-link" href={safeBannerLink(banner.button_link)}>{banner.button_text || 'Explore the collection'}<ArrowUpRight size={16} /></Link></div></section>)}
    <section className="container fashion-brand-story"><p className="eyebrow">Meet the label</p><h2>A considered<br /><em>point of view.</em></h2><p>Getting dressed is personal. Get to know Vanthara, and discover a collection to make your own.</p><Link className="text-link" href="/about">About Vanthara <ArrowUpRight size={17} /></Link><span className="brand-story-wordmark" aria-hidden="true">VANTHARA</span></section>
    {brands.ok && brands.data.data.length > 0 && <section className="container fashion-labels"><p className="eyebrow">In the collection</p><div>{brands.data.data.map(brand => <Link key={String(brand.brand_id)} href={`/products?${new URLSearchParams({ brand: brand.brand_name })}`}>{brand.brand_name}</Link>)}</div></section>}
  </div>;
}