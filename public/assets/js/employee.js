document.addEventListener('DOMContentLoaded', function () {
  var sidebarToggle = document.getElementById('sidebarToggle');
  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function () {
      var sidebar = document.querySelector('.employee-sidebar');
      if (sidebar) sidebar.classList.toggle('collapsed');
    });
  }
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (el) {
    return new bootstrap.Tooltip(el);
  });
});
