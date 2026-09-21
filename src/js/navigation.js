/** Mobile navigation: accessible disclosure with focus management. */
const FOCUSABLE = 'a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])';


/** Header dropdowns (Services). Hover and keyboard focus work without JS;
 *  the button makes them usable on touch screens and inside the mobile panel. */
function initSubmenus() {
  const groups = [...document.querySelectorAll('.site-nav__has-sub')];
  if (!groups.length) return;
  const close = (group) => {
    group.dataset.open = 'false';
    group.querySelector('[aria-expanded]')?.setAttribute('aria-expanded', 'false');
  };
  for (const group of groups) {
    const button = group.querySelector('[aria-expanded]');
    button?.addEventListener('click', () => {
      const open = group.dataset.open === 'true';
      for (const other of groups) close(other);
      if (!open) {
        group.dataset.open = 'true';
        button.setAttribute('aria-expanded', 'true');
      }
    });
  }
  document.addEventListener('click', (e) => {
    for (const group of groups) if (!group.contains(e.target)) close(group);
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') groups.forEach(close);
  });
}

export function initNavigation() {
  initSubmenus();
  const toggle = document.querySelector('[data-nav-toggle]');
  const nav = document.querySelector('[data-nav]');
  if (!toggle || !nav) return;

  const label = toggle.querySelector('[data-label-open]');
  const sticky = document.querySelector('[data-sticky-cta]');
  let lastFocus = null;

  const setOpen = (open) => {
    toggle.setAttribute('aria-expanded', String(open));
    // the panel starts exactly under the header, which is taller while the top bar is visible
    const header = document.querySelector('[data-header]');
    nav.style.insetBlockStart = open && header ? `${Math.max(0, Math.round(header.getBoundingClientRect().bottom))}px` : '';
    nav.dataset.open = String(open);
    document.body.style.overflow = open ? 'hidden' : '';
    if (sticky) sticky.hidden = open;                       // bar must not sit over the menu
    if (label) label.textContent = open ? label.dataset.labelClose : label.dataset.labelOpen;

    if (open) {
      lastFocus = document.activeElement;
      nav.querySelector(FOCUSABLE)?.focus();
    } else {
      (lastFocus || toggle).focus();
    }
  };

  toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') setOpen(false);
    if (e.key !== 'Tab' || toggle.getAttribute('aria-expanded') !== 'true') return;
    const items = [...nav.querySelectorAll(FOCUSABLE)].filter((el) => el.offsetParent !== null);
    if (!items.length) return;
    const first = items[0];
    const last = items[items.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  });

  // reset when resizing up to desktop
  window.matchMedia('(min-width: 62rem)').addEventListener('change', (e) => {
    if (e.matches && toggle.getAttribute('aria-expanded') === 'true') setOpen(false);
  });
}
