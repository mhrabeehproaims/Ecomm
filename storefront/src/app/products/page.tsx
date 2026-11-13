import type { Metadata } from 'next';
import { CatalogView, type CatalogQuery, queryText } from '@/components/catalog-view';

export async function generateMetadata({ searchParams }: { searchParams: Promise<CatalogQuery> }): Promise<Metadata> {
  const params = await searchParams;
  const keyword = queryText(params, 'keyword');
  const category = queryText(params, 'category');
  const filtered = Object.values(params).some(Boolean);
  return { title: keyword ? `Search: ${keyword}` : category || 'Shop the collection', description: 'Explore products, compare details and discover the Vanthara collection.', ...(filtered ? { robots: { index: false, follow: true } } : { alternates: { canonical: '/products' } }) };
}
export default async function ProductsPage({ searchParams }: { searchParams: Promise<CatalogQuery> }) { return <CatalogView params={await searchParams} />; }
