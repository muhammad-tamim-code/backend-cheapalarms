(function () {
  'use strict';

  var menuBtn = document.querySelector('.sg-header__menu-btn');
  var mobilePanel = document.getElementById('sg-mobile-panel');
  var mobileBar = document.getElementById('sg-mobile-bar');
  var footer = document.getElementById('sg-footer');
  var reveals = document.querySelectorAll('.sg-reveal');

  if (menuBtn && mobilePanel) {
    menuBtn.addEventListener('click', function () {
      var expanded = menuBtn.getAttribute('aria-expanded') === 'true';
      menuBtn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
      mobilePanel.hidden = expanded;
    });
  }

  if ('IntersectionObserver' in window && reveals.length) {
    var revealObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            revealObserver.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.08, rootMargin: '0px 0px -24px 0px' }
    );

    reveals.forEach(function (el) {
      revealObserver.observe(el);
    });
  } else {
    reveals.forEach(function (el) {
      el.classList.add('is-visible');
    });
  }

  function updateMobileBar() {
    if (!mobileBar || window.innerWidth >= 768) {
      mobileBar.classList.remove('is-visible');
      mobileBar.setAttribute('aria-hidden', 'true');
      return;
    }

    var hide = false;
    if (footer) {
      var rect = footer.getBoundingClientRect();
      if (rect.top < window.innerHeight && rect.bottom > 0) {
        hide = true;
      }
    }

    if (hide) {
      mobileBar.classList.remove('is-visible');
      mobileBar.setAttribute('aria-hidden', 'true');
    } else {
      mobileBar.classList.add('is-visible');
      mobileBar.setAttribute('aria-hidden', 'false');
    }
  }

  window.addEventListener('scroll', updateMobileBar, { passive: true });
  window.addEventListener('resize', updateMobileBar);
  updateMobileBar();

  var faqTriggers = document.querySelectorAll('.sg-value-faq__trigger');
  faqTriggers.forEach(function (trigger) {
    trigger.addEventListener('click', function () {
      var item = trigger.closest('.sg-value-faq__item');
      var panel = item ? item.querySelector('.sg-value-faq__panel') : null;
      if (!item || !panel) {
        return;
      }

      var isOpen = trigger.getAttribute('aria-expanded') === 'true';
      trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
      item.classList.toggle('is-open', !isOpen);
      panel.hidden = isOpen;
    });
  });
})();
