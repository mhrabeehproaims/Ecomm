'use client';

import { useState } from 'react';
import Image from 'next/image';
import { ImageIcon } from 'lucide-react';

export function Media({ src, alt, className = '', priority = false, sizes = '(max-width: 600px) 50vw, 25vw' }: { src: string | null; alt: string; className?: string; priority?: boolean; sizes?: string }) {
  const [failed, setFailed] = useState<string | null>(null);
  const [loaded, setLoaded] = useState<string | null>(null);
  const localBackend = src ? /^http:\/\//.test(src) || /^https:\/\/(localhost|127\.|10\.|192\.168\.|172\.(1[6-9]|2\d|3[01])\.|\[::1\])/.test(src) : false;
  return <div className={`media ${className}`} data-loading={Boolean(src && failed !== src && loaded !== src)}>
    {src && failed !== src ? <Image src={src} alt={alt} fill sizes={sizes} priority={priority} unoptimized={localBackend} className="object-cover" onLoad={() => setLoaded(src)} onError={() => setFailed(src)} /> : <span className="media-placeholder" role="img" aria-label={`${alt}: image unavailable`}><ImageIcon size={25} strokeWidth={1} aria-hidden="true" /><span>Image unavailable</span></span>}
  </div>;
}
