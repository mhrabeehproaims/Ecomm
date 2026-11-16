'use client';

import { useEffect, useRef, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { Search, ArrowUpRight } from 'lucide-react';

type Suggestion = { name: string; href: string };
export function SearchBox({ autoFocus = false, onNavigate }: { autoFocus?: boolean; onNavigate?: () => void } = {}) {
  const router = useRouter();
  const [query, setQuery] = useState('');
  const [open, setOpen] = useState(false);
  const [items, setItems] = useState<Suggestion[]>([]);
  const [status, setStatus] = useState<'idle' | 'loading' | 'ready' | 'error'>('idle');
  const [active, setActive] = useState(-1);
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
  return <div ref={box} className="search-box" onBlur={e => { if (!e.currentTarget.contains(e.relatedTarget)) setOpen(false); }} onKeyDown={e => { if (e.key === 'Escape') { setOpen(false); setActive(-1); } }}>
    <form action="/products" role="search" className="search-form" onSubmit={event => { event.preventDefault(); setOpen(false); router.push(`/products?${new URLSearchParams({ keyword: query.trim() })}`); onNavigate?.(); }}>
      <Search size={19} aria-hidden="true" />
      <label className="sr-only" htmlFor="site-search">Search the Vanthara catalog</label>
      <input id="site-search" type="search" role="combobox" name="keyword" placeholder="Search the collection" data-modal-initial-focus={autoFocus || undefined} autoComplete="off" maxLength={120} value={query} onFocus={() => setOpen(true)} onChange={e => { setQuery(e.target.value); setItems([]); setActive(-1); setStatus('idle'); setOpen(true); }} aria-autocomplete="list" aria-haspopup="listbox" aria-expanded={visible} aria-controls={visible && items.length ? 'search-suggestions' : undefined} aria-activedescendant={visible && active >= 0 ? `search-option-${active}` : undefined} onKeyDown={e => {
        if ((e.key === 'ArrowDown' || e.key === 'ArrowUp') && items.length) {
          e.preventDefault(); setOpen(true);
          setActive(index => (index + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length);
        } else if (e.key === 'Enter' && visible && active >= 0 && items[active]) {
          e.preventDefault(); setOpen(false); onNavigate?.(); router.push(items[active].href);
        }
      }} />
      <button type="submit" aria-label="Search products"><ArrowUpRight size={18} /></button>
    </form>
    {visible && <div className="search-results" id="search-results" aria-label="Search suggestions">
      <p className="eyebrow">Search the collection</p>
      <div role="status">{status === 'idle' || status === 'loading' ? <p>Finding your next discovery…</p> : status === 'error' ? <p>Suggestions are unavailable. You can still submit your search.</p> : items.length === 0 ? <p>No matching products yet.</p> : null}</div>
      {status === 'ready' && items.length > 0 && <ul role="listbox" id="search-suggestions" aria-label="Matching products">{items.map((item, index) => <li id={`search-option-${index}`} role="option" aria-selected={active === index} key={item.href} onMouseDown={e => e.preventDefault()} onClick={() => { setOpen(false); onNavigate?.(); router.push(item.href); }}><span>{item.name}</span><ArrowUpRight size={15} aria-hidden="true" /></li>)}</ul>}
      <Link className="search-all" href={`/products?keyword=${encodeURIComponent(query)}`} onClick={() => { setOpen(false); onNavigate?.(); }}>See all results <span aria-hidden="true">→</span></Link>
    </div>}
  </div>;
}
