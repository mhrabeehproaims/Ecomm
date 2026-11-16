'use client';

import { useRef, useState } from 'react';
import { businessTypes } from '@/lib/wholesale-contracts';

export function WholesaleForm() {
  const [busy, setBusy] = useState(false);
  const [feedback, setFeedback] = useState<{ message: string; success: boolean } | null>(null);
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const notice = useRef<HTMLDivElement>(null);
  const fields = [
    { name: 'name', label: 'Full Name', placeholder: 'Enter your name', type: 'text', autoComplete: 'name', maxLength: 100 },
    { name: 'email', label: 'Email Address', placeholder: 'you@example.com', type: 'email', autoComplete: 'email', maxLength: 100 },
    { name: 'phone', label: 'Phone Number', placeholder: '10–15 digits', type: 'tel', autoComplete: 'tel', maxLength: 16 },
    { name: 'company_name', label: 'Company Name', placeholder: 'Your company or business name', type: 'text', autoComplete: 'organization', maxLength: 255 },
  ];
  const errorFor = (name: string) => errors[name]?.length ? <span className="wholesale-field-error" id={`wholesale-${name}-error`}>{errors[name].join(' ')}</span> : null;
  return <form className="wholesale-form" aria-label="Wholesale enquiry" aria-busy={busy} onSubmit={async event => {
    event.preventDefault();
    if (busy) return;
    const form = event.currentTarget;
    setBusy(true); setFeedback(null); setErrors({});
    try {
      const response = await fetch('/api/wholesale-enquiry', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(Object.fromEntries(new FormData(form).entries())),
      });
      const body = await response.json();
      if (!response.ok) {
        if (body.errors && typeof body.errors === 'object') setErrors(body.errors);
        setFeedback({ message: body.message || 'Please check your enquiry and try again.', success: false });
      } else {
        form.reset();
        setFeedback({ message: body.message, success: true });
      }
    } catch {
      setFeedback({ message: 'We could not submit your enquiry. Please check your connection and try again.', success: false });
    } finally {
      setBusy(false);
      requestAnimationFrame(() => notice.current?.focus());
    }
  }}>
    <fieldset disabled={busy}>
      {fields.map(({ label, ...field }) => <label key={field.name} htmlFor={`wholesale-${field.name}`}><span>{label} <span className="wholesale-required" aria-hidden="true">*</span></span>
        <input {...field} id={`wholesale-${field.name}`} required pattern={field.name === 'phone' ? '[+]?[0-9]{10,15}' : undefined} inputMode={field.name === 'phone' ? 'tel' : undefined} aria-invalid={!!errors[field.name]} aria-describedby={errors[field.name] ? `wholesale-${field.name}-error` : undefined} />
        {errorFor(field.name)}
      </label>)}
      <label htmlFor="wholesale-business_type"><span>Type of Business <span className="wholesale-required" aria-hidden="true">*</span></span>
        <select id="wholesale-business_type" name="business_type" required defaultValue="" aria-invalid={!!errors.business_type} aria-describedby={errors.business_type ? 'wholesale-business_type-error' : undefined}>
          <option value="" disabled>Select your business type</option>{businessTypes.map(type => <option key={type}>{type}</option>)}
        </select>{errorFor('business_type')}
      </label>
      <label htmlFor="wholesale-message"><span>Message <span className="wholesale-required" aria-hidden="true">*</span></span>
        <textarea id="wholesale-message" name="message" rows={5} required maxLength={5000} placeholder="Tell us about your requirements" aria-invalid={!!errors.message} aria-describedby={errors.message ? 'wholesale-message-error' : undefined} />{errorFor('message')}
      </label>
    </fieldset>
    {feedback && <div ref={notice} tabIndex={-1} role={feedback.success ? 'status' : 'alert'} className={feedback.success ? 'form-success' : 'form-error'}>{feedback.message}</div>}
    <button className="button wholesale-submit" type="submit" disabled={busy}>{busy ? 'Submitting…' : 'Submit Enquiry'}</button>
  </form>;
}
