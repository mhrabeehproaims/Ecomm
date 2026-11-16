'use client';
import { useEffect } from 'react';
import { usePathname, useRouter } from 'next/navigation';

// Refresh open catalog tabs as well as new navigations, without polling private customer pages.
export function CatalogFreshness() {
  const pathname = usePathname(), router = useRouter();
  useEffect(() => {
    if (pathname !== '/' && !/^\/(products|categories)(\/|$)/.test(pathname)) return;
    const refresh = () => { if (document.visibilityState === 'visible') router.refresh(); };
    const timer = window.setInterval(refresh, 30_000);
    window.addEventListener('focus', refresh);
    return () => { window.clearInterval(timer); window.removeEventListener('focus', refresh); };
  }, [pathname, router]);
  return null;
}
