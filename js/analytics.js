/** Conversion events. No analytics vendor is loaded yet (Stage 14 decision);
 *  events are queued on window.dataLayer so any consent-aware tool can pick them up. */
export function initAnalytics() {
  window.dataLayer = window.dataLayer || [];
  const lang = document.documentElement.lang || 'en';

  const push = (event, params = {}) => window.dataLayer.push({ event, lang, ...params });

  document.addEventListener('click', (e) => {
    const el = e.target.closest('[data-event]');
    if (!el) return;
    push(el.dataset.event, { placement: el.dataset.placement || 'body' });
  });

  for (const form of document.querySelectorAll('[data-form]')) {
    form.addEventListener('submit', () => {
      const services = [...form.querySelectorAll('input[name="services[]"]:checked')].map((i) => i.value);
      push('quote_submit', { page_type: document.body.dataset.pageType || '', services });
    });
  }
}
