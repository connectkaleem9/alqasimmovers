/**
 * Google Analytics 4, loaded only after the visitor accepts cookies.
 *
 * The measurement ID comes from <html data-ga> (set by the build from
 * config/business.json). Until the visitor chooses, nothing is loaded and no
 * cookie is set; "Decline" keeps it that way. The choice is remembered in
 * localStorage and can be changed from the footer ("Cookie settings").
 * The admin area never loads analytics.
 */
const KEY = 'aq-consent';

const readChoice = () => { try { return localStorage.getItem(KEY); } catch { return null; } };
const saveChoice = (v) => { try { localStorage.setItem(KEY, v); } catch { /* private mode: ask again next visit */ } };

// remove Google Analytics cookies (_ga, _ga_XXXX) when consent is declined or withdrawn
const clearGaCookies = () => {
  const host = location.hostname.replace(/^www\./, '');
  for (const c of document.cookie.split(';')) {
    const name = c.split('=')[0].trim();
    if (!/^_ga/.test(name)) continue;
    for (const domain of ['', `; domain=.${host}`, `; domain=${host}`]) {
      document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/${domain}`;
    }
  }
};

export function initAnalytics() {
  const id = document.documentElement.dataset.ga;
  if (!id || location.pathname.startsWith('/admin/')) return;

  window.dataLayer = window.dataLayer || [];
  // gtag.js reads the raw `arguments` objects from dataLayer
  function gtag() { window.dataLayer.push(arguments); }   // eslint-disable-line prefer-rest-params
  const lang = document.documentElement.lang || 'en';
  let loaded = false;

  const load = () => {
    if (loaded) return;
    loaded = true;
    gtag('js', new Date());
    gtag('config', id, { language: lang });
    const s = document.createElement('script');
    s.async = true;
    s.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(id)}`;
    document.head.appendChild(s);
    trackPageOutcome();
  };
  const track = (name, params = {}) => { if (loaded) gtag('event', name, { language: lang, ...params }); };

  // outcomes that are only visible after a redirect
  const trackPageOutcome = () => {
    const q = new URLSearchParams(location.search);
    if (/\/reviews\/$/.test(location.pathname) && q.get('thanks') === '1') track('review_submit');
    if (/\/get-a-quote\/thank-you\/$/.test(location.pathname)) track('generate_lead', { method: 'quote_form' });
  };

  // phone and WhatsApp taps (every CTA carries data-event)
  document.addEventListener('click', (e) => {
    const el = e.target.closest('[data-event]');
    if (el) track(el.dataset.event, { placement: el.dataset.placement || 'body' });
  });
  for (const form of document.querySelectorAll('[data-form]')) {
    form.addEventListener('submit', () => track(form.classList.contains('review-form') ? 'review_form_send' : 'quote_form_send'));
  }

  // consent banner
  const banner = document.querySelector('[data-cookie-banner]');
  const settings = document.querySelector('[data-cookie-settings]');
  if (settings) {
    settings.hidden = false;
    settings.addEventListener('click', () => { if (banner) { banner.hidden = false; banner.querySelector('button')?.focus(); } });
  }
  banner?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-consent]');
    if (!btn) return;
    const choice = btn.dataset.consent;
    saveChoice(choice);
    banner.hidden = true;
    if (choice === 'granted') load();
    else { clearGaCookies(); if (loaded) location.reload(); }   // withdrawing consent: stop the running tag
  });

  const choice = readChoice();
  if (choice === 'granted') load();
  else if (choice === 'denied') clearGaCookies();
  else if (banner) banner.hidden = false;
}
