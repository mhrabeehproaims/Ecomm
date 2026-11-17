'use client';

import Image from 'next/image';
import Link from 'next/link';
import { ArrowLeft, ArrowRight, ArrowUpRight } from 'lucide-react';
import { useEffect, useState, type CSSProperties } from 'react';
import { safeBannerLink } from '@/lib/catalog';

export type HeroSlide = { id: string; image: string; title?: string | null; subtitle?: string | null; buttonText?: string | null; buttonLink?: string | null; buttonColor?: string | null; buttonTextColor?: string | null; buttonFontSize?: string | null; buttonShape?: string | null; buttonPositionType?: string | null; presetPosition?: string | null; buttonX?: number | string | null; buttonY?: number | string | null; remote?: boolean };

function color(value?: string | null, fallback = 'var(--plum)') { return value && /^#[0-9a-f]{3,8}$/i.test(value.trim()) ? value.trim() : fallback; }
function buttonPosition(slide: HeroSlide): CSSProperties {
  if (!slide.remote || slide.buttonPositionType !== 'custom' && !slide.presetPosition) return {};
  const preset = (slide.presetPosition || '').toLowerCase().replace(/[-_]+/g, ' ').trim();
  const positions: Record<string, [string, string]> = { 'top left': ['12%', '18%'], 'top center': ['50%', '18%'], 'top right': ['88%', '18%'], left: ['12%', '50%'], center: ['50%', '50%'], right: ['88%', '50%'], 'bottom left': ['12%', '78%'], 'bottom center': ['50%', '78%'], 'bottom right': ['88%', '78%'], top: ['50%', '18%'], bottom: ['50%', '78%'] };
  const [left, top] = slide.buttonPositionType === 'custom' ? [`${Number(slide.buttonX) || 50}%`, `${Number(slide.buttonY) || 50}%`] : (positions[preset] || positions.center);
  return { position: 'absolute', left, top, transform: 'translate(-50%, -50%)', margin: 0, backgroundColor: color(slide.buttonColor), color: color(slide.buttonTextColor, '#fff'), fontSize: slide.buttonFontSize && /^\d+(?:\.\d+)?px$/.test(slide.buttonFontSize) ? slide.buttonFontSize : undefined, borderRadius: slide.buttonShape === 'pill' ? '999px' : slide.buttonShape === 'square' ? '0' : undefined };
}

export function FashionHeroCarousel({ slides }: { slides: HeroSlide[] }) {
  const [active, setActive] = useState(0);
  const count = slides.length;
  useEffect(() => {
    if (count < 2) return;
    const timer = window.setInterval(() => setActive(value => (value + 1) % count), 6500);
    return () => window.clearInterval(timer);
  }, [count]);
  if (!count) return null;
  const slide = slides[active] || slides[0];
  const configuredButtonStyle = buttonPosition(slide);
  return <section className={`fashion-campaign fashion-hero-carousel ${slide.remote ? 'fashion-campaign--admin' : ''}`} aria-labelledby="campaign-title">
    <div className="fashion-campaign-image"><div className="fashion-hero-track" style={{ transform: `translateX(-${active * 100}%)` }}>{slides.map(item => <div className="fashion-hero-slide" key={item.id}>{item.remote ? <img src={item.image} alt={item.title || 'The Vanthara collection'} /> : <Image src={item.image} alt={item.title || 'The Vanthara editorial collection'} fill sizes="100vw" />}</div>)}</div></div>
    <div className="container fashion-campaign-inner"><div className="fashion-campaign-copy"><p className="eyebrow">The wardrobe edit</p><h1 id="campaign-title">{slide.title || <>An everyday<br /><em>point of view.</em></>}</h1><p>{slide.subtitle || 'A fresh perspective on getting dressed. Discover the Vanthara collection.'}</p><Link className="button button-primary" style={configuredButtonStyle} href={slide.buttonLink ? safeBannerLink(slide.buttonLink) : '/products'}>{slide.buttonText || 'Shop the collection'}<ArrowUpRight size={16} /></Link></div><span className="campaign-signature">VANTHARA / THE EDIT</span></div>
    {count > 1 && <div className="fashion-hero-controls"><button type="button" aria-label="Previous slide" onClick={() => setActive(value => (value - 1 + count) % count)}><ArrowLeft size={16} /></button><div className="fashion-hero-dots">{slides.map((item, index) => <button type="button" key={item.id} aria-label={`Show slide ${index + 1}`} aria-current={index === active} onClick={() => setActive(index)} />)}</div><button type="button" aria-label="Next slide" onClick={() => setActive(value => (value + 1) % count)}><ArrowRight size={16} /></button></div>}
  </section>;
}
