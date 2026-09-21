#!/usr/bin/env node
/**
 * Publishes the built site to the `deploy` branch on GitHub, which Hostinger pulls
 * into public_html. Run it after any change you want to go live:
 *
 *     npm run release
 *
 * It refuses to publish a preview build (sample reviews) or a build with errors.
 */
import { execFileSync } from 'node:child_process';
import { readFile, writeFile, rm, mkdir, readdir, copyFile, stat } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import path from 'node:path';
import os from 'node:os';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const DIST = path.join(ROOT, 'dist');
const run = (cmd, args, cwd) =>
  execFileSync(cmd, args, { cwd, stdio: 'pipe', encoding: 'utf8' }).trim();

const step = (msg) => console.log(`\n▶ ${msg}`);

async function copyDir(from, to) {
  await mkdir(to, { recursive: true });
  for (const entry of await readdir(from, { withFileTypes: true })) {
    if (entry.name === '.git') continue;
    const src = path.join(from, entry.name);
    const dest = path.join(to, entry.name);
    if (entry.isDirectory()) await copyDir(src, dest);
    else await copyFile(src, dest);
  }
}

try {
  step('Building the site (production, no preview)');
  console.log(run('node', [path.join(ROOT, 'scripts', 'build.mjs')], ROOT));

  step('Checking the build');
  console.log(run('node', [path.join(ROOT, 'scripts', 'qa-crawl.mjs'), '--allow-missing'], ROOT));
  console.log(run('node', [path.join(ROOT, 'scripts', 'secret-scan.mjs')], ROOT));

  const home = await readFile(path.join(DIST, 'index.html'), 'utf8');
  if (home.includes('sample-banner') || home.includes('data-sample')) {
    throw new Error('this is a PREVIEW build with sample reviews — never release it');
  }
  if (home.includes('[[OWNER-INPUT')) throw new Error('placeholder text found in the build');

  step('Publishing to the deploy branch');
  const tmp = path.join(os.tmpdir(), 'alqasim-deploy-' + Date.now());
  const remote = run('git', ['remote', 'get-url', 'origin'], ROOT);
  run('git', ['clone', '--quiet', '--branch', 'deploy', '--single-branch', remote, tmp]);

  for (const entry of await readdir(tmp)) {
    if (entry !== '.git') await rm(path.join(tmp, entry), { recursive: true, force: true });
  }
  await copyDir(DIST, tmp);
  // uploads live only on the server; git must never track or remove them
  await writeFile(path.join(tmp, '.gitignore'), 'uploads/*\n!uploads/.htaccess\n', 'utf8');

  // the temp clone has no identity of its own — reuse the one from this repo
  const who = {
    name: run('git', ['config', 'user.name'], ROOT) || 'Al Qasim Movers',
    email: run('git', ['config', 'user.email'], ROOT) || 'noreply@alqasimmovers.com'
  };
  run('git', ['config', 'user.name', who.name], tmp);
  run('git', ['config', 'user.email', who.email], tmp);

  run('git', ['add', '-A'], tmp);
  const changed = run('git', ['status', '--porcelain'], tmp);
  if (!changed) {
    console.log('  nothing changed — the live site already matches this build');
  } else {
    const sourceCommit = run('git', ['rev-parse', '--short', 'HEAD'], ROOT);
    run('git', ['commit', '-q', '-m', `Built site from ${sourceCommit}`], tmp);
    run('git', ['push', '-q', 'origin', 'deploy'], tmp);
    console.log(`  pushed: ${changed.split('\n').length} file(s) changed`);
  }
  await rm(tmp, { recursive: true, force: true });

  const size = (await readdir(DIST)).length;
  console.log(`\n✓ Released. ${size} top-level entries in dist/.`);
  console.log('  Hostinger: hPanel → Git → Deploy (or wait for auto-deploy).\n');
} catch (e) {
  console.error(`\n✗ Release stopped: ${e.message}\n`);
  if (e.stdout) console.error(e.stdout.toString());
  process.exit(1);
}
