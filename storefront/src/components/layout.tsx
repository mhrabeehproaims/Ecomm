import Link from 'next/link';
import Image from 'next/image';
import { ArrowUpRight, Grid2X2, Heart, Home, Leaf, ShoppingBag, UserRound } from 'lucide-react';
import { getCategories } from '@/lib/api';
import { catalogHref } from '@/lib/catalog';
import { SearchBox } from './search';

export async function Header() {
  const result = await getCategories();
  const categories = result.ok ? result.data.data : [];
  return <>
    <div className="announcement"><span>A little more thought. A little more everyday.</span><Link href="/about">Meet Vantara <ArrowUpRight size={13} /></Link></div>
    <header className="site-header">
      <div className="container header-main">
        <Link href="/" className="brand" aria-label="Vantara home"><Image src="/vantara-logo.png" alt="Vantara" width={172} height={56} priority /></Link>
        <SearchBox />
        <nav className="header-actions" aria-label="Shopping shortcuts">
          <Link href="/account"><UserRound size={21} /><span>Account</span></Link>
          <Link href="/wishlist"><Heart size={21} /><span>Wishlist</span></Link>
          <Link href="/cart"><ShoppingBag size={21} /><span>Bag</span></Link>
        </nav>
      </div>
      <div className="nav-border"><nav className="container category-nav" aria-label="Main navigation">
        <Link href="/categories" className="all-categories"><Grid2X2 size={17} />All categories</Link>
        <Link href="/products">Shop all</Link>
        {categories.slice(0, 5).map(c => <Link key={c.name} href={`/categories/${encodeURIComponent(c.name)}`}>{c.name}</Link>)}
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
        <div className="footer-story"><Image src="/vantara-logo.png" alt="Vantara" width={156} height={51} /><p>Thoughtful discoveries.<br />For the way you live, every day.</p><Link href="/about">Get to know us <ArrowUpRight size={15} /></Link></div>
        <div><h2>Discover</h2><Link href="/products">Shop all products</Link><Link href="/categories">Our categories</Link><Link href="/products?collection=offers">Special offers</Link><Link href="/wholesale">Shop for your business</Link></div>
        <div><h2>Here to help</h2><Link href="/contact">Contact us</Link><Link href="/faq">Common questions</Link><Link href="/shipping">Shipping information</Link><Link href="/returns">Returns & refunds</Link></div>
        <div className="footer-note"><Leaf size={24} strokeWidth={1.3} /><h2>Everyday, considered.</h2><p>Take a moment. Explore the details. Find what feels right for you.</p></div>
      </div>
      <div className="container footer-bottom"><span>© {new Date().getFullYear()} Vantara</span><div><Link href="/privacy">Privacy</Link><Link href="/terms">Terms & conditions</Link></div><span>Made for your everyday.</span></div>
    </footer>
    <nav className="mobile-nav" aria-label="Mobile shopping navigation">
      <Link href="/"><Home size={20} />Home</Link><Link href="/categories"><Grid2X2 size={20} />Categories</Link><Link href="/wishlist"><Heart size={20} />Wishlist</Link><Link href="/cart"><ShoppingBag size={20} />Bag</Link><Link href="/account"><UserRound size={20} />Account</Link>
    </nav>
  </>;
}
