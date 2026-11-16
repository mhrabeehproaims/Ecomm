'use client';

import { useEffect, useId, useRef, useState } from 'react';
import { ChevronLeft, ChevronRight, Maximize2, X } from 'lucide-react';
import { Media } from './media';

export function ProductGallery({ images, name }: { images: string[]; name: string }) {
  const [selected, setSelected] = useState(0);
  const [expanded, setExpanded] = useState(false);
  const swipe = useRef<{ x: number; y: number } | null>(null);
  const swiped = useRef(false);
  const dialog = useRef<HTMLDialogElement>(null);
  const viewerId = useId();
  const index = Math.min(selected, Math.max(0, images.length - 1));
  const move = (direction: number) => {
    if (images.length < 2) return;
    setSelected(current => (Math.min(current, images.length - 1) + direction + images.length) % images.length);
  };
  useEffect(() => {
    if (!expanded) return;
    const viewer = dialog.current;
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    viewer?.showModal();
    return () => {
      viewer?.close();
      document.body.style.overflow = previousOverflow;
    };
  }, [expanded]);
  const imageAlt = `${name}${images.length > 1 ? `, view ${index + 1} of ${images.length}` : ''}`;
  return <section className="product-gallery" aria-label={`${name} images`}>
    <div className="gallery-main" onPointerDown={event => { swipe.current = { x: event.clientX, y: event.clientY }; swiped.current = false; }} onPointerCancel={() => { swipe.current = null; }} onPointerUp={event => { const start = swipe.current; swipe.current = null; if (!start) return; const dx = event.clientX - start.x, dy = event.clientY - start.y; if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) { swiped.current = true; move(dx < 0 ? 1 : -1); } }}>
      {images.length ? <button type="button" className="gallery-enlarge" aria-label={`Enlarge ${name} image`} aria-haspopup="dialog" aria-controls={viewerId} onClick={() => { if (!swiped.current) setExpanded(true); swiped.current = false; }}>
        <Media src={images[index]} alt={imageAlt} priority sizes="(max-width: 767px) calc(100vw - 36px), (max-width: 1359px) 48vw, 608px" />
        <span className="gallery-enlarge-hint"><Maximize2 size={16} aria-hidden="true" /> Enlarge</span>
      </button> : <Media src={null} alt={imageAlt} />}
      {images.length > 1 && <div className="gallery-controls"><button type="button" onClick={() => move(-1)} aria-label="Previous product image"><ChevronLeft size={19} /></button><span aria-live="polite" aria-atomic="true">{index + 1} / {images.length}</span><button type="button" onClick={() => move(1)} aria-label="Next product image"><ChevronRight size={19} /></button></div>}
    </div>
    {images.length > 1 && <div className="gallery-thumbnails" aria-label="Choose a product image">{images.map((src, i) => <button type="button" key={src} aria-label={`Show ${name}, view ${i + 1}`} aria-pressed={index === i} onClick={() => setSelected(i)}><Media src={src} alt="" sizes="80px" /></button>)}</div>}
    <dialog ref={dialog} id={viewerId} className="image-viewer" aria-labelledby={`${viewerId}-title`} onClose={() => setExpanded(false)} onClick={event => { if (event.target === event.currentTarget) setExpanded(false); }} onKeyDown={event => {
      if (event.key === 'Tab') {
        const buttons = event.currentTarget.querySelectorAll<HTMLButtonElement>('button:not([disabled])');
        const first = buttons[0], last = buttons[buttons.length - 1];
        if (event.shiftKey && document.activeElement === first) {
          event.preventDefault();
          last?.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault();
          first?.focus();
        }
      }
      if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
        event.preventDefault();
        move(event.key === 'ArrowLeft' ? -1 : 1);
      }
    }}>
      <div className="image-viewer-panel">
        <div className="image-viewer-header"><h2 id={`${viewerId}-title`}>{name}</h2><button type="button" autoFocus aria-label="Close enlarged image" onClick={() => setExpanded(false)}><X size={24} /></button></div>
        <div className="image-viewer-stage">{expanded && <Media src={images[index] || null} alt={imageAlt} sizes="(max-width: 767px) 100vw, 1200px" />}</div>
        {images.length > 1 && <div className="image-viewer-controls"><button type="button" aria-label="Previous enlarged image" onClick={() => move(-1)}><ChevronLeft size={22} /></button><span aria-live="polite" aria-atomic="true">{index + 1} / {images.length}</span><button type="button" aria-label="Next enlarged image" onClick={() => move(1)}><ChevronRight size={22} /></button></div>}
      </div>
    </dialog>
  </section>;
}
