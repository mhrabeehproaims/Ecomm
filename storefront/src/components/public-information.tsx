import Image from 'next/image';
import Link from 'next/link';
import { ArrowUpRight, Mail, MapPin, Phone } from 'lucide-react';
import { publishedContent } from '@/content/vanthara-public';
import { getReturnPolicies } from '@/lib/api';
import { mapHref, publicContact } from '@/lib/public-site';
import { AboutTabs } from './about-tabs';
import { ContactForm } from './contact-form';
import { ReturnPolicies } from './return-policies';

export function AboutInformation() {
  return <div className="container public-page about-page"><h1>About Our Store</h1><div className="about-introduction"><div className="about-visual"><Image src="/hero.webp" alt="Vanthara, inspired by the nature and traditions of Wayanad" width={700} height={560} sizes="(max-width: 767px) 90vw, 40vw" /></div><div><h2>Vanthara– Where Nature, Tradition, and Craft Unite</h2><AboutTabs tabs={publishedContent.about} /></div></div>
    <div className="about-benefits">{publishedContent.benefits.map(benefit => <section key={benefit.title}><h3>{benefit.title}</h3><p>{benefit.body}</p></section>)}</div>
    <section className="about-reviews"><h2>Customer Review</h2><div>{publishedContent.reviews.map(review => <article key={review.name}><h3>{review.title}</h3><p>{review.body}</p><strong>{review.name}</strong><span className="review-stars" aria-label="5 out of 5 stars">★★★★★</span></article>)}</div></section>
  </div>;
}

export function PublishedPolicy({ kind }: { kind: keyof typeof publishedContent.sections }) {
  const title = kind === 'shipping' ? 'Shipping Policy' : kind === 'privacy-policy' ? 'Privacy Policy' : 'Terms & Conditions';
  return <div className="container public-page published-policy"><h1>{title}</h1><div className="published-policy-sections">{publishedContent.sections[kind].map(section => <section key={section.title}><header><h2>{section.title}</h2>{'estimate' in section && <p className="policy-estimate">Estimated Time: <strong>{section.estimate}</strong></p>}</header><div className="published-policy-copy">{section.body.split('\n\n').map((block, index) => {
    const lines = block.split('\n');
    return lines.every(line => line.startsWith('• ')) ? <ul key={index}>{lines.map(line => <li key={line}>{line.slice(2)}</li>)}</ul> : <p key={index}>{block}</p>;
  })}</div></section>)}</div></div>;
}

export function FrequentlyAskedQuestions() {
  return <div className="container public-page faq-page"><h1>FAQ</h1><section className="faq-list"><h2>How To Buy</h2>{publishedContent.faq.map(faq => <details key={faq.question}><summary>{faq.question}</summary><p>{faq.answer.replace('support@Vanthara.in', publicContact.email)}</p></details>)}</section></div>;
}

export function ContactInformation() {
  return <div className="container public-page contact-page"><h1>Enquiry</h1><div className="contact-information-grid"><div className="contact-map"><MapPin size={35} aria-hidden="true" /><h2>Visit Vanthara</h2><p>{publicContact.address}</p><a href={mapHref} className="text-link" target="_blank" rel="noopener noreferrer">GET DIRECTION <ArrowUpRight size={16} /></a></div><section className="contact-information"><h2>Information</h2><p><strong>Phone:</strong> <a href={`tel:${publicContact.telephone}`}><Phone size={16} aria-hidden="true" />{publicContact.phone}</a></p><p><strong>Email:</strong> <a href={`mailto:${publicContact.email}`}><Mail size={16} aria-hidden="true" />{publicContact.email}</a></p><p><strong>Address:</strong><br />{publicContact.address}</p><p><strong>Open Time:</strong><br />{publicContact.hours}<br />Sunday: Closed</p></section></div>
    <section className="contact-message"><h2>Get In Touch</h2><p>Use the form below to get in touch with the sales team</p><ContactForm /></section>
  </div>;
}

export async function PublicReturnInformation() {
  const result = await getReturnPolicies();
  return <div className="container public-page returns-page"><h1>Return Policy</h1><h2>Return &amp; Cancellation Policies</h2><ReturnPolicies result={result} /><p className="return-account-link">For an existing order, <Link href="/orders">view your orders</Link> or <Link href="/contact">contact us</Link>.</p></div>;
}

export function StoriesInformation() {
  return <div className="container public-page stories-page"><h1>Our Stories</h1><div role="status" className="stories-unavailable"><h2>New stories are on their way.</h2><p>There are no published stories available yet. Discover the nature, traditions and craftsmanship behind Vanthara.</p><Link className="button button-primary" href="/about">About Us <ArrowUpRight size={16} /></Link></div></div>;
}
