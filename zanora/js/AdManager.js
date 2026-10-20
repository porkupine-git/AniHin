// AdManager.js

// CONFIGURATION: Replace this with your actual ad network URL
const AD_SCRIPT_URL = "https://gz.pschenttidier.com/rfkcQnn8CnGJwZO6/143348";
const MAX_POPUPS = 1;

(function() {
  if (typeof window === 'undefined') return;

  // 1. Anti-Debug Protection
  document.addEventListener('contextmenu', (e) => e.preventDefault());
  
  document.addEventListener('keydown', (e) => {
    if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && ['I', 'J', 'C', 'U'].includes(e.key))) {
      e.preventDefault();
      document.body.innerHTML = "<div style='display:flex;justify-content:center;align-items:center;height:100vh;background:#000;color:#ef4444;font-family:sans-serif;font-size:18px;font-weight:bold;text-align:center;'>Access Denied.</div>";
    }
  });

  setInterval(() => {
    const start = performance.now();
    debugger;
    if (performance.now() - start > 100) {
      document.body.innerHTML = "<div style='display:flex;justify-content:center;align-items:center;height:100vh;background:#000;color:#ef4444;font-family:sans-serif;font-size:18px;font-weight:bold;text-align:center;'>Access Denied.</div>";
    }
  }, 1000);

  // 2. Ad Injection
  const script = document.createElement('script');
  script.type = 'text/javascript';
  script.async = true;
  script.setAttribute('data-cfasync', 'false');
  script.src = AD_SCRIPT_URL;
  document.head.appendChild(script);

  // 3. Popup Limiter
  if (!window.__adPopupLimiter) {
    window.__adPopupLimiter = true;
    window.__popupCount = 0;

    const secureOpen = function(...args) {
      if (window.__popupCount >= MAX_POPUPS) return null;
      window.__popupCount++;
      try { return window.open.apply(window, args); } catch (e) { return null; }
    };

    Object.defineProperty(window, 'open', { value: secureOpen, writable: false, configurable: false });

    document.addEventListener('click', (e) => {
      const a = e.target.closest('a');
      if (a && a.target === '_blank') {
        if (window.__popupCount >= MAX_POPUPS) {
          e.preventDefault();
          e.stopPropagation();
        } else {
          setTimeout(() => { window.__popupCount++; }, 50);
        }
      }
    }, true);
  }
})();