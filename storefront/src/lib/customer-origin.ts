// Keep production CSRF checks exact. Local development has two common loopback names.
export function customerOriginAllowed(origin: string | null, configuredOrigin: string, development: boolean): boolean {
  if (!origin) return false;
  if (origin === configuredOrigin) return true;
  if (!development) return false;
  try {
    const supplied = new URL(origin), configured = new URL(configuredOrigin);
    const loopback = new Set(['localhost', '127.0.0.1']);
    return supplied.origin === origin
      && loopback.has(supplied.hostname) && loopback.has(configured.hostname)
      && ['http:', 'https:'].includes(configured.protocol)
      && supplied.protocol === configured.protocol && supplied.port === configured.port;
  } catch { return false; }
}
