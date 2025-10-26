<!-- // CSS vendor (dibundle Vite)
import "jsvectormap/dist/jsvectormap.min.css";
import "flatpickr/dist/flatpickr.min.css";
import "dropzone/dist/dropzone.css";

// Alpine & plugin persist
import Alpine from "alpinejs";
import persist from "@alpinejs/persist";

// Optional komponenmu
import flatpickr from "flatpickr";
import Dropzone from "dropzone";

import chart01 from "./components/charts/chart-01";
import chart02 from "./components/charts/chart-02";
import chart03 from "./components/charts/chart-03";
import map01 from "./components/map-01";
import "./components/calendar-init.js";
import "./components/image-resize";

// ===== Alpine init + Store bersama layout =====
Alpine.plugin(persist);

document.addEventListener("alpine:init", () => {
  // Store global untuk navbar <-> sidebar
  Alpine.store("layout", {
    // false = expanded (lg), true = mini/collapsed (lg) & open (mobile)
    sidebarToggle: Alpine.$persist(false).as("sidebarCollapsed"),
    menuOpen: false, // menu actions pada navbar mobile
    darkMode: Alpine.$persist(false).as("themeDark"),
  });

  // Jika belum ada preferensi user, ikuti system theme
  if (localStorage.getItem("themeDark") === null) {
    Alpine.store("layout").darkMode = window.matchMedia("(prefers-color-scheme: dark)").matches;
  }
});

// Terapkan class 'dark' pada <html> dan sync saat berubah
const applyDark = (v) => document.documentElement.classList.toggle("dark", v);
document.addEventListener("alpine:init", () => {
  applyDark(Alpine.store("layout").darkMode);
  Alpine.effect(() => applyDark(Alpine.store("layout").darkMode));
});

window.Alpine = Alpine;
Alpine.start();

// ===== Helpers =====
const onReady = (fn) => {
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", fn, { once: true });
  } else {
    fn();
  }
};

// ===== Inisialisasi setelah DOM siap =====
onReady(() => {
  // --- Flatpickr (aman & reusable) ---
  const datepickers = document.querySelectorAll(".datepicker");
  if (datepickers.length) {
    datepickers.forEach((el) => {
      flatpickr(el, {
        mode: "range",
        static: true,
        monthSelectorType: "static",
        dateFormat: "M j, Y",
        defaultDate: [new Date().setDate(new Date().getDate() - 6), new Date()],
        prevArrow:
          '<svg class="stroke-current" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15.25 6L9 12.25L15.25 18.5" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        nextArrow:
          '<svg class="stroke-current" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M8.75 19L15 12.75L8.75 6.5" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        onReady: (_selectedDates, dateStr, instance) => {
          instance.element.value = dateStr.replace("to", "-");
          const customClass = instance.element.getAttribute("data-class");
          if (customClass) instance.calendarContainer.classList.add(customClass);
        },
        onChange: (_selectedDates, dateStr, instance) => {
          instance.element.value = dateStr.replace("to", "-");
        },
      });
    });
  }

  // --- Dropzone (aktif hanya jika elemen ada) ---
  const dz = document.querySelector("#demo-upload");
  if (dz) {
    // eslint-disable-next-line no-new
    new Dropzone("#demo-upload", { url: "/file/post" });
  }

  // --- Charts & Map (panggil kalau modulnya ada) ---
  try { chart01?.(); } catch {}
  try { chart02?.(); } catch {}
  try { chart03?.(); } catch {}
  try { map01?.(); } catch {}

  // --- Tahun footer ---
  const year = document.getElementById("year");
  if (year) year.textContent = new Date().getFullYear();

  // --- Copy helper (aman) ---
  const copyInput = document.getElementById("copy-input");
  if (copyInput) {
    const copyButton = document.getElementById("copy-button");
    const copyText = document.getElementById("copy-text");
    const websiteInput = document.getElementById("website-input");

    copyButton?.addEventListener("click", async () => {
      if (!websiteInput) return;
      try {
        await navigator.clipboard.writeText(websiteInput.value);
        if (copyText) {
          const prev = copyText.textContent;
          copyText.textContent = "Copied";
          setTimeout(() => (copyText.textContent = prev ?? "Copy"), 2000);
        }
      } catch {
        // fallback diam-diam
      }
    });
  }

  // --- Hapus semua logic search bar (di-remove total) ---
});

-->
<script>
(function () {
  // Set ke "" untuk pakai waktu perangkat (lokal browser).
  // Atau isi "Asia/Jakarta" kalau mau dipaksa WIB.
  const FORCED_TIMEZONE = ""; // contoh: "Asia/Jakarta"

  function formatTime(tz) {
    const fmt = new Intl.DateTimeFormat('en-GB', {
      hour: '2-digit', minute: '2-digit', second: '2-digit',
      hour12: false,
      timeZone: tz || undefined
    });
    // pastikan pemisah ":" (bukan titik)
    const parts = fmt.formatToParts(new Date());
    const get = (t) => parts.find(p => p.type === t)?.value || '00';
    return `${get('hour')}:${get('minute')}:${get('second')}`;
  }

  function getTzAbbr(tz) {
    try {
      const opts = { timeZone: tz || undefined, timeZoneName: 'short' };
      const str = new Intl.DateTimeFormat('en-GB', opts).format(new Date());
      const match = str.match(/\b([A-Z]{2,5})(?:[+-]\d{1,2})?\b/);
      return match ? match[1] : '';
    } catch { return ''; }
  }

  function updateClock() {
    const el = document.getElementById('local-time');
    if (!el) return;
    el.textContent = formatTime(FORCED_TIMEZONE || null);

    const tzSpan = document.getElementById('tz-abbr');
    if (tzSpan) {
      const abbr = FORCED_TIMEZONE ? getTzAbbr(FORCED_TIMEZONE) : getTzAbbr();
      tzSpan.textContent = abbr ? abbr : '';
      tzSpan.style.display = abbr ? '' : 'none';

      const titleEl = document.getElementById('clock-pill');
      if (titleEl) {
        const full = new Intl.DateTimeFormat(undefined, {
          weekday: 'short', year: 'numeric', month: 'short', day: '2-digit',
          hour: '2-digit', minute: '2-digit', second: '2-digit',
          hour12: false, timeZone: FORCED_TIMEZONE || undefined
        }).format(new Date());
        titleEl.title = full + (abbr ? ` (${abbr})` : '');
      }
    }
  }

  function start() {
    updateClock();
    setInterval(updateClock, 1000);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start, { once: true });
  } else {
    start();
  }
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  window.addEventListener('close-dialog', () => {
    document.getElementById('dialog')?.close();
  });
  window.addEventListener('notify', (e) => {
    const detail = e.detail ?? {};
    const message = detail.body ?? 'Perubahan berhasil disimpan.';
    const title = detail.title ?? 'Berhasil';
    const icon = detail.icon ?? 'success';
    const timer = detail.timer ?? 2000;
    const showConfirmButton = detail.showConfirmButton ?? false;

    if (window.Swal) {
      Swal.fire({
        icon,
        title,
        text: message,
        timer,
        timerProgressBar: timer > 0,
        showConfirmButton,
      });
    } else {
      // fallback sederhana bila SweetAlert belum termuat
      alert(message);
    }
  });

  window.addEventListener('confirm-delete', (e) => {
    const detail = e.detail ?? {};
    const {
      id,
      eventName,
      event: legacyEvent,
      title = 'Hapus data?',
      text = 'Tindakan ini tidak dapat dibatalkan.',
      icon = 'warning',
      confirmButtonText = 'Ya, hapus',
      cancelButtonText = 'Batal',
      payloadKey = 'id',
    } = detail;

    const resolvedEvent = eventName ?? legacyEvent ?? '';

    if (!resolvedEvent || typeof id === 'undefined' || id === null) {
      return;
    }

    const dispatchDelete = () => {
      if (typeof Livewire !== 'undefined' && typeof Livewire.dispatch === 'function') {
        Livewire.dispatch(resolvedEvent, { [payloadKey]: id });
      }
    };

    if (!window.Swal) {
      if (window.confirm(text)) {
        dispatchDelete();
      }
      return;
    }

    Swal.fire({
      title,
      text,
      icon,
      showCancelButton: true,
      confirmButtonText,
      cancelButtonText,
      reverseButtons: true,
      focusCancel: true,
    }).then((result) => {
      if (result.isConfirmed) {
        dispatchDelete();
      }
    });
  });
</script>