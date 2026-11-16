// Read-only source/Git audit. Writes only sanitized evidence to ignored artifacts.
import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import { execFileSync } from 'node:child_process';

const root = path.resolve(import.meta.dirname, '../..');
process.chdir(root);
const output = 'storefront/artifacts/readiness-20261116';
fs.mkdirSync(output, { recursive: true });
const hash = value => crypto.createHash('sha256').update(value).digest('hex');
const git = args => execFileSync('git', args, { encoding: 'utf8', windowsHide: true });
const checkpoint = 'storage/framework/testing/backups/checkout-20261113-165534';
const originals = JSON.parse(fs.readFileSync(`${checkpoint}/manifest.json`, 'utf8').replace(/^\uFEFF/, ''));
const restore = JSON.parse(fs.readFileSync('storefront/artifacts/backend-implementation/formatting-restoration.json', 'utf8'));
const inventory = JSON.parse(fs.readFileSync('storefront/artifacts/backend-implementation/changes.json', 'utf8'));
const byPath = new Map(originals.map(file => [file.path.replaceAll('\\', '/'), file]));
const invalidBackups = originals.filter(file => hash(fs.readFileSync(path.join(checkpoint, file.path))) !== file.sha256.toLowerCase()).map(file => file.path);
const restoredDivergences = restore.restored.filter(file => hash(fs.readFileSync(file)) !== byPath.get(file.replaceAll('\\', '/')).sha256.toLowerCase());
const sourceDivergences = originals.filter(file => !fs.existsSync(file.path) || hash(fs.readFileSync(file.path)) !== file.sha256.toLowerCase()).map(file => file.path.replaceAll('\\', '/'));
const undocumentedDivergences = sourceDivergences.filter(file => !inventory.files.some(change => change.path === file));

// Compare executable tokens against the actual pre-incident Git commit. Preserve literal values.
// Normalize only the precisely identified unused imports and optional trailing commas.
const php = '$d=json_decode(stream_get_contents(STDIN),true);$out=[];foreach($d as $label=>$source){$r=[];foreach(token_get_all($source) as $v){if(is_array($v)){if(in_array($v[0],[T_WHITESPACE,T_COMMENT,T_DOC_COMMENT]))continue;$r[]=[$v[0],$v[1]];}else{$r[]=$v;}}$n=[];foreach($r as $i=>$v){if($v==="," && in_array($r[$i+1]??null,["]",")"]))continue;$n[]=$v;}$out[$label]=$n;}echo json_encode(["identicalExecutableTokens"=>$out["before"]===$out["after"]]);';
const seeders = [
  ['database/seeders/AdminTableSeeder.php', 'use Illuminate\\Database\\Console\\Seeds\\WithoutModelEvents;'],
  ['database/seeders/DatabaseSeeder.php', 'use App\\Models\\User;'],
].map(([file, unusedImport]) => {
  const before = git(['show', `0dda039:${file}`]);
  const after = fs.readFileSync(file, 'utf8');
  const comparison = JSON.parse(execFileSync('php', ['-r', php], { input: JSON.stringify({ before: before.replace(unusedImport, ''), after }), encoding: 'utf8', windowsHide: true }));
  return { file, baseline: '0dda039', baselineSha256: hash(before), currentSha256: hash(after), removedUnusedImport: unusedImport, ...comparison };
});
const tracked = git(['ls-files', '-z']).split('\0').filter(Boolean);
const status = git(['status', '--porcelain=v1', '--untracked-files=all']);
const requiredUntracked = inventory.files.filter(file => !tracked.includes(file.path)).map(file => file.path);
const evidence = {
  auditDate: '2026-11-16', head: git(['rev-parse', 'HEAD']).trim(),
  checkpoint, backupCopies: originals.length, invalidBackups,
  restoredFiles: restore.restored.length, restoredDivergences, undocumentedDivergences,
  seeders, requiredImplementationFiles: inventory.files.length, requiredUntracked,
  trackedEnvironmentFiles: tracked.filter(file => /(^|\/)\.env($|\.)/i.test(file) && !/example|sample/i.test(file)),
  trackedVendorFiles: tracked.filter(file => file.startsWith('vendor/')).length,
  trackedTestCache: tracked.includes('.phpunit.result.cache'), gitStatus: status.split('\n').filter(Boolean),
  sourceHashes: Object.fromEntries(inventory.files.map(file => [file.path, hash(fs.readFileSync(file.path))])),
  limitation: 'Evidence proves the captured baseline survived restoration. Work never captured by a checkpoint or Git cannot be reconstructed from this audit.',
};
fs.writeFileSync(`${output}/source-audit.json`, JSON.stringify(evidence, null, 2));
console.log(JSON.stringify({ backupCopies: evidence.backupCopies, invalidBackups, restoredFiles: evidence.restoredFiles, restoredDivergences, undocumentedDivergences, seeders: seeders.map(({ file, identicalExecutableTokens }) => ({ file, identicalExecutableTokens })), requiredUntracked, trackedEnvironmentFiles: evidence.trackedEnvironmentFiles, trackedVendorFiles: evidence.trackedVendorFiles }, null, 2));
if (invalidBackups.length || restoredDivergences.length || undocumentedDivergences.length || seeders.some(s => !s.identicalExecutableTokens)) process.exitCode = 1;
