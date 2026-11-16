export const publicContact = {
  address: 'Old Vythiri, Vythiri, Kunnathidavaka, Kerala 673576',
  email: 'vantara@gmail.com',
  phone: '9074709600',
  telephone: '+919074709600',
  hours: 'Mon – Sat: 9:00am – 6:00pm IST',
};
export const mapHref = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(publicContact.address)}`;
export const footerInformation = [
  { label: 'About Us', href: '/about' },
  { label: 'Our Stories', href: '/stories' },
  { label: 'Contact Us', href: '/contact' },
  { label: 'FAQ', href: '/faq' },
  { label: 'My Account', href: '/account' },
];
export const footerServices = [
  { label: 'Shipping', href: '/shipping' },
  { label: 'Return & Refund', href: '/policy' },
  { label: 'Privacy Policy', href: '/privacy-policy' },
  { label: 'Terms & Conditions', href: '/terms-conditions' },
  { label: 'Wholesale Enquiry', href: '/wholesale-enquiry' },
];
export const publicPageAliases: Record<string, string> = {
  privacy: '/privacy-policy', terms: '/terms-conditions', 'terms-of-service': '/terms-conditions',
};
export function publicPageAlias(page: string): string | undefined {
  return Object.hasOwn(publicPageAliases, page) ? publicPageAliases[page] : undefined;
}
