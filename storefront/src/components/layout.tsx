import Link from 'next/link';
import Image from 'next/image';
import { ArrowRight, ArrowUpRight } from 'lucide-react';
import { getCategories, getProducts } from '@/lib/api';
import { footerInformation, footerServices, mapHref, publicContact } from '@/lib/public-site';
import { FashionHeader } from './fashion-header';
import { MobileNavigation } from './mobile-navigation';

export async function Header() {
  const [categories, products] = await Promise.all([getCategories(), getProducts()]);
  const active = products.ok ? products.data.data : [];
  const collections = categories.ok ? categories.data.data.map(category => ({ name: category.name, subcategories: [...new Set(active.filter(product => product.category === category.name).map(product => product.sub_category).filter((name): name is string => !!name))] })) : [];
  return <FashionHeader collections={collections} />;
}

export function Footer() {
  return <>
    <footer className="footer">
      <div className="container footer-main">
        <div className="footer-contact"><Link href="/" aria-label="Vanthara home"><Image src="/vantara-logo.png" alt="Vanthara" width={98} height={32} /></Link><p className="footer-brand-description">A considered point of view.<br />Discover the Vanthara collection.</p><address>{publicContact.address}</address><a href={mapHref} target="_blank" rel="noopener noreferrer" className="footer-direction">GET DIRECTION <ArrowUpRight size={14} /></a><a href={`mailto:${publicContact.email}`}>{publicContact.email}</a><a href={`tel:${publicContact.telephone}`}>{publicContact.phone}</a>
          <div className="footer-socials" aria-label="Social profiles">{['facebook', 'twitter', 'instagram', 'tiktok', 'pinterest'].map(network => <span key={network} className="footer-social-unavailable" role="img" aria-label={`${network}: official profile link unavailable`} title={`Official ${network} profile link has not been published`}><Image src={`/social/${network}.svg`} alt="" aria-hidden="true" width={14} height={14} /></span>)}</div>
        </div>
        <nav aria-label="Footer shopping"><h2>The collection</h2><Link href="/products?sort=newest">New arrivals</Link><Link href="/products">Shop all pieces</Link><Link href="/categories">Collections</Link></nav>
        <nav aria-label="Footer information"><h2>Information</h2>{footerInformation.map(link => <Link key={link.href} href={link.href}>{link.label}</Link>)}</nav>
        <nav aria-label="Footer customer services"><h2>Customer Services</h2>{footerServices.map(link => <Link key={link.href} href={link.href}>{link.label}</Link>)}</nav>
        <section className="footer-subscribe" aria-labelledby="footer-subscribe-title"><h2 id="footer-subscribe-title">Sign up for your purchase</h2><div className="footer-email-control"><input type="email" placeholder="Enter your e-mail" aria-label="Newsletter email address" aria-describedby="newsletter-availability" disabled /><button type="button" aria-label="Newsletter subscription unavailable" disabled><ArrowRight size={21} /></button></div><p id="newsletter-availability" className="footer-availability">Newsletter sign-up is currently unavailable.</p><p className="footer-consent"><input type="checkbox" aria-label="Agree to subscription terms" disabled /><span>By clicking subscribe, you agree to the <Link href="/terms-conditions">Terms of Service</Link> and <Link href="/privacy-policy">Privacy Policy</Link>.</span></p></section>
      </div>
      <div className="container footer-bottom"><span>©{new Date().getFullYear()} Vanthara. All Rights Reserved.</span><span>INR ₹ <span aria-hidden="true"> · </span> English</span></div>
    </footer>
    <MobileNavigation />
  </>;
}
