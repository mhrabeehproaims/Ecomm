import fs from 'node:fs/promises';
import path from 'node:path';
const root=path.resolve('..'), backup=path.join(root,'storage/framework/testing/backups/fashion-redesign-20261116');
const manifest=[];
async function walk(directory) { for(const entry of await fs.readdir(directory,{withFileTypes:true})) { const file=path.join(directory,entry.name); if(entry.isDirectory()) await walk(file); else {const relative=path.relative(process.cwd(),file).replaceAll('\\','/'); const current=await fs.readFile(file); let before; try{before=await fs.readFile(path.join(backup,relative));}catch{} if(!before || !current.equals(before)) manifest.push({path:'storefront/'+relative,change:before?'modified':'added'});}} }
for(const directory of ['src','public','tests']) await walk(path.resolve(directory));
for(const file of ['package.json','next.config.ts']) {if(!(await fs.readFile(file)).equals(await fs.readFile(path.join(backup,file)))) manifest.push({path:'storefront/'+file,change:'modified'});}
const additional=['storefront/README.md','storefront/.env.example','storefront/scripts/dev-local.mjs','storefront/scripts/smoke.mjs','storefront/scripts/fashion-performance.mjs','storefront/scripts/fashion-manifest.mjs','storefront/playwright.fashion.config.ts','storefront/playwright.transactions.config.ts','tests/Storefront/fashion-fixture.php','tests/Storefront/router.php','docs/STOREFRONT-FASHION-REDESIGN.md','docs/FASHION-DATA-PROPOSAL.md'];
for(const file of additional) if(!manifest.some(entry=>entry.path===file)) manifest.push({path:file,change:'task change; see report'});
manifest.sort((a,b)=>a.path.localeCompare(b.path));
await fs.writeFile('artifacts/fashion-redesign/modified-files.json',JSON.stringify(manifest,null,2));
console.log(manifest.length+' task files; '+manifest.filter(e=>/modal|catalog-filters|fashion-header/.test(e.path)).map(e=>e.path).join(', '));
