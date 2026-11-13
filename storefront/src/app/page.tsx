import Image from 'next/image';
import Link from 'next/link';
import { ArrowRight, ArrowUpRight, Layers3, MessageCircle, ScanLine } from 'lucide-react';
import { getBanners, getBrands, getCategories, getFeatured, getProducts, getTopSellers } from '@/lib/api';
import { apiOrigin } from '@/lib/config';
import { activeBanners, activeRecommendations, catalogHref, hasOffer, safeBannerLink } from '@/lib/catalog';
import { mediaUrl } from '@/lib/media';
import { CatalogState, CategoryGrid, ProductGrid, SectionHeading } from '@/components/catalog';
import { Media } from '@/components/media';

export const metadata = { alternates: { canonical: '/' } };
export default async function HomePage() {
  const [categories, products, featured, top, banners, brands] = await Promise.all([getCategories(), getProducts(), getFeatured(), getTopSellers(), getBanners(), getBrands()]);
  const active = products.ok ? products.data.data : [];
  const featuredProducts = featured.ok && products.ok ? activeRecommendations(featured.data.data, active).slice(0, 4) : [];
  const topProducts = top.ok && products.ok ? activeRecommendations(top.data.data.flatMap(item => item.product ? [item.product] : []), active).slice(0, 4) : [];
  const offers = active.filter(hasOffer).slice(0, 4);
  const promotions = banners.ok ? activeBanners(banners.data).slice(0, 3) : [];
  return <>
    <section className="container hero" aria-labelledby="hero-title">
      <div className="hero-copy"><p className="eyebrow"><span className="little-line" /> Welcome to Vantara</p><h1 id="hero-title">A little more care.<br /><em>In every choice.</em></h1><p className="hero-description">Discover the everyday, thoughtfully.<br />Find your next favourite in the Vantara collection.</p><Link href="/products" className="button button-primary">Explore the collection <ArrowUpRight size={18} /></Link><div className="hero-footnote"><span className="hero-dot" /> Everyday, considered.</div></div>
      <div className="hero-visual"><Image src="/hero.webp" alt="" fill priority sizes="(max-width: 700px) 100vw, 55vw" className="object-cover" /><span className="hero-caption">A moment for the everyday.</span><span className="hero-roundel" aria-hidden="true">A little more<br /><i>thoughtful.</i></span></div>
    </section>
    <div className="container values-strip"><div><Layers3 size={23} strokeWidth={1.2} /><span><strong>Discover your everyday</strong><small>A collection to explore at your pace</small></span></div><div><ScanLine size={24} strokeWidth={1.2} /><span><strong>Look a little closer</strong><small>Clear prices and product details</small></span></div><div><MessageCircle size={24} strokeWidth={1.2} /><span><strong>A little guidance</strong><small>Helpful answers along the way</small></span></div></div>

    <section className="container section" id="categories"><SectionHeading eyebrow="Find your kind of everyday" title="Start with what you love." href="/categories" link="All categories" />{categories.ok && categories.data.data.length > 0 ? <CategoryGrid categories={categories.data.data.slice(0, 6)} /> : <CatalogState compact error={!categories.ok} title={categories.ok ? 'New categories, coming into view.' : undefined} description={categories.ok ? 'Our category collection is empty for now. Browse all products for what’s available.' : undefined} />}</section>

    {promotions.length > 0 && <section className="container section promotions" aria-label="Current promotions">{promotions.map(b => <article className="promotion" key={String(b.banner_id)}><Media src={mediaUrl(apiOrigin, b.banner_image, null, 'BannerImage')} alt={b.banner_title || 'Vantara promotion'} sizes="(max-width: 700px) 100vw, 50vw" /><div><h2>{b.banner_title}</h2>{b.banner_subtitle && <p>{b.banner_subtitle}</p>}<Link className="button button-light" href={safeBannerLink(b.button_link)}>{b.button_text || 'Explore collection'}<ArrowUpRight size={16} /></Link></div></article>)}</section>}

    <section className="section featured-section"><div className="container"><SectionHeading eyebrow="In the spotlight" title="The Vantara edit." href="/products" link="Shop the collection" />{featuredProducts.length > 0 ? <ProductGrid products={featuredProducts} /> : <CatalogState error={!featured.ok || !products.ok} />}</div></section>

    {topProducts.length > 0 && <section className="container section"><SectionHeading eyebrow="Most-loved discoveries" title="Your everyday favourites." href="/products" /><ProductGrid products={topProducts} /></section>}

    {offers.length > 0 && <section className="container section"><SectionHeading eyebrow="Offers from the collection" title="A little more value." href="/products?collection=offers" link="Explore all offers" /><ProductGrid products={offers} /></section>}

    {active.length > 0 && <section className="container section"><SectionHeading eyebrow="Fresh from the catalog" title="Something new to discover." href="/products" link="Browse all products" /><ProductGrid products={active.slice(0, 4)} /></section>}

    <section className="container section story-grid"><div className="story-panel"><span className="eyebrow">The Vantara way</span><h2>Everyday choices.<br /><em>A little more thought.</em></h2><p>From a first look to a closer read, make space for the details that matter to you.</p><Link href="/about" className="text-link">Our story <ArrowUpRight size={17} /></Link><span className="story-leaf" aria-hidden="true" /></div><div className="wholesale-panel"><div className="eyebrow">For your business</div><h2>Good things,<br />on a bigger scale.</h2><p>Looking to source for your business?<br />Let’s start a conversation.</p><Link href="/wholesale" className="button button-light">Explore wholesale <ArrowUpRight size={17} /></Link><span className="wholesale-number" aria-hidden="true">&</span></div></section>

    {brands.ok && brands.data.data.length > 0 && <section className="container section brands"><SectionHeading eyebrow="Meet the names in our collection" title="Brands to discover." /><div className="brand-grid">{brands.data.data.map(b => <Link href={catalogHref({ brand: b.brand_name })} key={String(b.brand_id)}>{b.brand_name}<ArrowUpRight size={16} /></Link>)}</div></section>}

    <section className="container last-word"><p className="eyebrow">Take a look around</p><h2>Your next discovery starts here.</h2><Link className="text-link" href="/products">Step into the collection <ArrowRight size={18} /></Link></section>
  </>;
}
