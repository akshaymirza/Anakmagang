/* Kedayweb – Shared Tailwind Config (single source of truth) */
tailwind.config = {
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        "secondary-fixed": "#dce1ff",
        "surface-container-highest": "#d3e4fe",
        "primary": "#00236f",
        "on-tertiary-fixed": "#341100",
        "on-surface": "#0b1c30",
        "inverse-primary": "#b6c4ff",
        "tertiary-container": "#6e2c00",
        "primary-container": "#1e3a8a",
        "outline": "#757682",
        "on-error-container": "#93000a",
        "surface-bright": "#f8f9ff",
        "on-secondary-fixed-variant": "#374475",
        "surface-tint": "#4059aa",
        "on-tertiary-fixed-variant": "#773205",
        "on-tertiary": "#ffffff",
        "background": "#f8f9ff",
        "secondary-container": "#b7c4fd",
        "tertiary": "#4b1c00",
        "surface-container-low": "#eff4ff",
        "inverse-on-surface": "#eaf1ff",
        "on-tertiary-container": "#f39461",
        "on-primary-container": "#90a8ff",
        "secondary-fixed-dim": "#b7c4fd",
        "on-surface-variant": "#444651",
        "surface-container-high": "#dce9ff",
        "on-background": "#0b1c30",
        "surface": "#f8f9ff",
        "inverse-surface": "#213145",
        "error-container": "#ffdad6",
        "surface-container-lowest": "#ffffff",
        "on-secondary-container": "#435081",
        "surface-dim": "#cbdbf5",
        "tertiary-fixed-dim": "#ffb691",
        "on-secondary": "#ffffff",
        "primary-fixed": "#dce1ff",
        "outline-variant": "#c5c5d3",
        "on-primary-fixed-variant": "#264191",
        "tertiary-fixed": "#ffdbcb",
        "error": "#ba1a1a",
        "on-primary": "#ffffff",
        "secondary": "#4f5c8e",
        "on-secondary-fixed": "#071747",
        "primary-fixed-dim": "#b6c4ff",
        "on-error": "#ffffff",
        "surface-container": "#e5eeff",
        "on-primary-fixed": "#00164e",
        "surface-variant": "#d3e4fe",
        /* aliases used by projects page */
        "brand": "#1e3a8a",
        "canvas": "#f8f9ff",
        "line": "#c5c5d3"
      },
      borderRadius: {
        DEFAULT: "0.25rem",
        lg: "0.5rem",
        xl: "0.75rem",
        "2xl": "1rem",
        full: "9999px"
      },
      spacing: {
        "container-max": "1280px",
        md: "16px",
        base: "4px",
        xs: "4px",
        "3xl": "64px",
        gutter: "24px",
        xl: "32px",
        lg: "24px",
        sm: "8px",
        "2xl": "48px"
      },
      fontFamily: {
        "body-lg": ["Inter", "sans-serif"],
        "body-md": ["Inter", "sans-serif"],
        "body-sm": ["Inter", "sans-serif"],
        "label-md": ["Geist", "sans-serif"],
        "headline-xl": ["Geist", "sans-serif"],
        "headline-md": ["Geist", "sans-serif"],
        "headline-lg-mobile": ["Geist", "sans-serif"],
        "headline-lg": ["Geist", "sans-serif"],
        "label-sm": ["Geist", "sans-serif"],
        geist: ["Geist", "sans-serif"],
        inter: ["Inter", "sans-serif"]
      },
      fontSize: {
        "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }],
        "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }],
        "body-sm": ["14px", { lineHeight: "20px", fontWeight: "400" }],
        "label-md": ["14px", { lineHeight: "16px", letterSpacing: "0.05em", fontWeight: "600" }],
        "headline-xl": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "700" }],
        "headline-md": ["20px", { lineHeight: "28px", fontWeight: "600" }],
        "headline-lg-mobile": ["24px", { lineHeight: "32px", fontWeight: "600" }],
        "headline-lg": ["32px", { lineHeight: "40px", letterSpacing: "-0.01em", fontWeight: "600" }],
        "label-sm": ["12px", { lineHeight: "14px", fontWeight: "500" }]
      }
    }
  }
};

// ============================================================
// Timezone Helper: Memaksa waktu selalu mengikuti zona waktu WIB (Asia/Jakarta, UTC+7)
// ============================================================
window.getNowWIB = function(d) {
  const base = (d instanceof Date) ? d : new Date();
  try {
    return new Date(base.toLocaleString('en-US', { timeZone: 'Asia/Jakarta' }));
  } catch (e) {
    return new Date(base.getTime() + (7 * 3600 * 1000) + (base.getTimezoneOffset() * 60000));
  }
};

window.formatDateWIB = function(d = new Date()) {
  const w = window.getNowWIB(d);
  return `${w.getFullYear()}-${String(w.getMonth() + 1).padStart(2, '0')}-${String(w.getDate()).padStart(2, '0')}`;
};

window.formatTimeWIB = function(d = new Date(), withSeconds = false) {
  const w = window.getNowWIB(d);
  const pad = n => String(n).padStart(2, '0');
  return withSeconds
    ? `${pad(w.getHours())}:${pad(w.getMinutes())}:${pad(w.getSeconds())}`
    : `${pad(w.getHours())}:${pad(w.getMinutes())}`;
};

// ============================================================
// Dark Mode Theme Controller (Follows Chrome / System Default)
// ============================================================
function getSystemTheme() {
  return (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
}

window.applyTheme = function(theme) {
  const isDark = (theme === 'dark');
  if (isDark) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
  if (typeof window.updateThemeToggleUI === 'function') {
    window.updateThemeToggleUI();
  }
};

(function() {
  // Inisialisasi tema segera sebelum halaman selesai render agar tidak ada flicker
  try {
    // Reset preferensi manual lama satu kali agar pengguna langsung mengikuti default Chrome/Browser
    if (localStorage.getItem('km_theme_system_default_v1') !== 'true') {
      localStorage.removeItem('km_theme');
      localStorage.setItem('km_theme_system_default_v1', 'true');
    }

    const savedTheme = localStorage.getItem('km_theme');
    // Jika ada setelan manual dari tombol, ikuti itu; jika belum ada, otomatis ikuti setelan Chrome/Sistem
    const activeTheme = (savedTheme === 'dark' || savedTheme === 'light') ? savedTheme : getSystemTheme();
    window.applyTheme(activeTheme);
  } catch (e) {
    console.warn('Gagal memuat preferensi tema:', e);
  }

  // Real-time listener: jika pengguna mengubah setelan tema di Chrome/Sistem, halaman langsung beradaptasi
  try {
    const mql = window.matchMedia('(prefers-color-scheme: dark)');
    const onSystemThemeChange = function(e) {
      try {
        localStorage.removeItem('km_theme'); // Sinkronkan kembali ke pengaturan sistem
      } catch (err) {}
      window.applyTheme(e.matches ? 'dark' : 'light');
    };
    if (mql.addEventListener) {
      mql.addEventListener('change', onSystemThemeChange);
    } else if (mql.addListener) {
      mql.addListener(onSystemThemeChange);
    }
  } catch (e) {}

  // Pastikan style.css dimuat di semua halaman
  try {
    const links = Array.from(document.querySelectorAll('link[rel="stylesheet"]'));
    const hasStyle = links.some(l => (l.href || '').includes('style.css'));
    if (!hasStyle && document.head) {
      let cssPath = 'style.css';
      if (window.location.pathname.includes('/admin/Login/')) {
        cssPath = '../../style.css';
      } else if (window.location.pathname.includes('/admin/') || window.location.pathname.includes('/Login/')) {
        cssPath = '../style.css';
      }
      const link = document.createElement('link');
      link.rel = 'stylesheet';
      link.href = cssPath;
      document.head.appendChild(link);
    }
  } catch (e) {}
})();

window.updateThemeToggleUI = function() {
  const isDark = document.documentElement.classList.contains('dark');

  // 1. Ikon tombol aksi di header/navbar (klik untuk switch)
  // Saat gelap: tampilkan matahari (klik untuk ke mode terang)
  // Saat terang: tampilkan bulan (klik untuk ke mode gelap)
  document.querySelectorAll('.dark-icon-sun').forEach(el => {
    el.classList.toggle('hidden', !isDark);
  });
  document.querySelectorAll('.dark-icon-moon').forEach(el => {
    el.classList.toggle('hidden', isDark);
  });

  // 2. Ikon indikator di sidebar (menunjukkan tema saat ini)
  // Saat gelap: ikon bulan aktif (dark_mode)
  // Saat terang: ikon matahari aktif (light_mode)
  document.querySelectorAll('.sidebar-icon-moon').forEach(el => {
    el.classList.toggle('hidden', !isDark);
  });
  document.querySelectorAll('.sidebar-icon-sun').forEach(el => {
    el.classList.toggle('hidden', isDark);
  });

  // 3. Animasi toggle switch slider di sidebar
  document.querySelectorAll('.theme-toggle-switch').forEach(el => {
    if (isDark) {
      el.classList.remove('bg-slate-300');
      el.classList.add('bg-blue-600');
    } else {
      el.classList.remove('bg-blue-600');
      el.classList.add('bg-slate-300');
    }
  });
  document.querySelectorAll('.theme-toggle-knob').forEach(el => {
    el.style.transform = isDark ? 'translateX(14px)' : 'translateX(0px)';
  });
};

window.toggleDarkMode = function() {
  const isDark = document.documentElement.classList.toggle('dark');
  try {
    localStorage.setItem('km_theme', isDark ? 'dark' : 'light');
  } catch (e) {}
  window.updateThemeToggleUI();
  return isDark;
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', window.updateThemeToggleUI);
} else {
  window.updateThemeToggleUI();
}
