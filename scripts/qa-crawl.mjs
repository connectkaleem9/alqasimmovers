#!/usr/bin/env node
/**
 * Crawls dist/ and checks what a launch QA pass needs:
 * broken internal links, missing assets, titles/descriptions, H1 count,
 * hreflang reciprocity, canonical presence, placeholders, image dimensions,
 * and the CSS rules from the design system (no raw hex, no left/right).
 * Zero dependencies. Exit code 1 on any error.
 */
import { readFile, readdir, stat } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const DIST = path.join(ROOT, 'dist');
const errors = [];
const warnings = [];

/* --allow-missing: while the site is still being built (Stages 5-8), links to pages
   that do not exist yet are warnings instead of errors. Gate 12 runs WITHOUT this flag. */
const ALLOW_MISSING = process.argv.includes('--allow-missing');
const missing = (msg) => (ALLOW_MISSING ? warnings : errors).push(msg);

async function walk(dir) {
  const out = [];
  if (!existsSync(dir)) return out;
  for (const e of await readdir(dir, { withFileTypes: true })) {
    const full = path.join(dir, e.name);
    if (e.isDirectory()) out.push(...await walk(full));
    else out.push(full);
  }
  return out;
}

const attr = (tag, name) => (tag.match(new RegExp(`${name}="([^"]*)"`, 'i')) || [])[1];

function resolveLocal(href) {
  if (/^(https?:|mailto:|tel:|#|data:)/i.test(href)) return null;
  const clean = href.split('#')[0].split('?')[0];
  if (!clean.startsWith('/')) return { unsupported: true, clean };
  const target = path.join(DIST, clean);
  if (clean.endsWith('/')) return { file: path.join(target, 'index.html'), clean };
  return { file: target, clean };
}

const run = async () => {
  if (!existsSync(DIST)) { console.error('dist/ not found — run `node scripts/build.mjs` first'); process.exit(1); }

  const files = (await walk(DIST)).filter((f) => f.endsWith('.html'));
  if (!files.length) errors.push('no HTML files in dist/');

  const titles = new Map();
  const descriptions = new Map();
  const pages = [];

  for (const file of files) {
    const rel = '/' + path.relative(DIST, file).replace(/\\/g, '/').replace(/index\.html$/, '');
    const html = await readFile(file, 'utf8');
    pages.push({ rel, html, file });

    const title = (html.match(/<title>([\s\S]*?)<\/title>/i) || [])[1]?.trim();
    const desc = attr(html.match(/<meta name="description"[^>]*>/i)?.[0] || '', 'content');
    const lang = attr(html.match(/<html[^>]*>/i)?.[0] || '', 'lang');
    const dir = attr(html.match(/<html[^>]*>/i)?.[0] || '', 'dir');
    const noindex = /<meta name="robots"[^>]*noindex/i.test(html);

    if (!title) errors.push(`${rel}: missing <title>`);
    if (!desc) errors.push(`${rel}: missing meta description`);
    if (!lang) errors.push(`${rel}: <html> has no lang`);
    if (!dir) errors.push(`${rel}: <html> has no dir`);
    if (lang === 'ar' && dir !== 'rtl') errors.push(`${rel}: Arabic page must have dir="rtl"`);

    const h1 = (html.match(/<h1[\s>]/gi) || []).length;
    if (h1 !== 1) errors.push(`${rel}: ${h1} <h1> elements (needs 1)`);

    if (!noindex && !/rel="canonical"\s+href="https?:\/\/[^"]+"/i.test(html)) {
      errors.push(`${rel}: missing or relative canonical`);
    }
    if (html.includes('[[OWNER-INPUT')) errors.push(`${rel}: OWNER-INPUT placeholder reached dist/`);
    if (/lorem ipsum|TODO:/i.test(html)) errors.push(`${rel}: placeholder text (lorem/TODO)`);

    if (title && !noindex) {
      const k = `${lang}|${title}`;
      if (titles.has(k)) errors.push(`duplicate title in ${lang}: ${rel} and ${titles.get(k)}`);
      else titles.set(k, rel);
    }
    if (desc && !noindex) {
      const k = `${lang}|${desc}`;
      if (descriptions.has(k)) errors.push(`duplicate description in ${lang}: ${rel} and ${descriptions.get(k)}`);
      else descriptions.set(k, rel);
    }

    // headings must not skip levels
    const levels = [...html.matchAll(/<h([1-4])[\s>]/gi)].map((m) => Number(m[1]));
    for (let i = 1; i < levels.length; i++) {
      if (levels[i] - levels[i - 1] > 1) { warnings.push(`${rel}: heading jumps h${levels[i - 1]} → h${levels[i]}`); break; }
    }

    // links and assets
    for (const tag of html.match(/<a\b[^>]*>/gi) || []) {
      const href = attr(tag, 'href');
      if (!href) { errors.push(`${rel}: <a> without href`); continue; }
      const r = resolveLocal(href);
      if (!r || r.unsupported) continue;
      if (!existsSync(r.file)) missing(`${rel}: link target not built yet → ${href}`);
      if (/^\/(?!$)/.test(r.clean) && !r.clean.endsWith('/') && !path.extname(r.clean)) {
        errors.push(`${rel}: internal link missing trailing slash → ${href}`);
      }
      if (/target="_blank"/i.test(tag) && !/rel="[^"]*noopener/i.test(tag)) {
        errors.push(`${rel}: target=_blank without rel=noopener → ${href}`);
      }
    }

    for (const tag of html.match(/<img\b[^>]*>/gi) || []) {
      const src = attr(tag, 'src');
      if (attr(tag, 'alt') === undefined) errors.push(`${rel}: <img> without alt → ${src}`);
      if (!attr(tag, 'width') || !attr(tag, 'height')) warnings.push(`${rel}: <img> without width/height → ${src}`);
      const r = src ? resolveLocal(src) : null;
      if (r && !r.unsupported && !existsSync(r.file)) errors.push(`${rel}: missing image → ${src}`);
    }

    for (const tag of [...(html.match(/<link\b[^>]*>/gi) || []), ...(html.match(/<script\b[^>]*>/gi) || [])]) {
      const url = attr(tag, 'href') || attr(tag, 'src');
      if (!url) continue;
      const r = resolveLocal(url);
      if (r && !r.unsupported && !existsSync(r.file)) errors.push(`${rel}: missing asset → ${url}`);
    }
  }

  // hreflang reciprocity
  for (const p of pages) {
    const alts = [...p.html.matchAll(/<link rel="alternate" hreflang="([^"]+)" href="([^"]+)"/g)]
      .filter((m) => m[1] !== 'x-default');
    for (const [, lang, href] of alts) {
      const localPath = href.replace(/^https?:\/\/[^/]+/, '');
      const other = pages.find((q) => q.rel === localPath);
      if (!other) { errors.push(`${p.rel}: hreflang ${lang} points at ${localPath}, which does not exist`); continue; }
      if (!other.html.includes(`href="${href.replace(localPath, p.rel === '/' ? '/' : p.rel)}"`) &&
          !other.html.includes(p.rel)) {
        errors.push(`${p.rel}: hreflang not reciprocal with ${other.rel}`);
      }
    }
  }

  // sitemap / robots
  const smPath = path.join(DIST, 'sitemap.xml');
  if (!existsSync(smPath)) errors.push('sitemap.xml missing');
  else {
    const sm = await readFile(smPath, 'utf8');
    for (const loc of [...sm.matchAll(/<loc>([^<]+)<\/loc>/g)].map((m) => m[1])) {
      const local = loc.replace(/^https?:\/\/[^/]+/, '');
      const f = path.join(DIST, local, 'index.html');
      if (!existsSync(f)) errors.push(`sitemap lists ${loc} but the file does not exist`);
      else if (/<meta name="robots"[^>]*noindex/i.test(await readFile(f, 'utf8'))) {
        errors.push(`sitemap lists a noindex page: ${loc}`);
      }
    }
  }
  if (!existsSync(path.join(DIST, 'robots.txt'))) errors.push('robots.txt missing');
  else {
    const robots = await readFile(path.join(DIST, 'robots.txt'), 'utf8');
    if (/Disallow:\s*\/\s*$/m.test(robots)) errors.push('robots.txt disallows the whole site');
    if (!/Sitemap:/i.test(robots)) errors.push('robots.txt has no Sitemap line');
  }

  // CSS discipline (design-system §10)
  const cssFiles = (await walk(path.join(ROOT, 'src', 'css')))
    .filter((f) => f.endsWith('.css') && !/variables\.css$/.test(f) && !/fonts\.css$/.test(f));
  for (const f of cssFiles) {
    const css = await readFile(f, 'utf8');
    const name = path.basename(f);
    for (const m of css.match(/#[0-9a-fA-F]{3,8}\b/g) || []) {
      if (name !== 'components.css' || !/^#0E6349$/.test(m)) warnings.push(`${name}: raw colour ${m} (use a token)`);
    }
    for (const m of css.match(/(?:^|[;{\s])(?:margin|padding|border|inset)?-?(?:left|right)\s*:/g) || []) {
      errors.push(`${name}: physical property "${m.trim()}" — use logical properties (RTL)`);
    }
  }

  // page weight
  for (const p of pages) {
    const size = (await stat(p.file)).size;
    if (size > 100 * 1024) warnings.push(`${p.rel}: HTML is ${(size / 1024).toFixed(0)} KB`);
  }

  console.log(`\nQA crawl: ${pages.length} pages checked`);
  if (warnings.length) { console.log(`\nWarnings (${warnings.length}):`); for (const w of [...new Set(warnings)]) console.log(`  ! ${w}`); }
  if (errors.length) {
    console.error(`\nQA FAILED — ${errors.length} error(s):`);
    for (const e of [...new Set(errors)]) console.error(`  ✗ ${e}`);
    process.exit(1);
  }
  console.log('\nQA OK\n');
};

run().catch((e) => { console.error(e); process.exit(1); });
