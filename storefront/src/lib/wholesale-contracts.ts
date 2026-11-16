import { z } from 'zod';

export const businessTypes = ['Retailer', 'Distributor', 'Wholesaler', 'E-commerce Store', 'Reseller', 'Manufacturer', 'Other'] as const;
export const wholesaleInput = z.object({
  name: z.string().trim().min(1, 'Enter your full name.').max(100),
  email: z.email('Enter a valid email address.').max(100),
  phone: z.string().trim().regex(/^[+]?[0-9]{10,15}$/, 'Enter a phone number with 10–15 digits.'),
  company_name: z.string().trim().min(1, 'Enter your company name.').max(255),
  business_type: z.enum(businessTypes, { error: 'Select your business type.' }),
  message: z.string().trim().min(1, 'Tell us about your requirements.').max(5000),
});

export function wholesalePayload(input: z.infer<typeof wholesaleInput>) {
  const { business_type, ...payload } = input;
  // The existing Laravel/admin contract has no business_type column.
  // Preserve the selection in the message so staff receive all six form values.
  return { ...payload, message: `Type of business: ${business_type}\n\n${input.message}` };
}
