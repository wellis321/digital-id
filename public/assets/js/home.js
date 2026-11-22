/* Digital ID front page: reveal sections as they scroll into view. Without this script everything is simply visible. */
(function () {
  if (!('IntersectionObserver' in window)) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  var targets = document.querySelectorAll('.dh-head2, .dh-ladder, .dh-leave, .dh-story, .dh-joins, .dh-list, .dh-not, .dh-demo__form, .dh-faq, .dh-close .dh-wrap');
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } });
  }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
  targets.forEach(function (el) { el.classList.add('dh-rise'); io.observe(el); });
})();
