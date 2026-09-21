/**
 * Continuous auto-sliders for phones.
 *
 * Any element marked data-marquee="l2r" (left → right) or "r2l" (right → left)
 * becomes a seamless loop below 48rem: its children are cloned once, and the
 * track moves by exactly one set, so the loop never jumps. On tablet and desktop
 * the clones are removed and the section goes back to its normal static layout.
 * Without JavaScript the section simply stays static.
 */
const PHONE = '(max-width: 47.99rem)';

export function initMarquees() {
  const tracks = [...document.querySelectorAll('[data-marquee]')];
  if (!tracks.length) return;
  const mq = window.matchMedia(PHONE);

  const enable = (track) => {
    if (track.dataset.cloned) return;
    for (const child of [...track.children]) {
      const copy = child.cloneNode(true);
      copy.setAttribute('aria-hidden', 'true');   // screen readers hear each item once
      copy.setAttribute('inert', '');             // clones are never focusable
      copy.dataset.clone = 'true';
      track.appendChild(copy);
    }
    track.dataset.cloned = 'true';
    track.parentElement.classList.add('is-marquee');
  };

  const disable = (track) => {
    if (!track.dataset.cloned) return;
    track.querySelectorAll('[data-clone="true"]').forEach((el) => el.remove());
    delete track.dataset.cloned;
    track.parentElement.classList.remove('is-marquee');
  };

  const apply = () => tracks.forEach((t) => (mq.matches ? enable(t) : disable(t)));
  apply();
  mq.addEventListener('change', apply);
}
