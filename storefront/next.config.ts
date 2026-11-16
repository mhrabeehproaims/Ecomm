import type { NextConfig } from 'next';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const origin = new URL(process.env.LARAVEL_API_ORIGIN || 'http://localhost:8000');
const root = path.dirname(fileURLToPath(import.meta.url));
const config: NextConfig = {
  turbopack: { root },
  outputFileTracingRoot: root,
  poweredByHeader: false,
  devIndicators: false,
  images: {
    remotePatterns: [{ protocol: origin.protocol.slice(0, -1) as 'http' | 'https', hostname: origin.hostname, port: origin.port, pathname: '/storage/**' }],
    // Private Laravel media is intentionally served directly. Enabling the optimizer
    // for private networks would expand its SSRF surface. Public HTTPS media is optimized.
  },
  async headers() {
    return [{ source: '/(.*)', headers: [
      { key: 'X-Content-Type-Options', value: 'nosniff' },
      { key: 'Referrer-Policy', value: 'strict-origin-when-cross-origin' },
      { key: 'X-Frame-Options', value: 'DENY' },
      { key: 'Permissions-Policy', value: 'camera=(), microphone=(), geolocation=()' },
    ] }];
  },
};
export default config;
