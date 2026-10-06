/**
 * APS Dream Home - Admin Dashboard JS
 * Delegates sidebar logic to APS namespace (defined in unified.php head).
 * Only runs fallback if APS is not available (non-unified layouts).
 */

// Sidebar toggle — delegate to APS if available, otherwise standalone fallback
window.toggleSidebarSection = function (id) {
  if (window.APS && APS.toggleSection) return APS.toggleSection(id);
  // Fallback
  var ul = document.getElementById(id);
  if (!ul) return;
  var hidden = ul.style.display === 'none';
  ul.style.display = hidden ? '' : 'none';
  var arrow = document.getElementById('arrow-' + id);
  if (arrow) arrow.classList.toggle('collapsed', !hidden);
  var saved = sessionStorage.getItem('adminSidebarSections');
  var state = saved ? JSON.parse(saved) : {};
  state[id] = hidden;
  sessionStorage.setItem('adminSidebarSections', JSON.stringify(state));
};

window.toggleAllSidebarSections = function () {
  if (window.APS && APS.toggleAllSections) return APS.toggleAllSections();
  // Fallback
  var menus = document.querySelectorAll('.sidebar-menu[id]');
  var anyHidden = Array.from(menus).some(function (el) { return el.style.display === 'none'; });
  menus.forEach(function (el) {
    el.style.display = anyHidden ? '' : 'none';
    var saved = sessionStorage.getItem('adminSidebarSections');
    var state = saved ? JSON.parse(saved) : {};
    state[el.id] = anyHidden;
    sessionStorage.setItem('adminSidebarSections', JSON.stringify(state));
  });
  document.querySelectorAll('.sidebar-sec-arrow[id^="arrow-sec-"]').forEach(function (arr) {
    arr.classList.toggle('collapsed', !anyHidden);
  });
};

// Load saved state — only if APS didn't already handle it
document.addEventListener('DOMContentLoaded', function () {
  if (window.APS && APS._init) return; // APS handles its own restore
  // One-time migration from the old shared localStorage key.
  try {
    if (!sessionStorage.getItem('adminSidebarSections')) {
      var legacy = localStorage.getItem('adminSidebarSections');
      if (legacy) {
        sessionStorage.setItem('adminSidebarSections', legacy);
        localStorage.removeItem('adminSidebarSections');
      }
    }
  } catch (e) {}
  var saved = sessionStorage.getItem('adminSidebarSections');
  if (!saved) return;
  try {
    var state = JSON.parse(saved);
    Object.keys(state).forEach(function (id) {
      var ul = document.getElementById(id);
      var arrow = document.getElementById('arrow-' + id);
      if (ul) ul.style.display = state[id] ? '' : 'none';
      if (arrow) arrow.classList.toggle('collapsed', !state[id]);
    });
  } catch (e) {}
});

// Auto-dismiss alerts
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
    setTimeout(function () {
      var close = alert.querySelector('.btn-close');
      if (close) close.click();
    }, 5000);
  });
});

// Highlight active sidebar link
document.addEventListener('DOMContentLoaded', function () {
  var path = window.location.pathname;
  document.querySelectorAll('.sidebar-link').forEach(function (link) {
    if (link.getAttribute('href') === path) {
      link.classList.add('active');
      // Expand parent section
      var parent = link.closest('.sidebar-menu');
      if (parent) parent.style.display = '';
    }
  });
});
