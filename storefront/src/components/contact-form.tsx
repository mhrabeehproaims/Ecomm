'use client';

import { useRef, useState } from 'react';

export function ContactForm() {
  const [busy, setBusy] = useState(false);
  const [feedback, setFeedback] = useState<{ message: string; success: boolean } | null>(null);
  const notice = useRef<HTMLDivElement>(null);
  return <form className="customer-form contact-form" aria-label="Contact Vanthara" aria-busy={busy} onSubmit={async event => {
    event.preventDefault(); if (busy) return;
    const form = event.currentTarget;
    setBusy(true); setFeedback(null);
    try {
      const response = await fetch('/api/contact', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(Object.fromEntries(new FormData(form).entries())) });
      const body = await response.json();
      const details = body.errors ? Object.values(body.errors).flat().join(' ') : '';
      setFeedback({ message: details || body.message || 'Please try again.', success: response.ok });
      if (response.ok) form.reset();
    } catch { setFeedback({ message: 'We could not submit your message. Please check your connection and try again.', success: false }); }
    finally { setBusy(false); requestAnimationFrame(() => notice.current?.focus()); }
  }}>
    <fieldset disabled={busy}><div className="form-columns"><label>Your Name<input name="name" placeholder="Your Name*" autoComplete="name" required maxLength={100} /></label><label>Your Email<input name="email" type="email" placeholder="Your Email*" autoComplete="email" required maxLength={150} /></label></div>
      <label>Your Phone<input name="phone" type="tel" inputMode="tel" placeholder="Your Phone*" autoComplete="tel" pattern="[+]?[0-9]{7,15}" required maxLength={16} /></label>
      <label>Your Message<textarea name="message" rows={5} placeholder="Your Message*" required maxLength={1000} /></label>
    </fieldset>
    {feedback && <div ref={notice} tabIndex={-1} role={feedback.success ? 'status' : 'alert'} className={feedback.success ? 'form-success' : 'form-error'}>{feedback.message}</div>}
    <button type="submit" className="button button-primary" disabled={busy}>{busy ? 'Sending…' : 'Send Message'}</button>
  </form>;
}
