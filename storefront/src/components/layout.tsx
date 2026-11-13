import Link from 'next/link';
import Image from 'next/image';
import { Suspense } from 'react';
import { ArrowUpRight, Grid2X2, Heart, Leaf, ShoppingBag, UserRound } from 'lucide-react';
import { getCategories } from '@/lib/api';
import { SearchBox } from './search';
import { MobileNavigation } from './mobile-navigation';

async function CategoryLinks() {
  const result = await getCategories();
  const categories = result.ok ? result.data.data : [];
  return categories.slice(0, 5).map(c => <Link key={c.name} href={`/categories/${encodeURIComponent(c.name)}`}>{c.name}</Link>);
}

export function Header() {
  return <>
    <div className="announcement"><span>A little more thought. A little more everyday.</span><Link href="/about">Meet Vanthara <ArrowUpRight size={13} /></Link></div>
    <header className="site-header">
      <div className="container header-main">
        <Link href="/" className="brand" aria-label="Vanthara home"><Image src="/vantara-logo.png" alt="Vanthara" width={172} height={56} priority /></Link>
        <SearchBox />
        <nav className="header-actions" aria-label="Shopping shortcuts">
          <Link href="/account" aria-label="Your account"><UserRound size={21} aria-hidden="true" /><span>Account</span></Link>
          <Link href="/wishlist" aria-label="Your wishlist"><Heart size={21} aria-hidden="true" /><span>Wishlist</span></Link>
          <Link href="/cart" aria-label="Your shopping bag"><ShoppingBag size={21} aria-hidden="true" /><span>Bag</span></Link>
        </nav>
      </div>
      <div className="nav-border"><nav className="container category-nav" aria-label="Main navigation">
        <Link href="/categories" className="all-categories"><Grid2X2 size={17} />All categories</Link>
        <Link href="/products">Shop all</Link>
        <Suspense fallback={null}><CategoryLinks /></Suspense>
        <Link href="/products?collection=offers">Special offers</Link>
        <Link href="/wholesale" className="nav-wholesale">Wholesale enquiries <ArrowUpRight size={14} /></Link>
      </nav></div>
    </header>
  </>;
}

export function Footer() {
  return <>
    <footer className="footer">
      <div className="container footer-main">
        <div className="footer-story"><Image src="/vantara-logo.png" alt="Vanthara" width={156} height={51} /><p>Thoughtful discoveries.<br />For the way you live, every day.</p><Link href="/about">Get to know us <ArrowUpRight size={15} /></Link></div>
        <div><h2>Discover</h2><Link href="/products">Shop all products</Link><Link href="/categories">Our categories</Link><Link href="/products?collection=offers">Special offers</Link><Link href="/wholesale">Shop for your business</Link></div>
        <div><h2>Here to help</h2><Link href="/contact">Contact us</Link><Link href="/faq">Common questions</Link><Link href="/shipping">Shipping information</Link><Link href="/returns">Returns & refunds</Link></div>
        <div className="footer-note"><Leaf size={24} strokeWidth={1.3} /><h2>Everyday, considered.</h2><p>Take a moment. Explore the details. Find what feels right for you.</p></div>
      </div>
      <div className="container footer-bottom"><span>© {new Date().getFullYear()} Vanthara</span><div><Link href="/privacy">Privacy</Link><Link href="/terms">Terms & conditions</Link></div><span>Made for your everyday.</span></div>
    </footer>
    <MobileNavigation />
  </>;
}
