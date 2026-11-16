import { timingSafeEqual } from 'node:crypto';
import { revalidateTag } from 'next/cache';
import { NextRequest, NextResponse } from 'next/server';
import { catalogTag } from '@/lib/catalog-cache';

export async function POST(request: NextRequest) {
  const secret = process.env.STOREFRONT_CATALOG_REVALIDATE_SECRET || '';
  const supplied = Buffer.from(request.headers.get('authorization') || '');
  const expected = Buffer.from(`Bearer ${secret}`);
  if (secret.length < 32 || supplied.length !== expected.length || !timingSafeEqual(supplied, expected)) {
    return NextResponse.json({ message: 'Not authorized.' }, { status: 401, headers: { 'Cache-Control': 'no-store' } });
  }
  revalidateTag(catalogTag, { expire: 0 });
  return NextResponse.json({ revalidated: true }, { headers: { 'Cache-Control': 'no-store' } });
}
