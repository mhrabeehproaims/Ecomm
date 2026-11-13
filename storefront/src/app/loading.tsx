export default function Loading() {
  return <div className="container loading-page" role="status" aria-label="Loading collection"><span className="sr-only">Loading the Vanthara collection…</span><div className="skeleton skeleton-title" /><div className="skeleton skeleton-hero" /><div className="skeleton-grid">{[1,2,3,4].map(i => <div className="skeleton skeleton-card" key={i} />)}</div></div>;
}
