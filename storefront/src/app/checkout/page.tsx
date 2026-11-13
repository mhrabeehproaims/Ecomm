import { CustomerScreen } from '@/components/customer-screen';
export const metadata = { title: 'Checkout', robots: { index: false, follow: false } };
export default function Page() { return <CustomerScreen kind="checkout" />; }
