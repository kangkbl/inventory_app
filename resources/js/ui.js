// === Helpers ===
const qs  = (s, r=document) => r.querySelector(s);
const qsa = (s, r=document) => Array.from(r.querySelectorAll(s));
const onReady = (fn)=> (document.readyState === "loading" ? document.addEventListener("DOMContentLoaded", fn, {once:true}) : fn());
const mqLg = window.matchMedia("(min-width: 1024px)");
const isDesktop = () => mqLg.matches;

// ---- guard util (hindari double binding global) ----
const __bound = Object.create(null);
const bindOnce = (key, fn) => { if (__bound[key]) return; __bound[key] = true; fn(); };

// === Dark Mode ===
const THEME_KEY = "theme";
function getInitialTheme() {
  const saved = localStorage.getItem(THEME_KEY);
  if (saved === "dark" || saved === "light") return saved;
  return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
}
function applyTheme(t) { document.documentElement.classList.toggle("dark", t === "dark"); }
function initTheme(root = document) {
  let theme = getInitialTheme(); applyTheme(theme);
  const btn = qs("#btn-dark-toggle", root);
  if (btn && !btn.dataset.uiBound) {
    btn.addEventListener("click", () => {
      theme = theme === "dark" ? "light" : "dark";
      localStorage.setItem(THEME_KEY, theme);
      applyTheme(theme);
    });
    btn.dataset.uiBound = "1";
  }
}

// === Sidebar ===
const SIDEBAR_COLLAPSED_KEY = "sidebarCollapsed";
function applySidebarCollapsed(collapsed) {
  const s = qs("#sidebar"); if (!s) return;
  s.classList.toggle("lg:w-72", !collapsed);
  s.classList.toggle("lg:w-24", collapsed);

  // hide texts on lg only
  const hideLg = qsa(".menu-item-text, .menu-group-title, .promo-box, .logo", s);
  const logoIcon = qsa(".logo-icon", s);
  hideLg.forEach(el => el.classList.toggle("lg:hidden", collapsed));
  logoIcon.forEach(el => el.classList.toggle("lg:hidden", !collapsed));
}
function openMobileSidebar() {
  const s = qs("#sidebar"); if (!s) return;
  s.classList.remove("-translate-x-full"); s.classList.add("translate-x-0");
  showBackdrop(closeMobileSidebar);
}
function closeMobileSidebar() {
  const s = qs("#sidebar"); if (!s) return;
  s.classList.add("-translate-x-full"); s.classList.remove("translate-x-0");
  hideBackdrop();
}
let removeBackdrop = null;
function showBackdrop(onClick) {
  const el = document.createElement("div");
  el.className = "fixed inset-0 z-[9998] bg-black/30 lg:hidden";
  el.addEventListener("click", onClick, { once: true });
  document.body.appendChild(el);
  removeBackdrop = () => { el.remove(); removeBackdrop = null; };
}
function hideBackdrop() { removeBackdrop?.(); }

function initSidebar(root = document) {
  const btn = qs("#btn-sidebar-toggle", root); const s = qs("#sidebar", root);
  if (!btn || !s) return;

  // hanya bind sekali per tombol
  if (!btn.dataset.uiBound) {
    let collapsed = JSON.parse(localStorage.getItem(SIDEBAR_COLLAPSED_KEY) || "false");
    applySidebarCollapsed(collapsed);

    btn.addEventListener("click", () => {
      if (isDesktop()) {
        collapsed = !collapsed;
        localStorage.setItem(SIDEBAR_COLLAPSED_KEY, JSON.stringify(collapsed));
        applySidebarCollapsed(collapsed);
      } else {
        const isOpen = s.classList.contains("translate-x-0");
        isOpen ? closeMobileSidebar() : openMobileSidebar();
      }
    });
    btn.dataset.uiBound = "1";
  }

  // bind listener perubahan breakpoint sekali saja secara global
  bindOnce("mqLgChangeSidebar", () => {
    mqLg.addEventListener("change", e => { if (e.matches) closeMobileSidebar(); });
  });
}

// === Topbar menu (mobile) ===
function initTopbarMenu(root = document) {
  const btn = qs("#btn-menu-toggle", root); const box = qs("#topbar-actions", root);
  if (!btn || !box) return;

  const setOpen = (open) => {
    btn.setAttribute("aria-expanded", String(open));
    if (isDesktop()) { box.classList.add("flex"); box.classList.remove("hidden"); }
    else { box.classList.toggle("hidden", !open); box.classList.toggle("flex", open); }
  };

  if (!btn.dataset.uiBound) {
    btn.addEventListener("click", () => setOpen(box.classList.contains("hidden")));
    btn.dataset.uiBound = "1";
  }

  bindOnce("mqLgChangeTopbar", () => {
    mqLg.addEventListener("change", e => setOpen(e.matches)); // true=force open on desktop
  });

  setOpen(isDesktop());
}

// === Dropdowns (notif & user) ===
function closeAllDropdowns() {
  qsa('[aria-haspopup="menu"][aria-expanded="true"]').forEach(b=>{
    b.setAttribute("aria-expanded","false");
  });
  qsa("#panel-notif, #panel-user").forEach(p=>{
    p.classList.add("invisible","opacity-0","translate-y-2");
  });
}

function setupDropdown(btnSel, panelSel, root = document) {
  const btn = qs(btnSel, root); const panel = qs(panelSel, root);
  if (!btn || !panel) return;

  // panel default tertutup
  panel.classList.add("invisible","opacity-0","translate-y-2");
  btn.setAttribute("aria-haspopup","menu");
  btn.setAttribute("aria-expanded","false");

  if (!btn.dataset.uiBound) {
    const open = () => { btn.setAttribute("aria-expanded","true"); panel.classList.remove("invisible","opacity-0","translate-y-2"); };
    const close = () => { btn.setAttribute("aria-expanded","false"); panel.classList.add("invisible","opacity-0","translate-y-2"); };

    btn.addEventListener("click", (e)=>{ e.stopPropagation(); (btn.getAttribute("aria-expanded")==="true") ? close() : (closeAllDropdowns(), open()); });
    qsa("[data-close]", panel).forEach(x=> x.addEventListener("click", close));
    btn.dataset.uiBound = "1";
  }

  // global handler close (klik di luar dan ESC) → sekali saja
  bindOnce("dropdownGlobals", () => {
    document.addEventListener("click", (e)=> {
      // jika klik di dalam panel/btn, abaikan; selain itu tutup semua
      if (!e.target.closest("#panel-notif, #panel-user, #btn-notif, #btn-user")) closeAllDropdowns();
    });
    document.addEventListener("keydown", (e)=>{ if (e.key === "Escape") closeAllDropdowns(); });
  });
}

function initDropdowns(root = document) {
  setupDropdown("#btn-notif", "#panel-notif", root);
  setupDropdown("#btn-user", "#panel-user", root);
}

// === ESC untuk nutup drawer mobile cepat ===
function initGlobalEsc() {
  bindOnce("globalEsc", () => {
    document.addEventListener("keydown", (e)=> { if (e.key === "Escape") closeMobileSidebar(); });
  });
}

// === Snapshot kondisi ===
function initSnapshot(root = document) {
  const container = root.querySelector('[data-snapshot-root]');
  if (!container) return;

  const endpoint = container.getAttribute('data-snapshot-endpoint');
  if (!endpoint) return;

  const setText = (selector, value) => {
    const el = container.querySelector(selector);
    if (el) el.textContent = value;
  };

  const applyData = (payload) => {
    const counts = payload?.counts ?? {};
    const percentages = payload?.percentages ?? {};

    setText('[data-snapshot-total]', String(payload?.total ?? 0));
    setText('[data-snapshot-count="baik"]', String(counts.baik ?? 0));
    setText('[data-snapshot-count="rusak"]', String(counts.rusak ?? 0));
    setText('[data-snapshot-count="perbaikan"]', String(counts.perbaikan ?? 0));
    setText('[data-snapshot-count="lainnya"]', String(counts.lainnya ?? 0));

    const updatePercent = (key) => {
      const pct = Number.isFinite(percentages[key]) ? percentages[key] : 0;
      setText(`[data-snapshot-percent="${key}"]`, `${pct}%`);
      const bar = container.querySelector(`[data-snapshot-bar="${key}"]`);
      if (bar) bar.style.width = `${pct}%`;
    };

    updatePercent('baik');
    updatePercent('rusak');
    updatePercent('perbaikan');

    const lainnyaContainer = container.querySelector('[data-snapshot-lainnya-container]');
    if (lainnyaContainer) {
      const lain = Number(counts.lainnya ?? 0);
      lainnyaContainer.classList.toggle('hidden', lain <= 0);
    }
  };

  let fetching = false;
  let queued = false;

  const refresh = async () => {
    if (fetching) {
      queued = true;
      return;
    }

    fetching = true;
    try {
      const response = await fetch(endpoint, {
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        cache: 'no-store',
      });

      if (!response.ok) throw new Error(`Request failed: ${response.status}`);

      const data = await response.json();
      applyData(data);
    } catch (err) {
      console.error('Gagal memuat snapshot kondisi', err);
    } finally {
      fetching = false;
      if (queued) {
        queued = false;
        refresh();
      }
    }
  };

  if (!container.dataset.snapshotBound) {
    container.dataset.snapshotBound = '1';
    bindOnce('snapshotRefreshListener', () => {
      window.addEventListener('snapshot-refresh', () => refresh());
    });
  }

  refresh();
}

// === Boot (idempotent) ===
function bootUI(root = document) {
  initTheme(root);
  initSidebar(root);
  initTopbarMenu(root);
  initDropdowns(root);
  initGlobalEsc();
  initSnapshot(root);
}

// === Start on first load & re-init on SPA navigation ===
onReady(() => bootUI(document));

// Livewire Navigate hooks
document.addEventListener('livewire:navigated', () => {
  // jalankan setelah DOM termorph
  requestAnimationFrame(() => bootUI(document));
});

document.addEventListener('livewire:navigating', () => {
  // optional: bereskan state sebelum pindah halaman
  closeAllDropdowns();
  closeMobileSidebar();
});

