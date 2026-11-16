export function shoppingKey(product: { product_name: string; category?: string | null }): string {
  return `${product.category || ''}\u0000${product.product_name}`;
}
