/* Lightweight progressive enhancement for lower-power and touch devices. */
(function () {
  const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
  const applyMotionPreference = () => document.documentElement.classList.toggle('reduce-motion', localStorage.getItem('internspace-motion') === 'reduce' || reducedMotion.matches);
  applyMotionPreference(); reducedMotion.addEventListener?.('change', applyMotionPreference);
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('img').forEach(image => { image.loading = 'lazy'; image.decoding = 'async'; });
    document.querySelectorAll('.glass-card, article, section').forEach(element => { element.style.contentVisibility = 'auto'; element.style.containIntrinsicSize = '1px 360px'; });
    const style = document.createElement('style');
    style.textContent = '.reduce-motion *, .reduce-motion *::before, .reduce-motion *::after{animation-duration:.01ms!important;animation-iteration-count:1!important;scroll-behavior:auto!important;transition-duration:.01ms!important}';
    document.head.append(style);
  }, { once: true });
})();
