'use client';

export function QuantityStepper({ value, min = 1, max = 99, label, disabled = false, onChange }: { value: number; min?: number; max?: number; label: string; disabled?: boolean; onChange: (value: number) => void }) {
  return <div className="quantity-stepper" role="group" aria-label={label}><button type="button" aria-label={`Decrease ${label}`} disabled={disabled || value <= min} onClick={() => onChange(Math.max(min, value - 1))}>−</button><input aria-label={label} type="number" min={min} max={max} value={value} disabled={disabled} onChange={event => onChange(Math.min(max, Math.max(min, Number(event.target.value) || min)))} /><button type="button" aria-label={`Increase ${label}`} disabled={disabled || value >= max} onClick={() => onChange(Math.min(max, value + 1))}>+</button></div>;
}
