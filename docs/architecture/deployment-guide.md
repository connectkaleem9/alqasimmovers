# Deployment Guide — Hostinger

| Status | Owner | Stage | Date |
|---|---|---|---|
| Ready (host chosen; launch steps pending) | deployment-engineer | 4 / 14 | 2026-09-20 |

Host: **Hostinger shared hosting** (the owner's existing unlimited plan) — Apache + PHP + MySQL. See ADR-004/005.

## Local commands
```bash
npm run build      # node scripts/build.mjs  → dist/
npm run qa         # crawl dist/ (add -- --allow-missing while pages are unwritten)
npm run scan       # secret scan
npm run test       # PHP helper tests (needs php on PATH)
npm run check      # build + qa + scan
npm run serve      # static preview, http://localhost:4173 (no PHP)
npm run serve:php  # preview WITH the form handler: php -S localhost:4173 -t dist
```
Nothing is installed: npm is only a task runner, and the build uses Node built-ins.

## One-time server setup

**1. Database** (hPanel → Databases → MySQL Databases)
- Create a database and user, e.g. `u000000000_alqasim`. Save the password somewhere safe — it never goes in the repo.
- Open phpMyAdmin → Import → upload `db/schema.sql`. This creates the `leads` table and the `leads_this_month` view.

**2. Config file, above the web root** (hPanel → File Manager)
- Create `/home/<your-user>/private/` (a sibling of `public_html`, **not inside it**).
- Copy `src/php/config.sample.php` there as `alqasim-config.php`, fill in the database name, user, password, your email address, and a random `ip_salt`.
- Set its permissions to 600.
- Nothing outside `public_html` is reachable from the web, so this file can never be downloaded.

**3. Email** (hPanel → Emails)
- Create `website@alqasimmovers.com` and use it as `mail.from`; mail sent from your own domain is far less likely to be filtered as spam.
- Set `mail.to` to wherever you want the leads.

**4. PHP version** (hPanel → Advanced → PHP Configuration): PHP 8.1 or newer.

## Deploying a new version
1. `npm run check` locally (must be clean).
2. Upload the **contents of `dist/`** into `public_html/`:
   - Easiest: zip `dist`, upload the zip in File Manager, extract it, move the files into `public_html`.
   - Repeatable: FTP/SFTP sync (FileZilla) or hPanel's Git deployment pointed at a branch that contains the built files.
3. Confirm `public_html/.htaccess` exists (File Manager hides dotfiles until you enable "show hidden files").
4. Visit the site, then send a test enquiry through the form.

`dist/` is git-ignored, so if you use Git deployment, create a separate branch that does include the built output.

## What ships in `dist/`
```
index.html, ar/index.html, …      the pages
.htaccess                         headers, HTTPS + non-www redirects, trailing slash, 404, compression
form/quote.php, form/lib.php      the form handler
css/site.css, js/*.js, fonts/*, images/*
robots.txt, sitemap.xml, site.webmanifest
```

## Verification after deploy
- [ ] `https://alqasimmovers.com` loads; `http://` and `www.` both 301 to it
- [ ] `/ar/` loads right-to-left in Arabic
- [ ] A made-up URL returns a real **404** (`curl -I https://alqasimmovers.com/no-such-page/`)
- [ ] `/robots.txt` and `/sitemap.xml` load
- [ ] `/form/lib.php` opened directly does nothing harmful; `/db/schema.sql` and `/.htaccess` are blocked
- [ ] A test enquiry: appears in the `leads` table **and** arrives by email
- [ ] Headers present (securityheaders.com); CSP switched from Report-Only to enforced after a clean week

## Reading leads
phpMyAdmin → your database → `leads` (or the `leads_this_month` view). The `status` and `notes` columns are there for your own follow-up. A password-protected admin page can be added later if you want one — it is deliberately not built yet, because a login is the kind of thing that gets attacked.

## Rollback
Keep the previous `dist` zip. Rolling back = re-uploading it; the site is only static files plus one PHP script, so there is no migration to undo. Database rows are never deleted by a deploy.

## Post-launch (Stage 14)
- [ ] Google Search Console: verify the domain, submit the sitemap, inspect key EN and AR URLs
- [ ] Analytics connected (consent-aware) with `quote_submit`, `tel_click`, `whatsapp_click`
- [ ] Hostinger backups enabled; note where they are
- [ ] Optional: put free Cloudflare in front of the domain if TTFB from the UAE is slow
