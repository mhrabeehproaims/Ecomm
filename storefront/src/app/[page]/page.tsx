import { notFound } from 'next/navigation';
import Link from 'next/link';
import { ArrowUpRight, Heart, ShoppingBag, UserRound, MessageCircle, Leaf } from 'lucide-react';

const content: Record<string, { title: string; eyebrow: string; description: string; body: string; phase?: boolean }> = {
  about: { title: 'Everyday, considered.', eyebrow: 'Meet Vanthara', description: 'A little more care in every choice.', body: 'Welcome to the Vanthara storefront. Explore our catalog, take a closer look at product details and discover what fits your everyday. Our full brand story will be shared here when approved.' },
  contact: { title: 'Let’s start a conversation.', eyebrow: 'Here to help', description: 'A place for your questions.', body: 'Our customer contact form and verified contact details will be available in the next phase. Please use your existing Vanthara support channel for current orders. This page does not submit an enquiry.' },
  wholesale: { title: 'Good things, on a bigger scale.', eyebrow: 'For your business', description: 'Interested in sourcing from Vanthara?', body: 'Explore the catalog to find products relevant to your business. Our wholesale enquiry form will be connected in the next phase. Minimum quantities, business pricing and delivery terms require confirmation from Vanthara.' },
  faq: { title: 'A little guidance.', eyebrow: 'Common questions', description: 'Helpful things to know while exploring.', body: 'You can browse the catalog, search products and use available filters. Product details and listed prices come from Vanthara’s catalog. Sign in to manage your profile, addresses, favourites and shopping bag, or view your order status. Online order placement remains unavailable while checkout safeguards are completed. Empty collections mean there are no published items available in that section.' },
  shipping: { title: 'Shipping information.', eyebrow: 'Before you order', description: 'Our approved delivery policy will live here.', body: 'Serviceable locations, delivery charges, dispatch estimates and tracking information are awaiting business approval. Online ordering is not active in this storefront. No delivery date or free-shipping promise is offered here.' },
  privacy: { title: 'Privacy policy.', eyebrow: 'Policy information', description: 'Awaiting approved business copy.', body: 'This storefront is in development. A complete privacy policy, including the data controller, lawful purposes, retention, service providers and customer rights, must be approved and published before customer accounts and transactions are enabled. This notice is not a final privacy policy.' },
  terms: { title: 'Terms & conditions.', eyebrow: 'Policy information', description: 'Awaiting approved business copy.', body: 'Final terms covering seller identity, purchase conditions, pricing, payments, delivery, returns and dispute resolution must be approved before this storefront accepts orders. This notice is not a final set of commercial terms.' },
};
export function generateStaticParams() { return Object.keys(content).map(page => ({ page })); }
export async function generateMetadata({ params }: { params: Promise<{ page: string }> }) {
  const item = content[(await params).page];
  return item ? { title: item.title, description: item.description, robots: { index: false, follow: true } } : {};
}
export default async function InformationPage({ params }: { params: Promise<{ page: string }> }) {
  const { page } = await params;
  const item = content[page];
  if (!item) notFound();
  const Icon = page === 'cart' ? ShoppingBag : page === 'wishlist' ? Heart : page === 'account' ? UserRound : page === 'contact' ? MessageCircle : Leaf;
  return <div className="container information-page"><div className="information-icon"><Icon size={32} strokeWidth={1.1} /></div><p className="eyebrow">{item.eyebrow}</p><h1>{item.title}</h1><p className="information-lead">{item.description}</p><p>{item.body}</p><Link className="button button-primary" href="/products">Explore the collection <ArrowUpRight size={17} /></Link>{item.phase && <Link className="text-link" href="/faq">About this storefront <ArrowUpRight size={15} /></Link>}</div>;
}
