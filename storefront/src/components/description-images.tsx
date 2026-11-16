'use client';

import { useState } from 'react';
import { Maximize2 } from 'lucide-react';
import { Modal } from './modal';

export function DescriptionImages({ images, name }: { images: string[]; name: string }) {
  const [expanded, setExpanded] = useState<number | null>(null);
  const [failed, setFailed] = useState<string[]>([]);
  return <>
    <div className="description-images">{images.map((src, index) => <figure key={src}>
      {failed.includes(src) ? <p className="description-image-unavailable" role="status">Description image {index + 1} is unavailable.</p> : <button type="button" className="description-image-button" aria-label={`Enlarge ${name} description image ${index + 1}`} aria-haspopup="dialog" onClick={() => setExpanded(index)}>
        <img src={src} alt={`${name} detailed description, image ${index + 1}`} loading="lazy" decoding="async" onError={() => setFailed(current => [...current, src])} />
        <span className="description-image-hint"><Maximize2 size={15} aria-hidden="true" />Enlarge</span>
      </button>}
    </figure>)}</div>
    <Modal open={expanded !== null} onClose={() => setExpanded(null)} title={`${name} — description image ${(expanded ?? 0) + 1}`}>
      {expanded !== null && <div className="description-image-preview"><img src={images[expanded]} alt={`${name} detailed description, image ${expanded + 1}`} /></div>}
    </Modal>
  </>;
}
