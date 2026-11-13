import { RefreshCw, FileText } from 'lucide-react';
import type { ApiResult } from '@/lib/catalog';
import { plainText } from '@/lib/catalog';
import type { ReturnPolicy } from '@/lib/contracts';

const returnTypes: Record<ReturnPolicy['return_type'], string> = {
  refund: 'Refund',
  exchange: 'Exchange',
  refund_or_exchange: 'Refund or exchange',
};

export function ReturnPolicies({ result }: { result: ApiResult<{ status: 'success'; data: ReturnPolicy[] }> }) {
  if (!result.ok) return <div className="catalog-state policy-state" role="alert">
    <RefreshCw size={25} strokeWidth={1.25} aria-hidden="true" />
    <h2>Return information is taking a moment.</h2>
    <p>We couldn’t load the current policies. Please try again before relying on return information.</p>
    <a href="/returns" className="text-link">Try again <RefreshCw size={14} /></a>
  </div>;

  if (!result.data.data.length) return <div className="catalog-state policy-state" role="status">
    <FileText size={25} strokeWidth={1.25} aria-hidden="true" />
    <h2>No return policies have been published yet.</h2>
    <p>Return windows, eligibility, refund timing and replacement conditions will appear here when available. No return window is promised by this notice.</p>
  </div>;

  return <div className="policy-list">{result.data.data.map(policy => <article className="policy-card" key={String(policy.policy_id)}>
    <h2>{policy.title}</h2>
    <dl className="specifications">
      <div><dt>Return window</dt><dd>{policy.return_days} {policy.return_days === 1 ? 'day' : 'days'}</dd></div>
      <div><dt>Return type</dt><dd>{returnTypes[policy.return_type]}</dd></div>
    </dl>
    {policy.conditions && <div className="policy-conditions"><h3>Conditions</h3><p>{plainText(policy.conditions)}</p></div>}
  </article>)}</div>;
}
