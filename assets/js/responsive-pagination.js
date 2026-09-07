(function () {
  'use strict';

  var isEnglish = (document.documentElement.lang || '').toLowerCase().indexOf('en') === 0;
  var labels = isEnglish
    ? { previous: 'Previous page', next: 'Next page', page: 'Page' }
    : { previous: 'Página anterior', next: 'Página siguiente', page: 'Página' };

  function numericValue(element) {
    var value = parseInt((element.textContent || '').trim(), 10);
    return Number.isFinite(value) ? value : 0;
  }

  function currentPageFromLocation() {
    var match = window.location.pathname.match(/\/page\/(\d+)\/?$/i);
    if (match) return parseInt(match[1], 10) || 1;

    var params = new URLSearchParams(window.location.search);
    var paged = parseInt(params.get('paged') || '', 10);
    return Number.isFinite(paged) && paged > 0 ? paged : 1;
  }

  function buildPageUrl(page, exemplarHref) {
    var url;
    try {
      url = new URL(exemplarHref || window.location.href, window.location.href);
    } catch (error) {
      return '#';
    }

    if (/\/page\/\d+\/?$/i.test(url.pathname)) {
      if (page === 1) {
        url.pathname = url.pathname.replace(/\/page\/\d+\/?$/i, '/');
      } else {
        url.pathname = url.pathname.replace(/\/page\/\d+\/?$/i, '/page/' + page + '/');
      }
      return url.toString();
    }

    if (url.searchParams.has('paged')) {
      if (page === 1) url.searchParams.delete('paged');
      else url.searchParams.set('paged', String(page));
      return url.toString();
    }

    var currentPath = window.location.pathname.replace(/\/page\/\d+\/?$/i, '/');
    url = new URL(window.location.href);
    url.pathname = currentPath;
    if (page > 1) {
      url.pathname = currentPath.replace(/\/?$/, '/') + 'page/' + page + '/';
    }
    return url.toString();
  }

  function createPageLink(page, current, href) {
    if (page === current) {
      var currentNode = document.createElement('span');
      currentNode.className = 'page-numbers current';
      currentNode.setAttribute('aria-current', 'page');
      currentNode.textContent = String(page);
      return currentNode;
    }

    var link = document.createElement('a');
    link.className = 'page-numbers';
    link.href = href;
    link.setAttribute('aria-label', labels.page + ' ' + page);
    link.textContent = String(page);
    return link;
  }

  function createDots() {
    var dots = document.createElement('span');
    dots.className = 'page-numbers dots';
    dots.setAttribute('aria-hidden', 'true');
    dots.textContent = '…';
    dots.style.borderColor = 'transparent';
    dots.style.background = 'transparent';
    dots.style.paddingInline = '4px';
    return dots;
  }

  function createArrow(direction, href) {
    var link = document.createElement('a');
    link.className = 'page-numbers ' + direction;
    link.href = href;
    link.setAttribute('aria-label', direction === 'prev' ? labels.previous : labels.next);
    link.textContent = direction === 'prev' ? '‹' : '›';
    link.style.minWidth = '38px';
    link.style.textAlign = 'center';
    return link;
  }

  function enhancePagination(root) {
    if (root.dataset.momResponsivePagination === '1') return;

    var original = Array.prototype.slice.call(root.querySelectorAll('.page-numbers'));
    if (!original.length) return;

    var currentElement = root.querySelector('.page-numbers.current');
    var current = currentElement ? numericValue(currentElement) : currentPageFromLocation();
    if (!current) current = 1;

    var pageHrefMap = {};
    var numericPages = [];
    var exemplarHref = '';

    original.forEach(function (element) {
      var page = numericValue(element);
      if (page) {
        numericPages.push(page);
        if (element.tagName === 'A' && element.href) {
          pageHrefMap[page] = element.href;
          if (!exemplarHref && (/\/page\/\d+\/?(?:$|[?#])/i.test(element.href) || /[?&]paged=\d+/i.test(element.href))) {
            exemplarHref = element.href;
          }
        }
      }
    });

    var total = numericPages.length ? Math.max.apply(Math, numericPages) : current;
    if (total <= 1) return;

    if (!exemplarHref) {
      var prevExisting = root.querySelector('.page-numbers.prev');
      var nextExisting = root.querySelector('.page-numbers.next');
      exemplarHref = (nextExisting && nextExisting.href) || (prevExisting && prevExisting.href) || window.location.href;
    }

    var nav = document.createElement('div');
    nav.className = 'nav-links mom-responsive-pagination';
    nav.style.display = 'flex';
    nav.style.flexWrap = 'nowrap';
    nav.style.alignItems = 'center';
    nav.style.justifyContent = 'center';
    nav.style.gap = '8px';
    nav.style.width = '100%';
    nav.style.maxWidth = '100%';
    nav.style.overflow = 'hidden';

    root.innerHTML = '';
    root.appendChild(nav);
    root.dataset.momResponsivePagination = '1';
    root.style.width = '100%';
    root.style.overflow = 'hidden';

    function hrefFor(page) {
      return pageHrefMap[page] || buildPageUrl(page, exemplarHref);
    }

    function render(radius) {
      nav.innerHTML = '';

      if (current > 1) {
        nav.appendChild(createArrow('prev', hrefFor(current - 1)));
      }

      var pages = {};
      pages[1] = true;
      pages[total] = true;
      pages[current] = true;

      for (var distance = 1; distance <= radius; distance += 1) {
        if (current - distance > 1) pages[current - distance] = true;
        if (current + distance < total) pages[current + distance] = true;
      }

      var ordered = Object.keys(pages).map(Number).sort(function (a, b) { return a - b; });
      var previousPage = 0;

      ordered.forEach(function (page) {
        if (previousPage && page - previousPage > 1) {
          nav.appendChild(createDots());
        }
        nav.appendChild(createPageLink(page, current, hrefFor(page)));
        previousPage = page;
      });

      if (current < total) {
        nav.appendChild(createArrow('next', hrefFor(current + 1)));
      }
    }

    function fits() {
      return nav.scrollWidth <= root.clientWidth + 1;
    }

    function fitToWidth() {
      if (!root.clientWidth) return;

      var maxRadius = Math.max(current - 1, total - current);
      var estimatedRadius = Math.max(0, Math.floor(root.clientWidth / 96));
      var radius = Math.min(maxRadius, estimatedRadius);

      render(radius);

      while (radius > 0 && !fits()) {
        radius -= 1;
        render(radius);
      }

      while (radius < maxRadius) {
        var nextRadius = radius + 1;
        render(nextRadius);
        if (!fits()) {
          render(radius);
          break;
        }
        radius = nextRadius;
      }
    }

    var scheduled = false;
    function scheduleFit() {
      if (scheduled) return;
      scheduled = true;
      window.requestAnimationFrame(function () {
        scheduled = false;
        fitToWidth();
      });
    }

    fitToWidth();

    if ('ResizeObserver' in window) {
      var observer = new ResizeObserver(scheduleFit);
      observer.observe(root);
    } else {
      window.addEventListener('resize', scheduleFit, { passive: true });
    }
  }

  function init() {
    Array.prototype.slice.call(document.querySelectorAll('.pagination')).forEach(enhancePagination);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
