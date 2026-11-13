import { CustomerScreen } from '@/components/customer-screen';
export const metadata = { title: 'Orders', robots: { index: false, follow: false } };
export default function Page() { return <CustomerScreen kind="orders" />; }
