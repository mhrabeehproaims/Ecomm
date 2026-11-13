import type { Metadata } from 'next';
import { Header, Footer } from '@/components/layout';
import { indexable, siteOrigin } from '@/lib/config';
import './globals.css';

export const metadata: Metadata = {
  metadataBase: new URL(siteOrigin),
  title: { default: 'Vantara — Everyday, considered.', template: '%s | Vantara' },
  description: 'Explore the Vantara collection. Thoughtful discoveries, clear product details and a little more care for your everyday.',
  robots: { index: indexable, follow: indexable },
  icons: { icon: '/favicon.png' },
  openGraph: { type: 'website', siteName: 'Vantara', locale: 'en_IN', title: 'Vantara — Everyday, considered.', description: 'Thoughtful discoveries for your everyday.' },
};

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return <html lang="en-IN"><body><a className="skip-link" href="#main-content">Skip to content</a><Header /><main id="main-content" tabIndex={-1}>{children}</main><Footer /></body></html>;
}
