import type { z } from 'zod';
import type { ApiResult } from './catalog';
import { catalogRevalidateSeconds, catalogTag } from './catalog-cache';

export async function requestCatalog<T>(origin: string, path: string, schema: z.ZodType<T>, fresh = false): Promise<ApiResult<T>> {
  try {
    const response = await fetch(`${origin}/api/${path}`, {
      headers: { Accept: 'application/json' },
      signal: AbortSignal.timeout(6000),
      ...(fresh ? { cache: 'no-store' as const } : { next: { revalidate: catalogRevalidateSeconds, tags: [catalogTag] } }),
      redirect: 'error',
    });
    if (!response.ok) return { ok: false, kind: response.status === 404 ? 'not-found' : 'unavailable' };
    const parsed = schema.safeParse(await response.json());
    if (!parsed.success) return { ok: false, kind: 'invalid' };
    return { ok: true, data: parsed.data };
  } catch { return { ok: false, kind: 'unavailable' }; }
}
