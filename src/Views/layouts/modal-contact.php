<?php
$proof = \EricGitonga\Security\AntiSpamGate::generateProofToken();
?>
<dialog id="contact-modal">
  <button class="modal-close" data-close-modal aria-label="Close">×</button>
  <h3>Contact</h3>
  <p style="font-family: var(--serif); font-size: 1rem; color: var(--ink-muted); margin-bottom: 1.2rem;">
    Send a direct note, or reach out via email.
  </p>

  <form class="contact-form" id="contact-form" method="POST" action="/api/contact">
    <!-- 7-Layer Anti-Spam Security Tokens -->
    <div class="form-field hp-field" aria-hidden="true">
      <label for="contact-website">Website</label>
      <input id="contact-website" name="website" type="text" tabindex="-1" autocomplete="nope">
    </div>
    <input type="hidden" name="ts" value="<?= (int)$proof['timestamp'] ?>">
    <input type="hidden" name="salt" value="<?= htmlspecialchars((string)$proof['salt'], ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="sig" value="<?= htmlspecialchars((string)$proof['signature'], ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="_proof" value="">

    <div class="form-field">
      <label for="contact-name">Name</label>
      <input id="contact-name" name="name" type="text" required maxlength="100">
    </div>

    <div class="form-field">
      <label for="contact-email">Email</label>
      <input id="contact-email" name="email" type="email" required maxlength="150">
    </div>

    <div class="form-field">
      <label for="contact-message">Message</label>
      <textarea id="contact-message" name="message" rows="4" required maxlength="2500"></textarea>
    </div>

    <button type="submit" class="form-submit">Send Message</button>
    <p class="form-status" id="contact-status" role="status" aria-live="polite"></p>
  </form>

  <div class="modal-icons">
    <a href="https://github.com/ericgitonga" target="_blank" rel="noopener" aria-label="GitHub: ericgitonga">
      <svg viewBox="0 0 16 16" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/></svg>
    </a>
    <a href="mailto:gitonga@gmail.com" aria-label="Email: gitonga@gmail.com">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2.2"/><path d="M3.5 7l8.5 6 8.5-6"/></svg>
    </a>
  </div>
</dialog>
