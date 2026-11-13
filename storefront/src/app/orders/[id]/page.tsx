import { CustomerScreen } from '@/components/customer-screen';
import { notFound } from 'next/navigation';
export const metadata = { title: 'Order details', robots: { index: false, follow: false } };
export default async function Page({ params }: { params: Promise<{ id: string }> }) {
  const id = Number((await params).id);
  if (!Number.isSafeInteger(id) || id < 1) notFound();
  return <CustomerScreen kind="orders" orderId={id} />;
}
