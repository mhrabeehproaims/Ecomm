import { CustomerScreen } from '@/components/customer-screen';
export const metadata = { title: 'Wishlist', robots: { index: false, follow: false } };
export default function Page() { return <CustomerScreen kind="wishlist" />; }
