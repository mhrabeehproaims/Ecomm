import { NextRequest, NextResponse } from 'next/server';
import { getProducts } from '@/lib/api';
import { productHref } from '@/lib/catalog';

export async function GET(request: NextRequest) {
  const keyword = (request.nextUrl.searchParams.get('q') || '').trim().slice(0, 120);
  if (keyword.length < 2) return NextResponse.json({ items: [] });
  const result = await getProducts(keyword);
  if (!result.ok) return NextResponse.json({ message: 'Search is temporarily unavailable.' }, { status: 503, headers: { 'Cache-Control': 'no-store' } });
  return NextResponse.json({ items: result.data.data.slice(0, 5).map(p => ({ name: p.product_name, href: productHref(p) })) }, { headers: { 'Cache-Control': 'public, max-age=0, must-revalidate' } });
}
