import { createCipheriv, createDecipheriv, createHash, randomBytes } from 'node:crypto';

export const sessionCookie = 'vanthara_session';
export const sessionLifetime = 8 * 60 * 60;

function key(secret: string) {
  if (secret.length < 32) throw new Error('Session secret must contain at least 32 characters.');
  return createHash('sha256').update(secret).digest();
}
export function sealSession(token: string, secret: string, now = Date.now()) {
  const iv = randomBytes(12);
  const cipher = createCipheriv('aes-256-gcm', key(secret), iv);
  const encrypted = Buffer.concat([cipher.update(JSON.stringify({ token, expires: now + sessionLifetime * 1000 }), 'utf8'), cipher.final()]);
  return Buffer.concat([iv, cipher.getAuthTag(), encrypted]).toString('base64url');
}
export function openSession(value: string | undefined, secret: string, now = Date.now()): string | null {
  if (!value || value.length > 4096) return null;
  try {
    const bytes = Buffer.from(value, 'base64url');
    const decipher = createDecipheriv('aes-256-gcm', key(secret), bytes.subarray(0, 12));
    decipher.setAuthTag(bytes.subarray(12, 28));
    const json = JSON.parse(Buffer.concat([decipher.update(bytes.subarray(28)), decipher.final()]).toString('utf8'));
    return typeof json.token === 'string' && json.expires > now ? json.token : null;
  } catch { return null; }
}
