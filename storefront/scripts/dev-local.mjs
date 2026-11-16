import fs from 'node:fs';
import path from 'node:path';
import net from 'node:net';
import { fileURLToPath } from 'node:url';
import { randomBytes } from 'node:crypto';
import { spawn } from 'node:child_process';

const storefront = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const root = path.resolve(storefront, '..');
const localEnv = fs.existsSync(path.join(storefront, '.env.local')) ? fs.readFileSync(path.join(storefront, '.env.local'), 'utf8') : '';
const configuredOrigin = localEnv.match(/^LARAVEL_API_ORIGIN\s*=\s*["']?([^\r\n"']+)/m)?.[1]?.trim();
const api = new URL(process.env.LARAVEL_API_ORIGIN || configuredOrigin || 'http://localhost:8000');
if (api.protocol !== 'http:' || !['127.0.0.1', 'localhost'].includes(api.hostname) || !api.port || api.username || api.password) {
  throw new Error('dev:local requires an explicit loopback HTTP API port; remote APIs are not started or substituted.');
}
const port = Number(process.env.VANTHARA_STOREFRONT_PORT || 3000);
if (!Number.isInteger(port) || port < 1024 || port > 65535 || port === Number(api.port)) throw new Error('Choose a distinct local storefront port.');

async function check() {
  const marker = await fetch(`${api.origin}/__storefront-test/identity`, { signal: AbortSignal.timeout(2000) }).catch(() => null);
  if (marker?.ok && (await marker.json().catch(() => null))?.environment === 'isolated-storefront-test') throw new Error('This is a sample test catalog, not the admin database.');
  const response = await fetch(`${api.origin}/api/api-products`, { signal: AbortSignal.timeout(8000), headers: { Accept: 'application/json' } });
  const body = await response.json().catch(() => null);
  if (!response.ok || body?.status !== 'success' || !Array.isArray(body.data)) throw new Error('The configured origin is not serving the Vanthara product API.');
  console.log(`Catalog API ready: ${api.origin} (${body.data.length} active products).`);
}
if (process.argv.includes('--check')) {
  try { await check(); } catch { console.error(`Catalog unavailable at ${api.origin}. Start the matching Laravel app with npm.cmd run dev:local, or verify your Laravel host. Do not substitute the fixture API.`); process.exitCode = 1; }
} else {
  const backendEnv = fs.readFileSync(path.join(root, '.env'), 'utf8').match(/^APP_ENV\s*=\s*["']?([^\r\n"']+)/m)?.[1]?.trim();
  if (process.env.STOREFRONT_TEST_RUN || (process.env.APP_ENV || backendEnv) !== 'local') throw new Error('dev:local requires local mode and no isolated-test overrides.');
  for (const candidate of [Number(api.port), port]) await new Promise((resolve, reject) => {
    const probe = net.createServer(); probe.once('error', () => reject(new Error(`Port ${candidate} is occupied. Existing servers were left untouched; stop/reconfigure them explicitly.`)));
    probe.listen(candidate, '127.0.0.1', () => probe.close(resolve));
  });
  const secret = randomBytes(32).toString('hex');
  const common = { ...process.env, STOREFRONT_CATALOG_REVALIDATE_SECRET: secret };
  const backend = spawn('php', ['-S', `127.0.0.1:${api.port}`, path.join(root, 'scripts/local-api-router.php')], {
    cwd: path.join(root, 'public'), windowsHide: true, stdio: 'inherit',
    env: { ...common, APP_CONFIG_CACHE: path.join(root, 'storage/framework/cache/local-catalog-uncached.php'), STOREFRONT_CATALOG_REVALIDATE_URL: `http://127.0.0.1:${port}/api/catalog/revalidate`, STOREFRONT_TEST_CHECKOUT: 'false', STOREFRONT_TEST_COD: 'false' },
  });
  let frontend;
  const stop = () => { backend.kill(); frontend?.kill(); };
  process.once('SIGINT', stop); process.once('SIGTERM', stop);
  backend.once('error', error => { console.error(error.message); process.exitCode = 1; stop(); });
  try {
    let ready = false;
    for (let attempt = 0; attempt < 20; attempt++) { try { await check(); ready = true; break; } catch { await new Promise(resolve => setTimeout(resolve, 500)); } }
    if (!ready) throw new Error('Laravel catalog did not become ready. Check Laragon PHP/PATH and local MySQL configuration.');
    frontend = spawn(process.execPath, [path.join(storefront, 'node_modules/next/dist/bin/next'), 'dev', '--hostname', '127.0.0.1', '--port', String(port)], {
      cwd: storefront, windowsHide: true, stdio: 'inherit', env: { ...common, LARAVEL_API_ORIGIN: api.origin, STOREFRONT_URL: `http://127.0.0.1:${port}`, STOREFRONT_SANDBOX_TRANSACTIONS: 'false' },
    });
    frontend.once('exit', code => { process.exitCode = code || 0; backend.kill(); });
    backend.once('exit', code => { if (code) process.exitCode = code; frontend?.kill(); });
  } catch (error) { console.error(error.message); process.exitCode = 1; stop(); }
}
