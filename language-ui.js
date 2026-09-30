/* InternSpace UI Enhancement Engine (i18n, Mobile Drawer, Keyboard Shortcuts) */
(function () {
  function indexDictionary() {
    const index = new Map();
    if (window.I18n && window.I18n.dict) {
      Object.entries(window.I18n.dict).forEach(([key, value]) => { index.set(value.en, key); index.set(value.id, key); });
    }
    return index;
  }

  function bind(root = document.body) {
    const lookup = indexDictionary(); if (!lookup.size || !root) return;
    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT), nodes = [];
    while (walker.nextNode()) nodes.push(walker.currentNode);
    nodes.forEach(node => {
      const text = node.textContent.trim(), parent = node.parentElement, key = lookup.get(text);
      if (key && parent && !parent.closest('script,style,[data-no-i18n]') && !parent.hasAttribute('data-no-i18n') && !parent.dataset.i18n) parent.dataset.i18n = key;
    });
    root.querySelectorAll('input[placeholder],textarea[placeholder]').forEach(input => {
      const key = lookup.get(input.placeholder);
      if (key) input.dataset.i18n = key;
    });
  }

  function addSwitcher() {
    if (document.querySelector('[data-language-switcher]')) return;
    const button = document.createElement('button');
    button.type = 'button'; button.dataset.languageSwitcher = 'true';
    button.className = 'fixed bottom-4 left-4 z-[100] rounded-full bg-primary px-4 py-2 text-xs font-bold text-white shadow-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-blue-300 transition-transform active:scale-95 flex items-center gap-1.5';
    button.innerHTML = '<span class="material-symbols-outlined text-[16px]">language</span><span data-i18n-toggle>EN / ID</span>';
    button.setAttribute('aria-label', 'Switch language');
    button.onclick = () => window.I18n && window.I18n.toggleLang();
    document.body.append(button);
  }

  function setupMobileDrawer() {
    const sidebar = document.querySelector('aside');
    const header = document.querySelector('header');
    if (!sidebar || !header) return;

    if (!header.querySelector('[data-mobile-menu-btn]')) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.dataset.mobileMenuBtn = 'true';
      btn.className = 'md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 mr-2 focus:outline-none';
      btn.innerHTML = '<span class="material-symbols-outlined">menu</span>';
      btn.onclick = () => toggleMobileSidebar();
      header.prepend(btn);
    }
  }

  function toggleMobileSidebar() {
    const sidebar = document.querySelector('aside');
    if (!sidebar) return;
    const isHidden = sidebar.classList.contains('hidden');
    if (isHidden) {
      sidebar.classList.remove('hidden');
      sidebar.classList.add('flex', 'fixed', 'inset-0', 'z-50', 'w-64', 'bg-white', 'shadow-2xl');
      let overlay = document.getElementById('mobile-drawer-overlay');
      if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'mobile-drawer-overlay';
        overlay.className = 'fixed inset-0 bg-slate-900/50 z-40 md:hidden';
        overlay.onclick = () => toggleMobileSidebar();
        document.body.appendChild(overlay);
      }
      overlay.classList.remove('hidden');
    } else {
      sidebar.classList.add('hidden');
      sidebar.classList.remove('flex', 'fixed', 'inset-0', 'z-50', 'w-64', 'shadow-2xl');
      const overlay = document.getElementById('mobile-drawer-overlay');
      if (overlay) overlay.classList.add('hidden');
    }
  }

  function setupKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
      if ((e.key === '/' || (e.ctrlKey && e.key === 'k')) && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
        e.preventDefault();
        const searchInput = document.querySelector('input[type="search"], input[placeholder*="Search"], input[placeholder*="Cari"]');
        if (searchInput) searchInput.focus();
      }
      if (e.key === 'Escape') {
        const modal = document.getElementById('modal');
        if (modal && !modal.classList.contains('hidden')) {
          modal.classList.add('hidden');
        }
        const overlay = document.getElementById('mobile-drawer-overlay');
        if (overlay && !overlay.classList.contains('hidden')) {
          toggleMobileSidebar();
        }
      }
    });
  }

  function refresh() { bind(); if (window.I18n) window.I18n.applyLang(); }

  function start() {
    refresh();
    addSwitcher();
    setupMobileDrawer();
    setupKeyboardShortcuts();
    new MutationObserver(records => records.forEach(record => record.addedNodes.forEach(node => {
      if (node.nodeType === Node.ELEMENT_NODE) bind(node);
    }))).observe(document.body, { childList: true, subtree: true });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
  window.refreshLanguage = refresh;
  window.toggleMobileSidebar = toggleMobileSidebar;
})();
