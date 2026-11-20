// Client interaction token & dialog hash listener
document.addEventListener('DOMContentLoaded', () => {
  // Populate human interaction token on first interaction
  const events = ['pointerdown', 'keydown', 'touchstart', 'focusin'];
  const onFirstInteraction = () => {
    document.querySelectorAll('input[name="_proof"]').forEach(el => {
      if (!el.value) {
        el.value = 'human_' + Math.random().toString(36).substring(2) + '_' + Date.now();
      }
    });
    events.forEach(ev => window.removeEventListener(ev, onFirstInteraction, { capture: true }));
  };
  events.forEach(ev => window.addEventListener(ev, onFirstInteraction, { capture: true, passive: true }));

  // Modal dialog trigger logic
  const bioModal = document.getElementById('bio-modal');
  const contactModal = document.getElementById('contact-modal');

  const checkHash = () => {
    const hash = window.location.hash;
    if (hash === '#bio-modal' && bioModal) {
      if (typeof bioModal.showModal === 'function') bioModal.showModal();
    } else if (hash === '#contact-modal' && contactModal) {
      if (typeof contactModal.showModal === 'function') contactModal.showModal();
    }
  };

  document.querySelectorAll('a[href="#bio-modal"]').forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      history.pushState(null, '', '#bio-modal');
      if (bioModal && typeof bioModal.showModal === 'function') bioModal.showModal();
    });
  });

  document.querySelectorAll('a[href="#contact-modal"]').forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      history.pushState(null, '', '#contact-modal');
      if (contactModal && typeof contactModal.showModal === 'function') contactModal.showModal();
    });
  });

  document.querySelectorAll('[data-close-modal]').forEach(btn => {
    btn.addEventListener('click', () => {
      const d = btn.closest('dialog');
      if (d) {
        d.close();
        if (window.location.hash) history.pushState(null, '', window.location.pathname);
      }
    });
  });

  document.querySelectorAll('dialog[id$="modal"]').forEach(d => {
    d.addEventListener('click', (e) => {
      if (e.target === d) {
        d.close();
        if (window.location.hash) history.pushState(null, '', window.location.pathname);
      }
    });
  });

  checkHash();
  window.addEventListener('hashchange', checkHash);

  // Contact form submission via AJAX
  const contactForm = document.getElementById('contact-form');
  const contactStatus = document.getElementById('contact-status');
  if (contactForm) {
    contactForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const submitBtn = contactForm.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;
      if (contactStatus) {
        contactStatus.textContent = 'Sending...';
        contactStatus.className = 'form-status';
      }

      const fd = new FormData(contactForm);
      try {
        const resp = await fetch('/api/contact', {
          method: 'POST',
          body: fd,
          headers: { 'Accept': 'application/json' }
        });
        const data = await resp.json();
        if (resp.ok && data.success) {
          if (contactStatus) {
            contactStatus.textContent = data.message || 'Thank you — message sent!';
            contactStatus.className = 'form-status success';
          }
          contactForm.reset();
        } else {
          if (contactStatus) {
            contactStatus.textContent = data.error || 'Failed to send message. Please try again.';
            contactStatus.className = 'form-status error';
          }
        }
      } catch (err) {
        if (contactStatus) {
          contactStatus.textContent = 'Network error. Please try again.';
          contactStatus.className = 'form-status error';
        }
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }
});
