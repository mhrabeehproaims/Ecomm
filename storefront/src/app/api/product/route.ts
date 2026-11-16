import { NextRequest, NextResponse } from 'next/server';
import { getProduct } from '@/lib/api';

export async function GET(request: NextRequest) {
  const id = request.nextUrl.searchParams.get('id');
  const headers = { 'Cache-Control': 'no-store' };
  if (!id || id.length < 20 || id.length > 4096) return NextResponse.json({ message: 'Product not found.' }, { status: 404, headers });
  const result = await getProduct(id);
  if (!result.ok) return NextResponse.json({ message: result.kind === 'not-found' ? 'Product not found.' : 'This piece could not be loaded. Please try again.' }, { status: result.kind === 'not-found' ? 404 : 503, headers });
  if (!result.data[0]) return NextResponse.json({ message: 'Product not found.' }, { status: 404, headers });
  return NextResponse.json({ product: result.data[0] }, { headers });
}
