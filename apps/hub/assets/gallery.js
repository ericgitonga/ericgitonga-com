// Shared album-grid + modal image viewer for the Daguerreotypes and Dudus
// plates. Reads /api/media-manifest (built by api/sync-media.js from Angry
// Hosting), renders an album grid into #gallery-root, and opens a <dialog>
// modal with previous/next navigation when an album is chosen. One shared
// file (unlike this site's usual per-page duplication) because this is real
// interactive logic, not boilerplate — a fix here shouldn't need to happen
// twice.
(function () {
  function initGallery(plateKey) {
    var root = document.getElementById('gallery-root');
    if (!root) return;

    fetch('/api/media-manifest')
      .then(function (r) {
        if (!r.ok) throw new Error('manifest fetch failed: ' + r.status);
        return r.json();
      })
      .then(function (manifest) {
        var albums = (manifest.plates && manifest.plates[plateKey]) || [];
        renderGrid(root, albums, manifest.generatedAt);
      })
      .catch(function () {
        renderEmpty(root);
      });
  }

  function renderEmpty(root) {
    root.innerHTML = '<p class="gallery-empty">Photographs are on their way — check back soon.</p>';
  }

  function renderGrid(root, albums, generatedAt) {
    if (albums.length === 0) {
      renderEmpty(root);
      return;
    }

    var grid = document.createElement('div');
    grid.className = 'album-grid';

    albums.forEach(function (album, albumIndex) {
      var card = document.createElement('button');
      card.type = 'button';
      card.className = 'album-card';
      card.setAttribute('aria-label', 'Open album: ' + album.title);

      var thumb = document.createElement('span');
      thumb.className = 'album-thumb';
      var img = document.createElement('img');
      img.src = album.cover;
      img.alt = '';
      img.loading = 'lazy';
      thumb.appendChild(img);

      var title = document.createElement('span');
      title.className = 'album-title';
      title.textContent = album.title;

      var count = document.createElement('span');
      count.className = 'album-count';
      count.textContent = album.images.length + (album.images.length === 1 ? ' photo' : ' photos');

      card.appendChild(thumb);
      card.appendChild(title);
      card.appendChild(count);
      card.addEventListener('click', function () {
        openModal(albums, albumIndex, 0);
      });

      grid.appendChild(card);
    });

    root.innerHTML = '';
    root.appendChild(grid);

    if (generatedAt) {
      var updated = document.createElement('p');
      updated.className = 'gallery-updated';
      updated.textContent = 'Updated ' + formatDate(generatedAt);
      root.appendChild(updated);
    }
  }

  function formatDate(iso) {
    try {
      return new Date(iso).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      });
    } catch (e) {
      return iso;
    }
  }

  var EXPAND_ICON =
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
      '<path d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M8 21H5a2 2 0 0 1-2-2v-3M16 21h3a2 2 0 0 0 2-2v-3"/>' +
    '</svg>';
  var COMPRESS_ICON =
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
      '<path d="M9 3v3a2 2 0 0 1-2 2H4M21 8h-3a2 2 0 0 1-2-2V3M3 16h3a2 2 0 0 1 2 2v3M16 21v-3a2 2 0 0 1 2-2h3"/>' +
    '</svg>';
  // Element.requestFullscreen() isn't available everywhere (notably older
  // iOS Safari) — feature-detect once and skip rendering the button
  // entirely rather than wiring up a control that can't do anything.
  var FULLSCREEN_SUPPORTED = typeof document.documentElement.requestFullscreen === 'function';

  var modal, modalFigure, modalImg, modalCaption, modalThumbs, fullscreenBtn;
  var currentAlbums, currentAlbumIndex, currentImageIndex;

  function ensureModal() {
    if (modal) return;

    modal = document.createElement('dialog');
    modal.id = 'gallery-modal';
    // Close/fullscreen/prev/next all live *inside* the figure, not as its
    // siblings — when the figure becomes the fullscreen element, only its
    // own descendants travel into the top layer with it. A sibling control
    // would end up visually covered (and unclickable) the moment fullscreen
    // is entered.
    modal.innerHTML =
      '<figure class="gallery-figure">' +
        '<button type="button" class="modal-close" data-gallery-close aria-label="Close">×</button>' +
        (FULLSCREEN_SUPPORTED
          ? '<button type="button" class="modal-fullscreen" data-gallery-fullscreen aria-label="Enter fullscreen">' + EXPAND_ICON + '</button>'
          : '') +
        '<button type="button" class="gallery-nav gallery-prev" data-gallery-prev aria-label="Previous photo">‹</button>' +
        '<button type="button" class="gallery-nav gallery-next" data-gallery-next aria-label="Next photo">›</button>' +
        '<img class="gallery-image" alt="">' +
        '<figcaption class="gallery-caption"></figcaption>' +
        '<div class="gallery-thumbs" data-gallery-thumbs></div>' +
      '</figure>';
    document.body.appendChild(modal);

    modalFigure = modal.querySelector('.gallery-figure');
    modalImg = modal.querySelector('.gallery-image');
    modalCaption = modal.querySelector('.gallery-caption');
    modalThumbs = modal.querySelector('[data-gallery-thumbs]');
    fullscreenBtn = modal.querySelector('[data-gallery-fullscreen]');

    modal.querySelector('[data-gallery-close]').addEventListener('click', function () {
      modal.close();
    });
    modal.querySelector('[data-gallery-prev]').addEventListener('click', function () {
      step(-1);
    });
    modal.querySelector('[data-gallery-next]').addEventListener('click', function () {
      step(1);
    });
    if (fullscreenBtn) {
      fullscreenBtn.addEventListener('click', function () {
        if (document.fullscreenElement) {
          document.exitFullscreen();
        } else {
          // Fullscreening the <dialog> itself is rejected by the spec
          // ("Dialog elements are invalid") since it's already promoted to
          // the top layer by showModal() — fullscreen a child instead. The
          // figure still expands to fill the whole screen either way.
          modalFigure.requestFullscreen();
        }
      });
      document.addEventListener('fullscreenchange', updateFullscreenButton);
    }
    modal.addEventListener('click', function (e) {
      if (e.target === modal) modal.close();
    });
    modal.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft') {
        e.preventDefault();
        step(-1);
      } else if (e.key === 'ArrowRight') {
        e.preventDefault();
        step(1);
      }
    });
    modal.addEventListener('close', function () {
      if (document.fullscreenElement === modalFigure) document.exitFullscreen();
    });
  }

  function updateFullscreenButton() {
    if (!fullscreenBtn) return;
    var isFullscreen = document.fullscreenElement === modalFigure;
    fullscreenBtn.innerHTML = isFullscreen ? COMPRESS_ICON : EXPAND_ICON;
    fullscreenBtn.setAttribute('aria-label', isFullscreen ? 'Exit fullscreen' : 'Enter fullscreen');
  }

  function step(delta) {
    var images = currentAlbums[currentAlbumIndex].images;
    goTo((currentImageIndex + delta + images.length) % images.length);
  }

  function goTo(index) {
    currentImageIndex = index;
    renderImage();
  }

  function renderImage() {
    var album = currentAlbums[currentAlbumIndex];
    var image = album.images[currentImageIndex];
    modalImg.src = image.url;
    modalImg.alt = album.title + ' — photo ' + (currentImageIndex + 1) + ' of ' + album.images.length;
    modalCaption.textContent = album.title + ' — ' + (currentImageIndex + 1) + ' / ' + album.images.length;
    updateActiveThumb();
  }

  // Built once per album (not on every step/goTo) so clicking through
  // photos doesn't rebuild every thumbnail's DOM node each time — only the
  // active-state class and scroll position update per image.
  function renderThumbs() {
    var album = currentAlbums[currentAlbumIndex];
    modalThumbs.innerHTML = '';
    album.images.forEach(function (image, index) {
      var thumb = document.createElement('button');
      thumb.type = 'button';
      thumb.className = 'gallery-thumb';
      thumb.setAttribute('aria-label', 'Go to photo ' + (index + 1) + ' of ' + album.images.length);
      var img = document.createElement('img');
      img.src = image.url;
      img.alt = '';
      img.loading = 'lazy';
      thumb.appendChild(img);
      thumb.addEventListener('click', function () {
        goTo(index);
      });
      modalThumbs.appendChild(thumb);
    });
  }

  function updateActiveThumb() {
    var thumbs = modalThumbs.children;
    for (var i = 0; i < thumbs.length; i++) {
      thumbs[i].classList.toggle('is-active', i === currentImageIndex);
    }
    var active = thumbs[currentImageIndex];
    if (active) active.scrollIntoView({ block: 'nearest', inline: 'center' });
  }

  function openModal(albums, albumIndex, imageIndex) {
    ensureModal();
    currentAlbums = albums;
    currentAlbumIndex = albumIndex;
    currentImageIndex = imageIndex;
    renderThumbs();
    renderImage();
    updateFullscreenButton();
    modal.showModal();
  }

  window.initGallery = initGallery;
})();
