# Going live on Hostinger

Everything the site needs is already built and on GitHub. These are the one-time
setup steps, then how to publish changes afterwards.

- **Repo:** https://github.com/connectkaleem9/alqasimmovers
- **`main` branch** — the source (what we work on)
- **`deploy` branch** — the finished website only, ready for `public_html`

---

## One-time setup (about 20 minutes)

### 1. Database (hPanel → Databases → MySQL Databases)
1. Create a database and a user. Write the password down somewhere safe.
2. Open **phpMyAdmin → Import** and upload `db/schema.sql` from this project.
   That creates the `leads` table where every quote request is stored.

### 2. Settings file — this goes ABOVE public_html
1. In **File Manager**, go up one level from `public_html` and create a folder called `private`.
2. Copy `src/php/config.sample.php` into it and rename it `alqasim-config.php`.
3. Fill in: database name, database user, database password, your email address,
   and any random text for `ip_salt`.
4. Set its permissions to **600**.

Nothing above `public_html` can be opened from the internet, so your password is safe there.
**Never put this file inside `public_html`.**

### 3. Email (hPanel → Emails)
Create `website@alqasimmovers.com` and use it as the `from` address in the settings file.
Mail sent from your own domain is far less likely to land in spam. Put the address where
you want to receive leads in `to`.

### 4. PHP version (hPanel → Advanced → PHP Configuration)
Set PHP **8.1 or newer**.

### 5. Connect the website files
**Option A — Git (recommended, one click to update later)**
1. hPanel → **Git** → Create repository
2. Repository: `https://github.com/connectkaleem9/alqasimmovers`
3. Branch: **deploy**
4. Directory: `public_html`
5. Press **Deploy**

**Option B — manual upload**
Run `npm run build`, zip everything **inside** the `dist` folder, upload the zip in
File Manager, and extract it into `public_html`.

### 6. Domain and HTTPS
Point `alqasimmovers.com` at Hostinger, then turn on the free SSL certificate and
"force HTTPS". The `.htaccess` file already redirects `www` to the plain domain and
adds the trailing slashes.

---

## Publishing changes after that

```bash
npm run release
```

That one command builds the site, runs the link and SEO checks, scans for passwords,
**refuses to publish a preview build with sample reviews**, and pushes the finished
files to the `deploy` branch. Then press Deploy in hPanel (or let auto-deploy run).

---

## Check after the first deploy

- [ ] `https://alqasimmovers.com` opens, and `http://` and `www.` both redirect to it
- [ ] `https://alqasimmovers.com/ar/` opens right-to-left in Arabic
- [ ] A made-up address returns a real 404 page
- [ ] `/robots.txt` and `/sitemap.xml` open
- [ ] Send a test enquiry: it appears in the `leads` table **and** arrives by email
- [ ] `https://alqasimmovers.com/db/schema.sql` must NOT open (should be blocked)

## Still to do before launch
The site currently has only the homepage. About, Services, Areas, Contact, Get a Quote,
the legal pages and the 404 page come next (Stage 5 onward). Until then the menu links
lead nowhere, so hold off on advertising the address.
