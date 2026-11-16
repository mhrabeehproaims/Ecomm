import { NextRequest, NextResponse } from 'next/server';
import { z } from 'zod';
import { siteOrigin } from '@/lib/config';
import { contactInput } from '@/lib/contact-contracts';
import { customerOriginAllowed } from '@/lib/customer-origin';
import { customerRequest, CustomerError } from '@/lib/customer-server';

const json = (body: unknown, status: number) => NextResponse.json(body, { status, headers: { 'Cache-Control': 'no-store' } });
export async function POST(request: NextRequest) {
  if (!customerOriginAllowed(request.headers.get('origin'), siteOrigin, process.env.NODE_ENV === 'development') || request.headers.get('content-type')?.split(';')[0].trim().toLowerCase() !== 'application/json') return json({ message: 'This request could not be verified.' }, 403);
  try {
    const text = await request.text();
    if (text.length > 8192) return json({ message: 'Please shorten your message.' }, 413);
    let body: unknown;
    try { body = JSON.parse(text); } catch { return json({ message: 'Please check your message.' }, 400); }
    const result = await customerRequest('customer-enquiry', null, 'POST', contactInput.parse(body));
    if (result.status !== 'success') return json({ message: 'Your message could not be confirmed. Please try again.' }, 502);
    return json({ message: 'Thank you. Your message has been submitted.' }, 201);
  } catch (error) {
    if (error instanceof z.ZodError) return json({ message: 'Please check the form.', errors: z.flattenError(error).fieldErrors }, 422);
    if (error instanceof CustomerError) return json({ message: error.message, errors: error.errors }, error.status);
    return json({ message: 'Your message could not be submitted. Please try again.' }, 500);
  }
}
