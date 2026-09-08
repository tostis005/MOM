(function () {
  'use strict';

  var activeOverlay = null;
  var lastFocused = null;

  function getFocusable(container) {
    return Array.prototype.slice.call(
      container.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])')
    ).filter(function (element) {
      return element.offsetParent !== null;
    });
  }

  function closeOverlay(overlay, restoreFocus) {
    if (!overlay) return;
    overlay.classList.remove('is-open');
    overlay.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('mom-overlay-open');
    activeOverlay = null;

    if (restoreFocus !== false && lastFocused && typeof lastFocused.focus === 'function') {
      lastFocused.focus();
    }
  }

  function openOverlay(id, trigger) {
    var overlay = document.getElementById(id);
    if (!overlay) return;

    if (activeOverlay && activeOverlay !== overlay) {
      closeOverlay(activeOverlay, false);
    }

    lastFocused = trigger || document.activeElement;
    activeOverlay = overlay;
    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden', 'false');
    document.body.classList.add('mom-overlay-open');

    window.requestAnimationFrame(function () {
      var autofocus = overlay.querySelector('[data-overlay-autofocus]');
      if (autofocus && typeof autofocus.focus === 'function') {
        autofocus.focus();
        return;
      }

      var focusable = getFocusable(overlay);
      if (focusable.length) focusable[0].focus();
    });
  }

  document.addEventListener('click', function (event) {
    var opener = event.target.closest('[data-open-overlay]');
    if (opener) {
      event.preventDefault();
      openOverlay(opener.getAttribute('data-open-overlay'), opener);
      return;
    }

    var closer = event.target.closest('[data-close-overlay]');
    if (closer) {
      event.preventDefault();
      closeOverlay(closer.closest('[data-mom-overlay]'));
      return;
    }

    var menuLink = event.target.closest('.mobile-menu-nav a, .mobile-menu-cta');
    if (menuLink && activeOverlay && activeOverlay.id === 'mom-mobile-menu') {
      closeOverlay(activeOverlay, false);
    }
  });

  document.addEventListener('keydown', function (event) {
    if (!activeOverlay) return;

    if (event.key === 'Escape') {
      event.preventDefault();
      closeOverlay(activeOverlay);
      return;
    }

    if (event.key !== 'Tab') return;

    var focusable = getFocusable(activeOverlay);
    if (!focusable.length) return;

    var first = focusable[0];
    var last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  });
})();
