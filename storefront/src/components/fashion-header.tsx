'use client';

import Image from 'next/image';
import Link from 'next/link';
import { useEffect, useRef, useState } from 'react';
import { usePathname } from 'next/navigation';
import { ArrowRight, ChevronDown, Heart, Menu, Search, UserRound } from 'lucide-react';
import { catalogHref } from '@/lib/catalog';
import { BagLink } from './shopping-state';
import { Modal } from './modal';
import { SearchBox } from './search';

export type NavigationCollection = { name: string; subcategories: string[] };
export function FashionHeader({ collections }: { collections: NavigationCollection[] }) {
  const [panel, setPanel] = useState<'menu' | 'search' | null>(null), [mega, setMega] = useState(false);
  const root = useRef<HTMLElement>(null);
  const pathname = usePathname();
  const close = () => setPanel(null);
  useEffect(() => { setPanel(null); setMega(false); }, [pathname]);
  useEffect(() => {
    const dismiss = (event: PointerEvent) => { if (!root.current?.contains(event.target as Node)) setMega(false); };
    document.addEventListener('pointerdown', dismiss);
    return () => document.removeEventListener('pointerdown', dismiss);
  }, []);
  return <>
    <div className="announcement"><span>THE VANTHARA EDIT</span><Link href="/products">A new point of view <ArrowRight size={12} aria-hidden="true" /></Link></div>
    <header className="site-header fashion-header" ref={root} onKeyDown={event => { if (event.key === 'Escape') { setMega(false); root.current?.querySelector<HTMLButtonElement>('[aria-controls="collections-menu"]')?.focus(); } }}>
      <div className="container header-main">
        <button className="icon-button mobile-menu-trigger" type="button" aria-label="Open navigation" onClick={() => setPanel('menu')}><Menu size={22} strokeWidth={1.4} /></button>
        <Link href="/" className="brand" aria-label="Vanthara home"><Image src="/vantara-logo.png" alt="Vanthara" width={172} height={56} priority /></Link>
        <nav className="fashion-desktop-nav" aria-label="Main navigation"><Link href="/products?sort=newest">New arrivals</Link><button type="button" aria-expanded={mega} aria-controls="collections-menu" onClick={() => setMega(!mega)}>Collections <ChevronDown size={13} /></button>{collections.slice(0, 3).map(collection => <Link href={catalogHref({ category: collection.name })} key={collection.name}>{collection.name}</Link>)}<Link href="/about">The label</Link></nav>
        <nav className="header-actions" aria-label="Shopping shortcuts"><button type="button" className="icon-button" aria-label="Open search" onClick={() => setPanel('search')}><Search size={21} strokeWidth={1.4} /></button><Link href="/account" className="header-account" aria-label="Your account"><UserRound size={21} strokeWidth={1.4} /></Link><Link href="/wishlist" className="header-wishlist" aria-label="Your wishlist"><Heart size={21} strokeWidth={1.4} /></Link><BagLink /></nav>
      </div>
      {mega && <div className="fashion-mega" id="collections-menu" onBlur={event => { if (!root.current?.contains(event.relatedTarget)) setMega(false); }}><div className="container fashion-mega-inner"><div className="mega-introduction"><p className="eyebrow">Your wardrobe, considered</p><h2>Find your<br /><em>point of view.</em></h2><Link href="/products" onClick={() => setMega(false)}>Explore all pieces <ArrowRight size={16} /></Link></div>{collections.length ? collections.map(collection => <section key={collection.name}><h3><Link href={catalogHref({ category: collection.name })} onClick={() => setMega(false)}>{collection.name}</Link></h3><Link href={catalogHref({ category: collection.name })} onClick={() => setMega(false)}>View all</Link>{collection.subcategories.map(name => <Link key={name} href={catalogHref({ category: collection.name, subcategory: name })} onClick={() => setMega(false)}>{name}</Link>)}</section>) : <Link href="/products" onClick={() => setMega(false)}>Browse the collection</Link>}</div></div>}
    </header>
    <Modal open={panel === 'menu'} onClose={close} title="Explore Vanthara" variant="left"><nav className="mobile-drawer-links" aria-label="Mobile collections"><Link href="/products?sort=newest" onClick={close}>New arrivals <ArrowRight size={19} /></Link><Link href="/products" onClick={close}>Shop all pieces</Link>{collections.map(collection => <details key={collection.name}><summary>{collection.name}</summary><Link href={catalogHref({ category: collection.name })} onClick={close}>View all {collection.name}</Link>{collection.subcategories.map(name => <Link key={name} href={catalogHref({ category: collection.name, subcategory: name })} onClick={close}>{name}</Link>)}</details>)}<div className="drawer-secondary"><Link href="/about" onClick={close}>About the label</Link><Link href="/account" onClick={close}>My account</Link><Link href="/wholesale-enquiry" onClick={close}>Wholesale enquiries</Link><Link href="/contact" onClick={close}>Contact us</Link></div></nav></Modal>
    <Modal open={panel === 'search'} onClose={close} title="Find your next piece" variant="search"><SearchBox autoFocus onNavigate={close} /></Modal>
  </>;
}
