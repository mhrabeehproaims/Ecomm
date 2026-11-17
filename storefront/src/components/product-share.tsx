'use client';

import { Share2 } from 'lucide-react';
import { useState } from 'react';

export function ProductShare({ name, path }: { name: string; path: string }) {
  const [message, setMessage] = useState('');
  async function share() {
    const url = `${window.location.origin}${path}`;
    try {
      if (navigator.share) await navigator.share({ title: name, text: name, url });
      else {
        await navigator.clipboard.writeText(url);
        setMessage('Link copied');
        window.setTimeout(() => setMessage(''), 1800);
      }
    } catch (error) {
      if (error instanceof DOMException && error.name === 'AbortError') return;
      setMessage('Unable to share');
      window.setTimeout(() => setMessage(''), 1800);
    }
  }
  return <div className="product-share-control"><button type="button" className="icon-button product-share" aria-label={`Share ${name}`} title={`Share ${name}`} onClick={() => void share()}><Share2 size={17} strokeWidth={1.5} aria-hidden="true" /></button>{message && <span className="product-share-message" role="status">{message}</span>}</div>;
}
