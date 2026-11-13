'use client';

import { useState } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Media } from './media';

export function ProductGallery({ images, name }: { images: string[]; name: string }) {
  const [selected, setSelected] = useState(0);
  const index = Math.min(selected, Math.max(0, images.length - 1));
  const move = (direction: number) => setSelected((index + direction + images.length) % images.length);
  return <section className="product-gallery" aria-label={`${name} images`}>
    <div className="gallery-main">
      <Media src={images[index] || null} alt={`${name}${images.length > 1 ? `, view ${index + 1} of ${images.length}` : ''}`} priority sizes="(max-width: 767px) calc(100vw - 36px), (max-width: 1359px) 48vw, 608px" />
      {images.length > 1 && <div className="gallery-controls"><button type="button" onClick={() => move(-1)} aria-label="Previous product image"><ChevronLeft size={19} /></button><span aria-live="polite" aria-atomic="true">{index + 1} / {images.length}</span><button type="button" onClick={() => move(1)} aria-label="Next product image"><ChevronRight size={19} /></button></div>}
    </div>
    {images.length > 1 && <div className="gallery-thumbnails" aria-label="Choose a product image">{images.map((src, i) => <button type="button" key={src} aria-label={`Show ${name}, view ${i + 1}`} aria-pressed={index === i} onClick={() => setSelected(i)}><Media src={src} alt="" sizes="80px" /></button>)}</div>}
  </section>;
}
