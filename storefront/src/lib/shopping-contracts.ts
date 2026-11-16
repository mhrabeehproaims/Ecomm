import type { Product } from './contracts';
import type { CartItem, Customer } from './customer-contracts';

export type ShoppingSummary = { signedIn: boolean; cartCount: number; wishlist: string[] };
export type ShoppingSnapshot = { customer: Customer; items: CartItem[]; products: Product[]; summary: ShoppingSummary };
