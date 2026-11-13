import { CustomerScreen } from '@/components/customer-screen';
export const metadata = { title: 'Cart', robots: { index: false, follow: false } };
export default function Page() { return <CustomerScreen kind="cart" />; }
