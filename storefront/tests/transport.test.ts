import assert from 'node:assert/strict';
import { createServer } from 'node:http';
import { after, before, test } from 'node:test';
import { envelope, productSchema } from '../src/lib/contracts';
import { requestCatalog } from '../src/lib/transport';

let origin = '';
const seen: string[] = [];
const server = createServer((req, res) => {
  seen.push(req.url || '');
  assert.equal(req.method, 'GET');
  assert.equal(req.headers.accept, 'application/json');
  assert.equal(req.headers.authorization, undefined);
  if (req.url === '/api/error') { res.writeHead(500); return res.end('private stack trace'); }
  if (req.url === '/api/redirect') { res.writeHead(302, { Location: '/api/empty' }); return res.end(); }
  if (req.url === '/api/missing') { res.writeHead(404); return res.end(); }
  res.setHeader('Content-Type', 'application/json');
  if (req.url === '/api/malformed') return res.end(JSON.stringify({ data: 'incorrect' }));
  if (req.url === '/api/html') return res.end('<html>Error</html>');
  res.end(JSON.stringify({ status: 'success', data: [] }));
});
before(async () => { await new Promise<void>(resolve => server.listen(0, '127.0.0.1', resolve)); const address = server.address(); assert.ok(address && typeof address !== 'string'); origin = `http://127.0.0.1:${address.port}`; });
after(async () => { await new Promise<void>((resolve, reject) => server.close(error => error ? reject(error) : resolve())); });

test('real HTTP transport accepts an empty collection', async () => { assert.deepEqual(await requestCatalog(origin, 'empty', envelope(productSchema)), { ok: true, data: { status: 'success', data: [] } }); });
test('transport fails visibly on backend HTTP failures and withholds server errors', async () => { assert.deepEqual(await requestCatalog(origin, 'error', envelope(productSchema)), { ok: false, kind: 'unavailable' }); });
test('transport detects invalid response contracts', async () => { assert.deepEqual(await requestCatalog(origin, 'malformed', envelope(productSchema)), { ok: false, kind: 'invalid' }); });
test('transport rejects redirects and non-JSON bodies', async () => { assert.deepEqual(await requestCatalog(origin, 'redirect', envelope(productSchema)), { ok: false, kind: 'unavailable' }); assert.deepEqual(await requestCatalog(origin, 'html', envelope(productSchema)), { ok: false, kind: 'unavailable' }); });
test('transport distinguishes not-found responses', async () => { assert.deepEqual(await requestCatalog(origin, 'missing', envelope(productSchema)), { ok: false, kind: 'not-found' }); });
test('encoded ciphertext stays intact in the request path', async () => { const id = 'opaque/with+symbols=='; await requestCatalog(origin, `api-products/${encodeURIComponent(id)}`, envelope(productSchema), true); assert.equal(seen.at(-1), `/api/api-products/${encodeURIComponent(id)}`); });
