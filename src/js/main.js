import { initNavigation } from './navigation.js';
import { initForms } from './forms.js';
import { initAnalytics } from './analytics.js';

initNavigation();
initForms();
initAnalytics();

/* Hide the sticky CTA bar while a text field has focus so it never covers
   the field or the on-screen keyboard (accessibility finding A11Y-D1). */
const sticky = document.querySelector('[data-sticky-cta]');
if (sticky) {
  const isText = (el) => el && /^(INPUT|TEXTAREA|SELECT)$/.test(el.tagName);
  document.addEventListener('focusin', (e) => { if (isText(e.target)) sticky.hidden = true; });
  document.addEventListener('focusout', () => { setTimeout(() => { if (!isText(document.activeElement)) sticky.hidden = false; }, 50); });
}
