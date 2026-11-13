// Review-only source inventory/diff against the pre-edit checkpoint; never applies changes.
import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import { spawnSync } from 'node:child_process';
const root = path.resolve(import.meta.dirname, '../..');
const backup = path.join(root, 'storage/framework/testing/backups/checkout-20261113-165534');
const output = path.join(root, 'storefront/artifacts/backend-implementation');
fs.mkdirSync(output, { recursive: true });
const raw = JSON.parse(fs.readFileSync(path.join(backup, 'manifest.json'), 'utf8').replace(/^\uFEFF/, ''));
const before = raw.flatMap(file => file.path ? [file] : file.value || []);
if (!before.every(file => typeof file.path === 'string' && typeof file.sha256 === 'string')) throw Error('Invalid backup manifest');
const originals = new Map(before.map(file => [file.path.replaceAll('\\', '/'), file.sha256.toLowerCase()]));
const files = [];
function walk(folder) { for (const entry of fs.readdirSync(path.join(root, folder), { withFileTypes: true })) {
  const relative = `${folder}/${entry.name}`;
  if (entry.isDirectory()) walk(relative); else files.push(relative);
} }
for (const dir of ['app', 'config', 'routes', 'database/migrations', 'resources/views', 'tests', 'storefront/src', 'storefront/tests', 'storefront/scripts', 'docs']) walk(dir);
files.push('storefront/README.md', 'storefront/package.json');
const incidental = ['database/seeders/AdminTableSeeder.php', 'database/seeders/DatabaseSeeder.php'];
files.push(...incidental);
const changes = [];
const empty = path.join(output, 'empty'); fs.writeFileSync(empty, '');
let fullDiff = '';
for (const file of [...new Set(files)].sort()) {
  const current = path.join(root, file), hash = crypto.createHash('sha256').update(fs.readFileSync(current)).digest('hex');
  if (originals.get(file) === hash) continue;
  if (incidental.includes(file)) {
    changes.push({ path: file, status: 'incidental-formatting', sha256: hash, diffAvailable: false,
      note: 'Pre-existing seeders formatted by the accidental broad Pint run. Neither was executed. No pre-format copy was captured.' });
    continue;
  }
  const saved = path.join(backup, file);
  const existed = fs.existsSync(saved);
  changes.push({ path: file, status: existed ? 'modified' : 'created', sha256: hash });
  const diff = spawnSync('git', ['diff', '--no-index', '--no-renames', '--', existed ? saved : empty, current], { encoding: 'utf8', windowsHide: true });
  if (![0, 1].includes(diff.status)) throw Error(diff.stderr || `Diff failed: ${file}`);
  fullDiff += diff.stdout.split('\n').map(line => line.startsWith('diff --git ') ? `diff --git a/${file} b/${file}` : line.startsWith('--- ') ? `--- ${existed ? `a/${file}` : '/dev/null'}` : line.startsWith('+++ ') ? `+++ b/${file}` : line).join('\n');
}
fs.writeFileSync(path.join(output, 'changes.json'), JSON.stringify({ backup, files: changes }, null, 2));
fs.writeFileSync(path.join(output, 'full.diff'), fullDiff);
console.log(`${changes.length} changed/new source and documentation files inventoried. Backup and full review diff retained.`);
