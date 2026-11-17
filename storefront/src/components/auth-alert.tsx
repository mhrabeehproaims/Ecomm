'use client';

import Link from 'next/link';
import { Modal } from './modal';

export function AuthAlert({ open, onClose }: { open: boolean; onClose: () => void }) {
  return <Modal open={open} onClose={onClose} title="Sign in required"><div className="auth-alert" role="alert"><p>Please login to add this item to your cart.</p><div className="auth-alert-actions"><Link className="button button-primary" href="/login?next=%2Fcart">Login</Link><button type="button" className="button button-outline" onClick={onClose}>Close</button></div></div></Modal>;
}
