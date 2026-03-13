// ── QuickChatPDF — app.js ─────────────────────────────────

// ── Smooth scroll ─────────────────────────────────────────
document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
  anchor.addEventListener("click", function (e) {
    const target = document.querySelector(this.getAttribute("href"));
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  });
});

// ── Navbar shadow on scroll ───────────────────────────────
window.addEventListener("scroll", function () {
  const nav = document.querySelector(".qcp-navbar");
  if (nav) {
    nav.style.boxShadow =
      window.scrollY > 10 ? "0 4px 30px rgba(0,0,0,.08)" : "none";
  }
});

// ── Toast System ──────────────────────────────────────────
const QCPToast = (function () {
  const ICONS = {
    success: "bi-check-circle-fill",
    danger: "bi-x-circle-fill",
    warning: "bi-exclamation-triangle-fill",
    info: "bi-info-circle-fill",
  };

  const COLORS = {
    success: "#22c55e",
    danger: "#ef4444",
    warning: "#f97316",
    info: "#3b82f6",
  };

  let container = null;

  function getContainer() {
    if (!container) {
      container = document.createElement("div");
      container.id = "qcp-toast-container";
      container.style.cssText =
        "position:fixed;bottom:24px;right:24px;z-index:99999;" +
        "display:flex;flex-direction:column;gap:10px;" +
        "max-width:340px;width:calc(100% - 48px);pointer-events:none";
      document.body.appendChild(container);
    }
    return container;
  }

  function show(message, type, duration) {
    type = type || "info";
    duration = duration || 4000;

    const icon = ICONS[type] || ICONS.info;
    const color = COLORS[type] || COLORS.info;
    const c = getContainer();

    const toast = document.createElement("div");
    toast.style.cssText =
      "background:#fff;" +
      "border:1px solid #e2e8f0;" +
      "border-left:4px solid " +
      color +
      ";" +
      "border-radius:12px;" +
      "padding:14px 16px;" +
      "display:flex;align-items:flex-start;gap:12px;" +
      "box-shadow:0 8px 30px rgba(0,0,0,.1);" +
      "pointer-events:all;" +
      "opacity:0;transform:translateX(20px);" +
      "transition:opacity .3s ease,transform .3s ease;" +
      "position:relative;";

    toast.innerHTML =
      '<i class="bi ' +
      icon +
      '" style="color:' +
      color +
      ';font-size:1.1rem;flex-shrink:0;margin-top:1px"></i>' +
      '<div style="flex:1;min-width:0">' +
      '<div style="font-size:.88rem;font-weight:500;color:#0f172a;line-height:1.5;word-break:break-word">' +
      message +
      "</div>" +
      "</div>" +
      '<button style="background:none;border:none;padding:0;cursor:pointer;color:#94a3b8;' +
      'font-size:.9rem;flex-shrink:0;line-height:1;margin-top:1px" ' +
      'onclick="this.parentElement.remove()">' +
      '<i class="bi bi-x-lg"></i>' +
      "</button>";

    // Progress bar
    const progress = document.createElement("div");
    progress.style.cssText =
      "position:absolute;bottom:0;left:0;height:3px;" +
      "background:" +
      color +
      ";opacity:.3;border-radius:0 0 12px 12px;" +
      "width:100%;transition:width linear " +
      duration +
      "ms";
    toast.appendChild(progress);

    c.appendChild(toast);

    // Animate in
    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        toast.style.opacity = "1";
        toast.style.transform = "translateX(0)";
        progress.style.width = "0%";
      });
    });

    // Auto remove
    const timer = setTimeout(function () {
      remove(toast);
    }, duration);

    // Pause on hover
    toast.addEventListener("mouseenter", function () {
      clearTimeout(timer);
      progress.style.transition = "none";
    });

    return toast;
  }

  function remove(toast) {
    if (!toast || !toast.parentElement) return;
    toast.style.opacity = "0";
    toast.style.transform = "translateX(20px)";
    setTimeout(function () {
      if (toast.parentElement) toast.remove();
    }, 300);
  }

  // Shorthand methods
  return {
    show: show,
    success: function (msg, dur) {
      return show(msg, "success", dur);
    },
    error: function (msg, dur) {
      return show(msg, "danger", dur);
    },
    warning: function (msg, dur) {
      return show(msg, "warning", dur);
    },
    info: function (msg, dur) {
      return show(msg, "info", dur);
    },
  };
})();

// ── Flash messages from PHP session ──────────────────────
document.addEventListener("DOMContentLoaded", function () {
  const flash = document.getElementById("qcp-flash-data");
  if (!flash) return;
  const msg = flash.dataset.message;
  const type = flash.dataset.type || "info";
  if (msg) QCPToast.show(msg, type);
});
