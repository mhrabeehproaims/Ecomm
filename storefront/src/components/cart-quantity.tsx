'use client';
import { useEffect, useState } from 'react';
import { QuantityStepper } from './quantity-stepper';

export function CartQuantity({ name, quantity, stock, busy, save }: { name: string; quantity: number; stock: number; busy: boolean; save: (quantity: number) => void }) {
  const [draft, setDraft] = useState(String(quantity));
  useEffect(() => { setDraft(String(quantity)); }, [quantity]);
  return <form className="bag-quantity-form" onSubmit={e => { e.preventDefault(); if (!busy && Number(draft) !== quantity) save(Number(draft)); }}>
    <label>Quantity<QuantityStepper value={Math.max(1, Number(draft) || 1)} max={Math.min(stock, 99)} label={`Quantity for ${name}`} disabled={busy} onChange={value => setDraft(String(value))} /></label>
    <button type="submit" className="text-link quantity-update" disabled={busy || Number(draft) === quantity}>Update quantity</button>
  </form>;
}
