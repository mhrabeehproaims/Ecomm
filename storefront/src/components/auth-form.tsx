'use client';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useState } from 'react';
import { customerApi, safeNextPath } from '@/lib/customer-client';

export function AuthForm({ register = false }: { register?: boolean }) {
  const router = useRouter();
  const [busy, setBusy] = useState(false), [message, setMessage] = useState('');
  return <div className="container auth-page"><div className="auth-story"><p className="eyebrow">Your Vanthara account</p><h1>{register ? 'Make yourself at home.' : 'Welcome back.'}</h1><p>Save your favourites, gather your everyday essentials and keep your orders in one place.</p></div><div className="auth-panel"><h2>{register ? 'Create an account' : 'Sign in'}</h2><form className="customer-form" onSubmit={async e => {
    e.preventDefault(); setBusy(true); setMessage('');
    const data = Object.fromEntries(new FormData(e.currentTarget).entries());
    try {
      if (register) { await customerApi('register', data); router.push('/login?registered=1'); }
      else { await customerApi('login', data); router.replace(safeNextPath(new URLSearchParams(window.location.search).get('next'))); router.refresh(); }
    } catch (error) { setMessage(error instanceof Error ? error.message : 'Please try again.'); } finally { setBusy(false); }
  }}>
    {register && <><label>First name<input name="first_name" autoComplete="given-name" maxLength={100} required /></label><label>Last name<input name="last_name" autoComplete="family-name" maxLength={100} /></label><label>Email (optional)<input name="email" type="email" autoComplete="email" /></label></>}
    <label>Mobile number<input name={register ? 'phone' : 'mobile'} type="tel" inputMode="numeric" pattern="[+]?[0-9]{7,15}" autoComplete="tel" required placeholder="7–15 digits, optional +" /></label>
    <label>Password<input name="password" type="password" minLength={register ? 8 : 1} maxLength={128} autoComplete={register ? 'new-password' : 'current-password'} required /></label>
    {register && <p className="form-help">Use at least 8 characters. Business terms and privacy information are available in the footer.</p>}
    {message && <p role="alert" className="form-error">{message}</p>}
    <button className="button button-primary" disabled={busy}>{busy ? 'Please wait…' : register ? 'Create account' : 'Sign in'}</button>
  </form><p className="auth-switch">{register ? 'Already have an account?' : 'New to Vanthara?'} <Link href={register ? '/login' : '/register'}>{register ? 'Sign in' : 'Create an account'}</Link></p></div></div>;
}
