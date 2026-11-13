'use client';
import { RefreshCw } from 'lucide-react';
export default function ErrorPage({ reset }: { error: Error & { digest?: string }; reset: () => void }) { return <div className="container information-page"><RefreshCw size={30} /><h1>Let’s take that again.</h1><p>Something interrupted this page. Please try once more.</p><button className="button button-primary" onClick={reset}>Try again <RefreshCw size={16} /></button></div>; }
