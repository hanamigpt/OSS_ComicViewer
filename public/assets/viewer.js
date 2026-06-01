(() => {
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduceMotion || !('IntersectionObserver' in window)) {
    return;
  }

  const blocks = document.querySelectorAll('.comic-block');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) {
        return;
      }
      entry.target.classList.add('is-visible');
      observer.unobserve(entry.target);
    });
  }, { rootMargin: '120px 0px' });

  blocks.forEach((block) => {
    block.dataset.animate = 'true';
    observer.observe(block);
  });
})();
