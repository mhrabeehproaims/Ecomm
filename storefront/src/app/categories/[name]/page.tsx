import { CatalogView } from '@/components/catalog-view';

export async function generateMetadata({ params }: { params: Promise<{ name: string }> }) {
  const { name } = await params;
  return { title: `${name} collection`, alternates: { canonical: `/categories/${encodeURIComponent(name)}` } };
}
export default async function CategoryPage({ params }: { params: Promise<{ name: string }> }) {
  const { name } = await params;
  return <CatalogView params={{ category: name }} />;
}
