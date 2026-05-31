/**
 * APS Dream Home - Admin Dashboard JS
 * Simple version - No IIFE, No try-catch to ensure it loads
 */

// Define sidebar toggle functions directly
window.toggleSidebarSection = function (id) {
  var ul = document.getElementById(id);
  if (!ul) return;
  var hidden = ul.style.display === 'none';
  ul.style.display = hidden ? '' : 'none';
  var arrow = document.getElementById('arrow-' + id);
  if (arrow) {
    arrow.classList.toggle('collapsed', !hidden);
  }
  var savedState = localStorage.getItem('adminSidebarSections');
  var state = savedState ? JSON.parse(savedState) : {};
  state[id] = hidden;
  localStorage.setItem('adminSidebarSections', JSON.stringify(state));
};

window.toggleAllSidebarSections = function () {
  var menus = document.querySelectorAll('.sidebar-menu[id]');
  var anyHidden = Array.from(menus).some(function (el) {
    return el.style.display === 'none';
  });
  menus.forEach(function (el) {
    el.style.display = anyHidden ? '' : 'none';
    var savedState = localStorage.getItem('adminSidebarSections');
    var state = savedState ? JSON.parse(savedState) : {};
    state[el.id] = anyHidden;
    localStorage.setItem('adminSidebarSections', JSON.stringify(state));
  });
  document.querySelectorAll('.sidebar-sec-arrow[id^="arrow-sec-"]').forEach(function (arr) {
    arr.classList.toggle('collapsed', !anyHidden);
  });
};

// Load saved state on DOM ready
document.addEventListener('DOMContentLoaded', function () {
  var savedState = localStorage.getItem('adminSidebarSections');
  if (savedState) {
    try {
      var state = JSON.parse(savedState);
      Object.keys(state).forEach(function (id) {
        var ul = document.getElementById(id);
        var arrow = document.getElementById('arrow-' + id);
        if (ul) {
          ul.style.display = state[id] ? '' : 'none';
        }
        if (arrow) {
          if (state[id]) {
            arrow.classList.remove('collapsed');
          } else {
            arrow.classList.add('collapsed');
          }
        }
      });
    } catch (e) {}
  }
});
