#!/usr/bin/env node
/** Refuses to let credentials reach the repo or dist/. Run before every deploy. */
import { readFile, readdir } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const SKIP = new Set(['.git', 'node_modules', 'fonts']);
const BINARY = /\.(woff2?|png|jpe?g|webp|avif|ico|pdf|zip)$/i;

const PATTERNS = [
  [/AKIA[0-9A-Z]{16}/g, 'AWS access key id'],
  [/(?:secret|password|passwd|pwd|token|api[_-]?key)\s*[:=]\s*["'][^"'\s]{8,}["']/gi, 'hard-coded credential'],
  [/-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----/g, 'private key'],
  [/sk_live_[0-9a-zA-Z]{10,}/g, 'Stripe live key'],
  [/gh[pousr]_[0-9A-Za-z]{20,}/g, 'GitHub token'],
  [/AIza[0-9A-Za-z_-]{30,}/g, 'Google API key'],
  [/xox[baprs]-[0-9A-Za-z-]{10,}/g, 'Slack token'],
  [/postgres(?:ql)?:\/\/[^\s"']+:[^\s"']+@/g, 'database URL with password'],
  [/mysqli?:\/\/[^\s"']+:[^\s"']+@/g, 'database URL with password'],
  // PHP array style: 'password' => 'value'  /  "db_pass" => "value"
  [/["'](?:password|passwd|pwd|secret|api[_-]?key|token|ip_salt)["']\s*=>\s*["'][^"'\s]{8,}["']/gi, 'credential in a PHP config array']
];

/** Obvious placeholders in sample files are not secrets. */
const PLACEHOLDER = /REPLACE_ME|CHANGE_ME|YOUR_|EXAMPLE|xxxxx/i;

const findings = [];

async function walk(dir) {
  const out = [];
  for (const e of await readdir(dir, { withFileTypes: true })) {
    if (SKIP.has(e.name)) continue;
    const full = path.join(dir, e.name);
    if (e.isDirectory()) out.push(...await walk(full));
    else if (!BINARY.test(e.name)) out.push(full);
  }
  return out;
}

const run = async () => {
  for (const file of await walk(ROOT)) {
    const rel = path.relative(ROOT, file);
    if (rel.startsWith('scripts' + path.sep + 'secret-scan')) continue;   // this file holds the patterns
    let text;
    try { text = await readFile(file, 'utf8'); } catch { continue; }
    for (const [re, label] of PATTERNS) {
      const hits = (text.match(re) || []).filter((h) => !PLACEHOLDER.test(h));
      if (hits.length) findings.push(`${rel}: possible ${label} (${hits.length} match${hits.length > 1 ? 'es' : ''})`);
    }
  }
  for (const f of ['.env', '.env.local', '.env.production']) {
    if (existsSync(path.join(ROOT, f))) findings.push(`${f} exists — make sure it is git-ignored and never deployed`);
  }

  if (findings.length) {
    console.error(`\nSECRET SCAN FAILED — ${findings.length} finding(s):`);
    for (const f of findings) console.error(`  ✗ ${f}`);
    process.exit(1);
  }
  console.log('\nSecret scan clean\n');
};

run().catch((e) => { console.error(e); process.exit(1); });
