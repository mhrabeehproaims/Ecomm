import { z } from 'zod';

export const contactInput = z.object({
  name: z.string().trim().min(1, 'Enter your name.').max(100),
  email: z.email('Enter a valid email address.').max(150),
  phone: z.string().trim().regex(/^[+]?[0-9]{7,15}$/, 'Enter 7–15 digits, with an optional + prefix.'),
  message: z.string().trim().min(1, 'Enter your message.').max(1000),
});
