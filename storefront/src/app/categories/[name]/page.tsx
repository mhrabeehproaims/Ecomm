import { CatalogView } from '@/components/catalog-view';
import { notFound } from 'next/navigation';
import { decodedSegment } from '@/lib/route-segment';

export async function generateMetadata({ params }: { params: Promise<{ name: string }> }) {
  const name = decodedSegment((await params).name);
  if (name === null) notFound();
  return { title: `${name} collection`, alternates: { canonical: `/categories/${encodeURIComponent(name)}` } };
}
export default async function CategoryPage({ params }: { params: Promise<{ name: string }> }) {
  const name = decodedSegment((await params).name);
  if (name === null) notFound();
  return <CatalogView params={{ category: name }} />;
}
