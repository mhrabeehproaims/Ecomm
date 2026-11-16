import { PublicReturnInformation } from '@/components/public-information';

export const metadata = {
  title: 'Returns & refunds',
  description: 'Read the return policies currently published by Vanthara.',
  alternates: { canonical: '/policy' },
};

export default async function ReturnsPage() {
  return <PublicReturnInformation />;
}
