#!/usr/bin/env node
/**
 * Al Qasim Movers — static site build.
 * Zero dependencies (Node built-ins only). See docs/architecture/architecture.md (ADR-001, ADR-006).
 *
 * Input : src/pages/<lang>/<path>/index.html  (page meta in a leading <!--page {json} --> block)
 * Output: dist/<path>/index.html               (en → root, ar → /ar/…)
 *
 * Generates: head metadata, canonical, reciprocal hreflang, Open Graph, JSON-LD,
 *            breadcrumbs, sitemap.xml, robots.txt, and copies assets.
 * Fails on : unverified business facts, [[OWNER-INPUT placeholders, duplicate titles,
 *            missing description, multiple <h1>, broken hreflang pairs.
 */
import { readFile, writeFile, mkdir, readdir, copyFile, rm, stat } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import path from 'node:path';
import { createHash } from 'node:crypto';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const SRC = path.join(ROOT, 'src');
const DIST = path.join(ROOT, 'dist');
const LANGS = ['en', 'ar'];
const DEFAULT_LANG = 'en';

const errors = [];
const warnings = [];
const fail = (msg) => errors.push(msg);
const warn = (msg) => warnings.push(msg);

/* ---------------------------------------------------------------- helpers */

const readJSON = async (p) => JSON.parse(await readFile(p, 'utf8'));
const esc = (s = '') => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;')
  .replace(/>/g, '&gt;').replace(/"/g, '&quot;');

async function walk(dir) {
  const out = [];
  if (!existsSync(dir)) return out;
  for (const entry of await readdir(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) out.push(...await walk(full));
    else out.push(full);
  }
  return out;
}

async function copyDir(from, to) {
  if (!existsSync(from)) return 0;
  let n = 0;
  for (const file of await walk(from)) {
    const base = path.basename(file);
    // .gitkeep and editor dotfiles stay out of dist/, but real host config (.htaccess) must ship.
    if (base.startsWith('.') && base !== '.htaccess') continue;
    if (base.endsWith('.md')) continue;   // notes for us, not for the web server
    const rel = path.relative(from, file);
    const dest = path.join(to, rel);
    await mkdir(path.dirname(dest), { recursive: true });
    await copyFile(file, dest);
    n++;
  }
  return n;
}

/** Minimal, safe CSS minifier: strips comments and collapses whitespace. */
function minifyCSS(css) {
  return css
    .replace(/\/\*[\s\S]*?\*\//g, '')
    .replace(/\s+/g, ' ')
    // NOTE: never strip whitespace around + - > ~ : calc()/clamp() REQUIRE the spaces
    // around + and -, and stripping them silently invalidates the declaration.
    .replace(/\s*([{};,])\s*/g, '$1')
    .replace(/([a-zA-Z-]):\s+/g, '$1:')
    .replace(/;}/g, '}')
    .trim();
}

/* ------------------------------------------------- business fact resolution */

/**
 * Resolves a dotted path in business.json and refuses to output unverified facts.
 * `business.phone.international` → looks at business.phone, requires verified === true.
 */
function businessValue(business, dotted, { optional = false } = {}) {
  const parts = dotted.split('.');
  let node = business;
  let holder = null;
  for (const part of parts) {
    if (node == null || typeof node !== 'object') return undefined;
    if (Object.prototype.hasOwnProperty.call(node, 'verified')) holder = node;
    node = node[part];
  }
  if (node && typeof node === 'object' && Object.prototype.hasOwnProperty.call(node, 'value')) {
    holder = node;
    node = node.value;
  }
  // The verification check comes first: an unverified fact must fail the build even
  // when its value is still null, otherwise it would silently render as empty.
  if (holder && holder.verified !== true) {
    if (!optional) fail(`business.${dotted} is used in a template but is not verified in config/business.json`);
    return undefined;
  }
  if (node === undefined || node === null) {
    if (!optional) fail(`business.${dotted} is used in a template but has no value in config/business.json`);
    return undefined;
  }
  return node;
}

/* ------------------------------------------------------------ url helpers */

const urlPath = (lang, rel) => {
  const clean = rel.replace(/^\/+|\/+$/g, '');
  const base = lang === DEFAULT_LANG ? '' : `/${lang}`;
  return clean === '' ? `${base}/` : `${base}/${clean}/`;
};

/* ------------------------------------------------------------ page parsing */

function parsePage(raw, file) {
  const m = raw.match(/^\s*<!--page([\s\S]*?)-->/);
  if (!m) { fail(`${file}: missing <!--page {...} --> meta block`); return null; }
  let meta;
  try { meta = JSON.parse(m[1]); }
  catch (e) { fail(`${file}: page meta is not valid JSON — ${e.message}`); return null; }
  return { meta, body: raw.slice(m[0].length).trim() };
}

/* ----------------------------------------------------------------- schema */

function buildSchema(page, ctx) {
  const { business, domain, lang } = ctx;
  const graph = [];
  const orgId = `${domain}/#business`;

  const org = {
    '@type': 'MovingCompany',
    '@id': orgId,
    name: (lang === 'ar' && businessValue(business, 'nameArabic', { optional: true }))
      || businessValue(business, 'name'),
    url: domain + (lang === DEFAULT_LANG ? '/' : `/${lang}/`),
    telephone: businessValue(business, 'phone.e164'),
    image: `${domain}/images/logo/mark-512.png`,
    address: {
      '@type': 'PostalAddress',
      addressLocality: business.address.addressLocality,
      addressRegion: business.address.addressRegion,
      addressCountry: business.address.addressCountry
    },
    areaServed: [
      { '@type': 'City', name: 'Dubai' },
      ...(business.areasServed.otherEmirates?.verified
        ? (business.areasServed.otherEmirates.emirates || []).map((n) => ({ '@type': 'AdministrativeArea', name: n }))
        : [])
    ]
  };
  // Only emit optional properties once the owner has verified them.
  const street = businessValue(business, 'address.streetAddress', { optional: true });
  if (street) org.address.streetAddress = street;
  const email = businessValue(business, 'email', { optional: true });
  if (email) org.email = email;
  const sp = business.socialProfiles || {};
  const social = sp.verified ? [sp.facebook, sp.instagram].filter(Boolean) : [];
  if (social.length) org.sameAs = social;

  if (page.meta.type === 'home') {
    graph.push(org, {
      '@type': 'WebSite',
      '@id': `${domain}/#website-${lang}`,
      url: domain + (lang === DEFAULT_LANG ? '/' : `/${lang}/`),
      name: org.name,
      inLanguage: lang,
      publisher: { '@id': orgId }
    });
  } else {
    graph.push(org);
  }

  if (page.meta.type === 'service' && page.meta.serviceName) {
    graph.push({
      '@type': 'Service',
      name: page.meta.serviceName,
      serviceType: page.meta.serviceName,
      provider: { '@id': orgId },
      areaServed: { '@type': 'City', name: 'Dubai' },
      inLanguage: lang
    });
  }

  if (Array.isArray(page.meta.breadcrumbs) && page.meta.breadcrumbs.length) {
    graph.push({
      '@type': 'BreadcrumbList',
      itemListElement: page.meta.breadcrumbs.map((b, i) => ({
        '@type': 'ListItem',
        position: i + 1,
        name: b.name,
        item: domain + urlPath(lang, b.path)
      }))
    });
  }

  return `<script type="application/ld+json">${JSON.stringify({ '@context': 'https://schema.org', '@graph': graph })}</script>`;
}

/* ------------------------------------------------------------- reviews */

/**
 * Renders the reviews section from content/reviews.json (real customer reviews).
 * If there are none and PREVIEW=1 is set, sample cards are rendered for layout
 * preview only, with a visible warning banner. A normal build can never output
 * sample reviews — see .claude/rules/content-integrity-rules.md.
 */
function renderReviews(reviews, sample, t, lang, preview, business) {
  const stars = (n) => `<p class="review-card__stars" aria-label="${n} / 5">${'★'.repeat(n)}${'☆'.repeat(5 - n)}</p>`;
  const initials = (name) => (name || '?').trim().split(/\s+/).map((w) => w[0]).slice(0, 2).join('').toUpperCase();

  const card = (r) => `<article class="review-card">
        <div class="review-card__head">
          <span class="review-card__avatar" aria-hidden="true">${esc(initials(r.name))}</span>
          <div>
            ${stars(Number(r.rating) || 5)}
            <p class="review-card__who">${esc(r.name || '')}</p>
            <p class="review-card__where">${esc(r.area || '')}</p>
          </div>
        </div>
        <blockquote class="review-card__text">${esc((r.text && (r.text[lang] || r.text.en)) || '')}</blockquote>
      </article>`;

  if (reviews.length) {
    return `<div class="card-grid card-grid--3">\n      ${reviews.slice(0, 6).map(card).join('\n      ')}\n    </div>`;
  }
  if (preview && sample.length) {
    warn('PREVIEW build: sample review cards rendered — never deploy this output');
    return `<p class="sample-banner">${esc(t.reviews.sampleBanner)}</p>
    <div class="card-grid card-grid--3" data-sample="true">\n      ${sample.map(card).join('\n      ')}\n    </div>`;
  }
  // No written reviews yet, but the owner has confirmed the aggregate figures:
  // show those (true) with a way for customers to leave a review.
  const sp = business.socialProof || {};
  if (sp.verified === true && sp.customers && sp.rating) {
    return `<div class="review-summary">
      <div class="review-summary__score">
        <p class="review-summary__num">${esc(sp.rating)}</p>
        <p class="review-summary__stars" aria-label="${esc(sp.rating)} / 5">★★★★★</p>
        <p class="review-summary__label">${esc(t.reviews.avgLabel)}</p>
      </div>
      <div class="review-summary__people">
        <picture>
          <source type="image/webp" srcset="/images/hero/customer-avatars.webp">
          <img src="/images/hero/customer-avatars.png" alt="" width="390" height="109" loading="lazy" decoding="async">
        </picture>
        <p><b>${esc(sp.customers)}</b> ${esc(t.reviews.customersLabel)}</p>
      </div>
      <div class="review-summary__cta">
        <p>${esc(t.reviews.ctaText)}</p>
        <a class="btn btn--primary" href="${urlPath(lang, 'reviews')}#write-review">${esc(t.reviews.ctaButton)}</a>
      </div>
    </div>`;
  }
  return `<p class="empty-note">${esc(t.reviews.empty)}</p>`;
}

/* -------------------------------------------------------- social proof */

/**
 * "1,200+ Happy Customers · ★★★★★ (4.9 Rating)" strip for the hero.
 * Renders only when business.socialProof is verified. Under PREVIEW=1 it renders
 * the owner's proposed sample figures with a visible SAMPLE tag. A normal build
 * with unverified figures renders nothing — a customer count or rating must never
 * be invented (content-integrity-rules.md).
 */
function renderSocialProof(business, t, preview) {
  const sp = business.socialProof || {};
  let customers, rating, source, sample = false;
  if (sp.verified === true && sp.customers && sp.rating) {
    ({ customers, rating } = sp);
    source = sp.ratingSource || '';
  } else if (preview && sp.previewSample) {
    ({ customers, rating } = sp.previewSample);
    sample = true;
  } else {
    return '';
  }
  const avatars = `<picture>
          <source type="image/webp" srcset="/images/hero/customer-avatars.webp">
          <img src="/images/hero/customer-avatars.png" alt="" width="390" height="109" class="proof__avatars-img" decoding="async">
        </picture>`;
  return `<div class="proof"${sample ? ' data-sample="true"' : ''}>
        <div class="proof__avatars" aria-hidden="true">${avatars}</div>
        <div class="proof__text">
          <p class="proof__count">${esc(customers)} ${esc(t.proof.customers)}</p>
          <p class="proof__rating"><span class="proof__stars" aria-hidden="true">★★★★★</span> <span>(${esc(rating)} ${esc(t.proof.rating)}${source ? ' · ' + esc(source) : ''})</span></p>
        </div>${sample ? `\n        <span class="proof__sample">${esc(t.proof.sample)}</span>` : ''}
      </div>`;
}

/* -------------------------------------------------------------- rendering */

function renderTokens(tpl, ctx) {
  return tpl.replace(/\{\{\s*([^}]+?)\s*\}\}/g, (full, key) => {
    if (key.startsWith('>')) return full;                       // partial, handled elsewhere
    if (key.startsWith('url:')) return ctx.urls[key.slice(4)] ?? full;
    if (key.startsWith('nav:')) return ctx.navLists[key.slice(4)] ?? '';
    if (key.startsWith('block:')) return ctx.blocks[key.slice(6)] ?? '';
    if (key.startsWith('business.')) {
      const v = businessValue(ctx.business, key.slice(9));
      return v === undefined ? '' : esc(v);
    }
    if (key.startsWith('t.')) {
      const v = key.slice(2).split('.').reduce((o, k) => (o == null ? o : o[k]), ctx.t);
      if (v === undefined) { warn(`missing i18n string: ${key} (${ctx.lang})`); return ''; }
      return esc(String(v).replace('{phone}', businessValue(ctx.business, 'phone.international') || ''));
    }
    if (key.startsWith('legal.')) return esc(ctx.legal[key.slice(6)] ?? '');
    if (key.startsWith('page.')) return esc(key.slice(5).split('.').reduce((o, k) => (o == null ? o : o[k]), ctx.page) ?? '');
    if (key in ctx.scalars) return ctx.scalars[key];
    warn(`unknown token {{${key}}}`);
    return '';
  });
}

async function renderPartials(tpl, partials, ctx, depth = 0) {
  if (depth > 5) { fail('partial nesting too deep'); return tpl; }
  const out = tpl.replace(/\{\{>\s*([\w-]+)\s*\}\}/g, (full, name) => {
    if (!partials[name]) { fail(`unknown partial: ${name}`); return ''; }
    return partials[name];
  });
  return /\{\{>\s*[\w-]+\s*\}\}/.test(out) ? renderPartials(out, partials, ctx, depth + 1) : out;
}

/* -------------------------------------------------------------- the build */

async function build() {
  const business = await readJSON(path.join(ROOT, 'config', 'business.json'));
  const preview = process.env.PREVIEW === '1';
  const reviewsFile = await readJSON(path.join(ROOT, 'content', 'reviews.json'));
  const sampleFile = preview
    ? await readJSON(path.join(ROOT, 'content', 'reviews.sample.json'))
    : { reviews: [] };
  const domain = businessValue(business, 'domain')?.replace(/\/$/, '') || 'https://alqasimmovers.com';

  await rm(DIST, { recursive: true, force: true });
  await mkdir(DIST, { recursive: true });

  // 1. collect pages
  const pages = [];
  for (const lang of LANGS) {
    const dir = path.join(SRC, 'pages', lang);
    for (const file of await walk(dir)) {
      if (!file.endsWith('.html')) continue;
      const rel = path.relative(dir, file).replace(/\\/g, '/');
      const route = rel.replace(/index\.html$/, '').replace(/\.html$/, '');
      const raw = await readFile(file, 'utf8');
      const parsed = parsePage(raw, path.relative(ROOT, file));
      if (!parsed) continue;
      pages.push({ lang, route, file, ...parsed });
    }
  }
  if (!pages.length) fail('no pages found in src/pages/<lang>/');

  // 2. hreflang pairs (reciprocity comes from file presence, then is verified)
  const byRoute = new Map();
  for (const p of pages) {
    if (!byRoute.has(p.route)) byRoute.set(p.route, {});
    byRoute.get(p.route)[p.lang] = p;
  }
  for (const [route, langs] of byRoute) {
    const present = LANGS.filter((l) => langs[l]);
    if (present.length === 1 && present[0] === 'ar') {
      fail(`route "${route}" exists in Arabic only — every /ar/ page needs its English counterpart`);
    }
    if (present.length === 1 && present[0] === 'en') {
      warn(`route "${route}" has no Arabic version yet (allowed: Arabic ships when reviewed)`);
    }
  }

  // 2b. asset versions: a short content hash appended to CSS/JS URLs, so a changed
  //     file gets a new URL and browsers can never keep showing a stale copy
  //     (CSS is cached for 30 days by .htaccess).
  const cssSource = (await Promise.all(
    ['reset', 'variables', 'fonts', 'base', 'layout', 'components', 'utilities']
      .map((n) => readFile(path.join(SRC, 'css', `${n}.css`), 'utf8'))
  )).join('\n');
  const jsFiles = (await walk(path.join(SRC, 'js'))).filter((f) => f.endsWith('.js')).sort();
  const jsSource = (await Promise.all(jsFiles.map((f) => readFile(f, 'utf8')))).join('\n');
  const hash = (text) => createHash('sha1').update(text).digest('hex').slice(0, 10);
  const assetVersion = { css: hash(minifyCSS(cssSource)), js: hash(jsSource) };

  // 3. shared partials + i18n
  const partialFiles = await walk(path.join(SRC, 'partials'));
  const layoutRaw = await readFile(path.join(SRC, 'partials', 'layout.html'), 'utf8');
  const i18n = {};
  for (const lang of LANGS) i18n[lang] = await readJSON(path.join(SRC, 'i18n', `${lang}.json`));

  // 4. render each page
  const sitemap = [];
  const titles = new Map();

  for (const page of pages) {
    const { lang } = page;
    const t = i18n[lang];
    const other = LANGS.find((l) => l !== lang);
    const pair = byRoute.get(page.route);
    const canonical = domain + urlPath(lang, page.route);
    const indexable = page.meta.index !== false;
    page.meta.canonical = canonical;   // consumed by {{page.canonical}} in the layout

    const urls = {
      home: urlPath(lang, ''), services: urlPath(lang, 'services'), areas: urlPath(lang, 'areas'),
      blog: urlPath(lang, 'blog'), about: urlPath(lang, 'about'), contact: urlPath(lang, 'contact'),
      quote: urlPath(lang, 'get-a-quote'), faq: urlPath(lang, 'faq'),
      projects: urlPath(lang, 'projects'), reviews: urlPath(lang, 'reviews'),
      privacy: urlPath(lang, 'privacy-policy'), terms: urlPath(lang, 'terms-and-conditions'),
      cookies: urlPath(lang, 'cookie-policy')
    };

    const legal = lang === 'ar'
      ? { privacy: 'سياسة الخصوصية', terms: 'الشروط والأحكام', cookies: 'سياسة ملفات الارتباط' }
      : { privacy: 'Privacy Policy', terms: 'Terms & Conditions', cookies: 'Cookie Policy' };

    // footer nav lists are generated from the page inventory that actually exists
    const listFor = (prefix) => {
      const items = [...byRoute.keys()]
        .filter((r) => r.startsWith(`${prefix}/`) && r !== `${prefix}/`)
        .filter((r) => byRoute.get(r)[lang])
        .sort();
      if (!items.length) return '';
      return `<ul class="site-footer__list">${items.map((r) => {
        const p = byRoute.get(r)[lang];
        return `<li><a href="${urlPath(lang, r)}">${esc(p.meta.navTitle || p.meta.h1 || r)}</a></li>`;
      }).join('')}</ul>`;
    };

    const hreflang = Object.keys(pair).length > 1
      ? LANGS.filter((l) => pair[l]).map((l) =>
        `<link rel="alternate" hreflang="${l}" href="${domain + urlPath(l, page.route)}">`).join('\n') +
        `\n<link rel="alternate" hreflang="x-default" href="${domain + urlPath(DEFAULT_LANG, page.route)}">`
      : '';

    const ctx = {
      business, domain, lang, t, page: page.meta, urls, legal,
      navLists: { services: listFor('services'), areas: listFor('areas') },
      blocks: {
        reviews: renderReviews(reviewsFile.reviews || [], sampleFile.reviews || [], t, lang, preview, business),
        socialProof: renderSocialProof(business, t, preview)
      },
      scalars: {
        lang, dir: t.dir, home: urls.home, domain,
        altLang: other,
        altUrl: pair[other] ? urlPath(other, page.route) : urlPath(other, ''),
        ogLocale: lang === 'ar' ? 'ar_AE' : 'en_AE',
        bodyFont: lang === 'ar' ? 'tajawal-400.woff2' : 'poppins-400.woff2',
        year: String(new Date().getFullYear()),
        socialLinks: (() => {
          const sp = business.socialProfiles || {};
          if (!sp.verified) return '';
          return [['facebook', 'Facebook'], ['instagram', 'Instagram']]
            .filter(([k]) => sp[k])
            .map(([k, label]) => `<a class="topbar__social" href="${esc(sp[k])}" rel="noopener noreferrer" target="_blank" aria-label="${label}"><svg class="icon" aria-hidden="true" width="16" height="16"><use href="/images/icons/sprite.svg#${k}"></use></svg></a>`)
            .join('');
        })(),
        langEnCurrent: lang === 'en' ? ' aria-current="true"' : '',
        langArCurrent: lang === 'ar' ? ' aria-current="true"' : '',
        enUrl: page.meta.output ? urlPath('en', '') : (pair.en ? urlPath('en', page.route) : urlPath('en', '')),
        arUrl: page.meta.output ? urlPath('ar', '') : (pair.ar ? urlPath('ar', page.route) : urlPath('ar', '')),
        cssVersion: assetVersion.css,
        jsVersion: assetVersion.js,
        tagline: esc(business.tagline?.verified ? business.tagline[lang] : ''),
        hours: esc(business.openingHours?.verified ? business.openingHours.display[lang] : ''),
        gscMeta: (() => {
          const v = business.integrations?.googleSiteVerification;
          return v && /^[\w-]+$/.test(v) ? `<meta name="google-site-verification" content="${v}">` : '';
        })(),
        gaId: /^G-[A-Z0-9]+$/.test(business.integrations?.ga4MeasurementId || '') ? business.integrations.ga4MeasurementId : '',
        hreflang,
        robots: indexable ? '' : '<meta name="robots" content="noindex, follow">',
        schema: '',
        content: ''
      }
    };
    ctx.scalars.schema = buildSchema(page, ctx);

    // partials are rendered with the same context, then injected
    const partials = {};
    for (const f of partialFiles) {
      const name = path.basename(f, '.html');
      if (name === 'layout') continue;
      partials[name] = renderTokens(await readFile(f, 'utf8'), ctx);
    }

    // Page bodies may include partials too (e.g. {{> cta-band}}, {{> quote-form}}),
    // so expand tokens first and then inject the already-rendered partials.
    ctx.scalars.content = await renderPartials(renderTokens(page.body, ctx), partials, ctx);
    let html = await renderPartials(layoutRaw, partials, ctx);
    html = renderTokens(html, ctx);

    // ---- per-page checks
    const where = `${lang}:/${page.route}`;
    if (!page.meta.title) fail(`${where}: missing title`);
    if (!page.meta.description) fail(`${where}: missing meta description`);
    if (page.meta.title && page.meta.title.length > 62) warn(`${where}: title is ${page.meta.title.length} chars (>62)`);
    if (page.meta.description && (page.meta.description.length < 80 || page.meta.description.length > 170)) {
      warn(`${where}: description is ${page.meta.description.length} chars (aim 140–160)`);
    }
    const h1s = (html.match(/<h1[\s>]/g) || []).length;
    // "shell" pages are layout wrappers for PHP (the admin supplies its own <h1>)
    if (h1s !== 1 && !page.meta.shell) fail(`${where}: page has ${h1s} <h1> elements (needs exactly 1)`);
    if (html.includes('[[OWNER-INPUT')) fail(`${where}: contains an [[OWNER-INPUT placeholder`);
    if (/lorem ipsum/i.test(html)) fail(`${where}: contains lorem ipsum`);
    const key = `${lang}|${page.meta.title}`;
    if (titles.has(key)) fail(`${where}: duplicate title with ${titles.get(key)}`);
    else titles.set(key, where);

    // ---- write
    // the admin shell: site header + footer around a marker that /admin/index.php
    // replaces with the dashboard. Not a public page, never in the sitemap.
    // a page can be written to a fixed file instead of a route folder (the 404 document)
    if (page.meta.output) {
      // an error document has no URL of its own: no canonical, no hreflang, no alternates
      const doc = html
        .replace(/\n?\s*<link rel="canonical"[^>]*>/g, '')
        .replace(/\n?\s*<link rel="alternate"[^>]*>/g, '')
        .replace(/\n?\s*<meta property="og:url"[^>]*>/g, '');
      await writeFile(path.join(DIST, page.meta.output), doc, 'utf8');
      continue;
    }
    if (page.meta.shell) {
      await mkdir(path.join(DIST, 'admin'), { recursive: true });
      await writeFile(path.join(DIST, 'admin', 'shell.html'), html, 'utf8');
      continue;
    }
    const outDir = path.join(DIST, urlPath(lang, page.route).replace(/^\//, ''));
    await mkdir(outDir, { recursive: true });
    // pages that show live data (reviews, projects) are served by PHP
    await writeFile(path.join(outDir, page.meta.php ? 'index.php' : 'index.html'), html, 'utf8');

    if (indexable) {
      sitemap.push({
        loc: canonical,
        alternates: Object.keys(pair).length > 1 ? LANGS.filter((l) => pair[l]).map((l) => ({ lang: l, href: domain + urlPath(l, page.route) })) : [],
        lastmod: (await stat(page.file)).mtime.toISOString().slice(0, 10)
      });
    }
  }

  // 5. sitemap.xml (with hreflang alternates) + robots.txt
  const sm = ['<?xml version="1.0" encoding="UTF-8"?>',
    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'];
  for (const u of sitemap.sort((a, b) => a.loc.localeCompare(b.loc))) {
    sm.push('  <url>', `    <loc>${u.loc}</loc>`, `    <lastmod>${u.lastmod}</lastmod>`);
    for (const a of u.alternates) sm.push(`    <xhtml:link rel="alternate" hreflang="${a.lang}" href="${a.href}"/>`);
    if (u.alternates.length) sm.push(`    <xhtml:link rel="alternate" hreflang="x-default" href="${u.alternates.find((a) => a.lang === DEFAULT_LANG)?.href}"/>`);
    sm.push('  </url>');
  }
  sm.push('</urlset>');
  await writeFile(path.join(DIST, 'sitemap.xml'), sm.join('\n'), 'utf8');
  await writeFile(path.join(DIST, 'robots.txt'),
    `User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /form/\n\nSitemap: ${domain}/sitemap.xml\n`, 'utf8');

  // 6. assets
  const css = cssSource;
  await mkdir(path.join(DIST, 'css'), { recursive: true });
  await writeFile(path.join(DIST, 'css', 'site.css'), minifyCSS(css), 'utf8');

  // JS: module imports get the version too, otherwise a cached navigation.js
  // could be paired with a new main.js
  await mkdir(path.join(DIST, 'js'), { recursive: true });
  for (const f of jsFiles) {
    const code = (await readFile(f, 'utf8'))
      .replace(/(from\s+['"]\.\/[\w-]+\.js)(['"])/g, `$1?v=${assetVersion.js}$2`);
    await writeFile(path.join(DIST, 'js', path.basename(f)), code, 'utf8');
  }
  // admin dashboard → /admin/
  await copyDir(path.join(SRC, 'admin'), path.join(DIST, 'admin'));

  // PHP form handler → /form/ ; the sample config never ships (it would be a 404 anyway,
  // but .htaccess also denies *.sample.php)
  const phpFrom = path.join(SRC, 'php');
  if (existsSync(phpFrom)) {
    for (const file of await walk(phpFrom)) {
      if (file.endsWith('config.sample.php')) continue;
      const dest = path.join(DIST, 'form', path.relative(phpFrom, file));
      await mkdir(path.dirname(dest), { recursive: true });
      await copyFile(file, dest);
    }
  }
  await copyDir(path.join(SRC, 'images'), path.join(DIST, 'images'));
  await copyDir(path.join(SRC, 'fonts'), path.join(DIST, 'fonts'));
  await copyDir(path.join(SRC, 'static'), DIST);

  // 7. report
  console.log(`\nPages built : ${pages.length} (${LANGS.map((l) => `${l}: ${pages.filter((p) => p.lang === l).length}`).join(', ')})`);
  console.log(`Sitemap URLs: ${sitemap.length}`);
  console.log(`CSS         : ${(css.length / 1024).toFixed(1)} KB → ${(minifyCSS(css).length / 1024).toFixed(1)} KB minified`);
  if (warnings.length) {
    console.log(`\nWarnings (${warnings.length}):`);
    for (const w of [...new Set(warnings)]) console.log(`  ! ${w}`);
  }
  if (errors.length) {
    console.error(`\nBUILD FAILED — ${errors.length} error(s):`);
    for (const e of [...new Set(errors)]) console.error(`  ✗ ${e}`);
    process.exit(1);
  }
  console.log('\nBuild OK → dist/\n');
}

build().catch((e) => { console.error(e); process.exit(1); });
