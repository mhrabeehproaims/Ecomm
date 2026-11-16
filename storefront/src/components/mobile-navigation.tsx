'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { Grid2X2, Heart, Home, ShoppingBag, UserRound } from 'lucide-react';

const destinations = [
  { href: '/', label: 'Home', icon: Home },
  { href: '/categories', label: 'Categories', icon: Grid2X2 },
  { href: '/wishlist', label: 'Wishlist', icon: Heart },
  { href: '/cart', label: 'Bag', icon: ShoppingBag },
  { href: '/account', label: 'Account', icon: UserRound },
];

export function MobileNavigation() {
  const pathname = usePathname();
  if (pathname.startsWith('/products/') || !['/', '/products', '/categories', '/wishlist', '/cart'].some(path => pathname === path || (path === '/categories' && pathname.startsWith('/categories/')))) return null;
  return <nav className="mobile-nav" aria-label="Mobile shopping navigation">{destinations.map(({ href, label, icon: Icon }) => {
    const active = pathname === href || (href === '/categories' && (pathname.startsWith('/categories/') || pathname.startsWith('/products')));
    return <Link href={href} key={href} aria-current={active ? 'page' : undefined}><Icon size={21} aria-hidden="true" /><span>{label}</span></Link>;
  })}</nav>;
}
