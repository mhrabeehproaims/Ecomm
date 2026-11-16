'use client';

import { type ReactNode, useEffect, useId, useRef } from 'react';
import { X } from 'lucide-react';

export function Modal({ open, onClose, title, children, variant = 'center' }: { open: boolean; onClose: () => void; title: string; children: ReactNode; variant?: 'center' | 'left' | 'right' | 'search' }) {
  const ref = useRef<HTMLDialogElement>(null);
  const id = useId();
  useEffect(() => {
    if (!open) return;
    const dialog = ref.current;
    const overflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    dialog?.showModal();
    dialog?.querySelector<HTMLElement>('[data-modal-initial-focus]')?.focus();
    return () => { dialog?.close(); document.body.style.overflow = overflow; };
  }, [open]);
  return <dialog ref={ref} className={`fashion-dialog fashion-dialog--${variant}`} aria-labelledby={id} onCancel={event => { event.preventDefault(); onClose(); }} onClose={onClose} onClick={event => { if (event.target === event.currentTarget) onClose(); }} onKeyDown={event => {
    if (event.key === 'Escape') { event.preventDefault(); event.stopPropagation(); onClose(); return; }
    if (event.key !== 'Tab') return;
    const controls = [...event.currentTarget.querySelectorAll<HTMLElement>('button:not([disabled]),a[href],input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex="0"]')].filter(element => element.getClientRects().length);
    const first = controls[0], last = controls[controls.length - 1];
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
  }}><div className="fashion-dialog-panel"><header className="fashion-dialog-heading"><h2 id={id}>{title}</h2><button type="button" className="icon-button" autoFocus aria-label={`Close ${title.toLowerCase()}`} onClick={onClose}><X size={22} /></button></header>{open && children}</div></dialog>;
}
