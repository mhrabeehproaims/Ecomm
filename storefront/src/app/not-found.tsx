import Link from 'next/link';
import { ArrowUpRight } from 'lucide-react';
export default function NotFound() { return <div className="container information-page"><p className="eyebrow">A little detour</p><h1>This discovery isn’t here.</h1><p>The page or product may no longer be available. There’s more to explore in the collection.</p><Link className="button button-primary" href="/products">Back to the collection <ArrowUpRight size={17} /></Link></div>; }
