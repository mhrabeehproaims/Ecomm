import { getCategories } from '@/lib/api';
import { CatalogState, CategoryGrid } from '@/components/catalog';

export const metadata = { title: 'Explore categories', alternates: { canonical: '/categories' } };
export default async function CategoriesPage() {
  const result = await getCategories();
  return <div className="container catalog-page"><div className="page-heading"><p className="eyebrow">A place for every discovery</p><h1>Find your kind of everyday.</h1><p>Explore the collection, one category at a time.</p></div>{result.ok && result.data.data.length > 0 ? <CategoryGrid categories={result.data.data} /> : <CatalogState error={!result.ok} title={result.ok ? 'Our categories are coming together.' : undefined} description={result.ok ? 'There are no categories available yet. Explore the catalog or visit us again soon.' : undefined} />}</div>;
}
