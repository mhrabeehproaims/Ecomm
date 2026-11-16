import type { Metadata } from 'next';
import localFont from 'next/font/local';
import { ShoppingProvider } from '@/components/shopping-state';
import { Header, Footer } from '@/components/layout';
import { CatalogFreshness } from '@/components/catalog-freshness';
import { indexable, siteOrigin } from '@/lib/config';
import './globals.css';
import './public-information.css';
import './fashion.css';

const manrope = localFont({ src: '../fonts/manrope-normal.woff2', variable: '--font-fashion-sans', display: 'swap', weight: '400 700' });
const editorial = localFont({ src: [{ path: '../fonts/cormorant-normal.woff2', style: 'normal', weight: '400 600' }, { path: '../fonts/cormorant-italic.woff2', style: 'italic', weight: '400 600' }], variable: '--font-fashion-serif', display: 'swap' });

export const metadata: Metadata = {
  metadataBase: new URL(siteOrigin),
  title: { default: 'Vanthara — A considered point of view.', template: '%s | Vanthara' },
  description: 'Discover the Vanthara clothing collection. Explore new arrivals, find your size and make the everyday your own.',
  robots: { index: indexable, follow: indexable },
  icons: { icon: '/favicon.png' },
  openGraph: { type: 'website', siteName: 'Vanthara', locale: 'en_IN', title: 'Vanthara — A considered point of view.', description: 'Thoughtful discoveries for your everyday.' },
};

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return <html lang="en-IN"><body className={`${manrope.variable} ${editorial.variable}`}><ShoppingProvider><CatalogFreshness /><a className="skip-link" href="#main-content">Skip to content</a><Header /><main id="main-content" tabIndex={-1}>{children}</main><Footer /></ShoppingProvider></body></html>;
}
