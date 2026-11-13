import { getReturnPolicies } from '@/lib/api';
import { ReturnPolicies } from '@/components/return-policies';

export const metadata = {
  title: 'Returns & refunds',
  description: 'Read the return policies currently published by Vanthara.',
  alternates: { canonical: '/returns' },
};

export default async function ReturnsPage() {
  const result = await getReturnPolicies();
  return <div className="container information-page returns-page">
    <p className="eyebrow">Before you order</p>
    <h1>Returns & refunds.</h1>
    <p className="information-lead">Current return information from Vanthara.</p>
    <p>Eligibility depends on the product and applicable policy. For an existing order, please use your existing Vanthara support channel. Online return requests are not available in this storefront yet.</p>
    <ReturnPolicies result={result} />
  </div>;
}
