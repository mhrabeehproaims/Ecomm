'use client';

import { useEffect, useRef, useState } from 'react';
import Link from 'next/link';
import { Search, ArrowUpRight } from 'lucide-react';

type Suggestion = { name: string; href: string };
export function SearchBox() {
  const [query, setQuery] = useState('');
  const [open, setOpen] = useState(false);
  const [items, setItems] = useState<Suggestion[]>([]);
  const [status, setStatus] = useState<'idle' | 'loading' | 'ready' | 'error'>('idle');
  const box = useRef<HTMLDivElement>(null);
  useEffect(() => {
    if (query.trim().length < 2) return;
    const controller = new AbortController();
    const timer = setTimeout(async () => {
      setStatus('loading');
      try {
        const response = await fetch(`/api/search?q=${encodeURIComponent(query.trim())}`, { signal: controller.signal });
        if (!response.ok) throw new Error('Search unavailable');
        const payload = await response.json() as { items: Suggestion[] };
        setItems(payload.items);
        setStatus('ready');
      } catch { if (!controller.signal.aborted) setStatus('error'); }
    }, 300);
    return () => { clearTimeout(timer); controller.abort(); };
  }, [query]);
  useEffect(() => {
    function close(event: PointerEvent) { if (!box.current?.contains(event.target as Node)) setOpen(false); }
    document.addEventListener('pointerdown', close);
    return () => document.removeEventListener('pointerdown', close);
  }, []);
  const visible = open && query.trim().length >= 2;
  return <div ref={box} className="search-box" onBlur={e => { if (!e.currentTarget.contains(e.relatedTarget)) setOpen(false); }} onKeyDown={e => { if (e.key === 'Escape') setOpen(false); }}>
    <form action="/products" role="search" className="search-form" onSubmit={() => setOpen(false)}>
      <Search size={19} aria-hidden="true" />
      <label className="sr-only" htmlFor="site-search">Search the Vantara catalog</label>
      <input id="site-search" type="search" name="keyword" placeholder="Find something for your everyday…" autoComplete="off" maxLength={120} value={query} onFocus={() => setOpen(true)} onChange={e => { setQuery(e.target.value); setItems([]); setStatus('idle'); setOpen(true); }} aria-expanded={visible} aria-controls={visible ? 'search-results' : undefined} />
      <button type="submit" aria-label="Search products"><ArrowUpRight size={18} /></button>
    </form>
    {visible && <div className="search-results" id="search-results" aria-label="Search suggestions">
      <p className="eyebrow">Search the collection</p>
      <div role="status">{status === 'idle' || status === 'loading' ? <p>Finding your next discovery…</p> : status === 'error' ? <p>Suggestions are unavailable. You can still submit your search.</p> : items.length === 0 ? <p>No matching products yet.</p> : null}</div>
      {status === 'ready' && items.length > 0 && <ul>{items.map(item => <li key={item.href}><Link href={item.href} prefetch={false} onClick={() => setOpen(false)}>{item.name}<ArrowUpRight size={15} /></Link></li>)}</ul>}
      <Link className="search-all" href={`/products?keyword=${encodeURIComponent(query)}`} onClick={() => setOpen(false)}>See all results <span aria-hidden="true">→</span></Link>
    </div>}
  </div>;
}
