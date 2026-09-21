/** Quote/contact form enhancement. The form works without JS; this adds
 *  inline validation, an error summary and digit normalisation. */

const AR_DIGITS = /[٠-٩۰-۹]/g;
const toWestern = (s) => s.replace(AR_DIGITS, (d) => {
  const code = d.charCodeAt(0);
  return String(code >= 0x06F0 ? code - 0x06F0 : code - 0x0660);
});

// UAE: 05x xxx xxxx, +9715x…, 04 landline, 800 numbers
const PHONE_OK = /^(?:\+?971|0)(?:5\d{8}|[234679]\d{7})$|^800\d{3,7}$/;

export function initForms() {
  for (const form of document.querySelectorAll('[data-form]')) {
    // Records when the page was loaded; the server rejects submissions that arrive
    // implausibly fast. Absent (JS off) simply skips the check.
    const stamp = form.querySelector('[data-timestamp]');
    if (stamp) stamp.value = String(Date.now());

    const t = {
      required: form.dataset.msgRequired || 'This field is required.',
      phone: form.dataset.msgPhone || 'Please enter a valid UAE phone number.',
      summary: form.dataset.msgSummary || 'Please check the following:'
    };

    const phone = form.querySelector('input[type="tel"]');
    if (phone) {
      phone.addEventListener('blur', () => { phone.value = toWestern(phone.value).replace(/[\s-]/g, ''); });
    }

    form.addEventListener('submit', (e) => {
      const problems = [];
      form.querySelectorAll('[required]').forEach((field) => {
        clearError(field);
        if (!field.value.trim()) problems.push(setError(field, t.required));
        else if (field.type === 'tel' && !PHONE_OK.test(toWestern(field.value).replace(/[\s-]/g, ''))) {
          problems.push(setError(field, t.phone));
        }
      });

      const old = form.querySelector('.form__summary');
      if (old) old.remove();

      if (problems.length) {
        e.preventDefault();
        const box = document.createElement('div');
        box.className = 'form__summary';
        box.setAttribute('role', 'alert');
        box.tabIndex = -1;
        box.innerHTML = `<h2></h2><ul></ul>`;
        box.querySelector('h2').textContent = t.summary;
        const ul = box.querySelector('ul');
        for (const p of problems) {
          const li = document.createElement('li');
          const a = document.createElement('a');
          a.href = `#${p.id}`;
          a.textContent = p.label;
          a.addEventListener('click', (ev) => { ev.preventDefault(); document.getElementById(p.id)?.focus(); });
          li.append(a);
          ul.append(li);
        }
        form.prepend(box);
        box.focus();
        return;
      }

      const submit = form.querySelector('[type="submit"]');
      if (submit && form.dataset.msgSending) {
        submit.setAttribute('aria-disabled', 'true');
        submit.textContent = form.dataset.msgSending;
      }
    });
  }
}

function fieldWrap(field) { return field.closest('.field') || field.parentElement; }

function clearError(field) {
  const wrap = fieldWrap(field);
  wrap.classList.remove('field--error');
  wrap.querySelector('.field__error')?.remove();
  field.removeAttribute('aria-invalid');
}

function setError(field, message) {
  const wrap = fieldWrap(field);
  const id = field.id || `f-${Math.random().toString(36).slice(2, 8)}`;
  field.id = id;
  wrap.classList.add('field--error');
  field.setAttribute('aria-invalid', 'true');

  const msg = document.createElement('p');
  msg.className = 'field__error';
  msg.id = `${id}-error`;
  msg.textContent = message;
  wrap.append(msg);
  field.setAttribute('aria-describedby', `${id}-error`);

  const labelEl = wrap.querySelector('.field__label');
  return { id, label: `${labelEl ? labelEl.textContent.trim() : id} — ${message}` };
}
