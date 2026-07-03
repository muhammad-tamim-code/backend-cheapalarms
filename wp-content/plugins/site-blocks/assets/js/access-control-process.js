/**
 * Access Control radial process — scroll reveal animation.
 */
(function () {
  'use strict';

  var radial = document.querySelector('.sg-ac-process__radial');
  if (!radial) {
    return;
  }

  var prefersReduced =
    window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (prefersReduced) {
    radial.classList.add('sg-ac-process--visible');
    return;
  }

  radial.classList.add('sg-ac-process--animate');

  if (!('IntersectionObserver' in window)) {
    radial.classList.add('sg-ac-process--visible');
    return;
  }

  var observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          radial.classList.add('sg-ac-process--visible');
          observer.disconnect();
        }
      });
    },
    { root: null, rootMargin: '0px 0px -10% 0px', threshold: 0.15 }
  );

  observer.observe(radial);
})();
