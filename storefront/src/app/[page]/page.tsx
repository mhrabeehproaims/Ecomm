import { notFound, permanentRedirect } from 'next/navigation';
import { WholesaleForm } from '@/components/wholesale-form';
import { AboutInformation, ContactInformation, FrequentlyAskedQuestions, PublicReturnInformation, PublishedPolicy, StoriesInformation } from '@/components/public-information';
import { publicPageAlias, publicPageAliases } from '@/lib/public-site';

const pages: Record<string, { title: string; description: string }> = {
  about: { title: 'About Us', description: 'Vanthara — where nature, tradition, and craft unite.' },
  stories: { title: 'Our Stories', description: 'Stories from the world of Vanthara.' },
  contact: { title: 'Contact Us', description: 'Get in touch with Vanthara in Old Vythiri, Wayanad.' },
  faq: { title: 'FAQ', description: 'Answers to frequently asked questions about Vanthara.' },
  shipping: { title: 'Shipping Policy', description: 'Order processing, shipping, delivery and tracking information.' },
  policy: { title: 'Return Policy', description: 'Return and cancellation policies currently published by Vanthara.' },
  'privacy-policy': { title: 'Privacy Policy', description: 'How Vanthara collects, uses and safeguards personal information.' },
  'terms-conditions': { title: 'Terms & Conditions', description: 'Terms and conditions for the Vanthara website and services.' },
  wholesale: { title: 'Wholesale Enquiry', description: 'Fill out the form below and our team will get back to you shortly.' },
  'wholesale-enquiry': { title: 'Wholesale Enquiry', description: 'Fill out the form below and our team will get back to you shortly.' },
};
export function generateStaticParams() { return [...Object.keys(pages), ...Object.keys(publicPageAliases)].map(page => ({ page })); }
export async function generateMetadata({ params }: { params: Promise<{ page: string }> }) {
  const { page } = await params;
  const canonical = publicPageAlias(page) || (page === 'wholesale' ? '/wholesale-enquiry' : `/${page}`);
  const item = Object.hasOwn(pages, canonical.slice(1)) ? pages[canonical.slice(1)] : undefined;
  return item ? { ...item, alternates: { canonical }, robots: { index: false, follow: true } } : {};
}
export default async function InformationPage({ params }: { params: Promise<{ page: string }> }) {
  const { page } = await params;
  const alias = publicPageAlias(page);
  if (alias) permanentRedirect(alias);
  if (!Object.hasOwn(pages, page)) notFound();
  if (page === 'about') return <AboutInformation />;
  if (page === 'stories') return <StoriesInformation />;
  if (page === 'contact') return <ContactInformation />;
  if (page === 'faq') return <FrequentlyAskedQuestions />;
  if (page === 'policy') return <PublicReturnInformation />;
  if (page === 'shipping' || page === 'privacy-policy' || page === 'terms-conditions') return <PublishedPolicy kind={page} />;
  return <div className="wholesale-page"><header className="wholesale-heading"><h1>Wholesale Enquiry</h1><p>{pages[page].description}</p></header><WholesaleForm /></div>;
}
