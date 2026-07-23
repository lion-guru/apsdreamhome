(function () {
  'use strict';

  window.toggleProfile = function () {
    document.getElementById('profileDropdown').classList.toggle('show');
  };

  document.addEventListener('click', function (e) {
    var userBox = document.querySelector('.user-box');
    var dropdown = document.getElementById('profileDropdown');
    if (userBox && dropdown && !userBox.contains(e.target)) {
      dropdown.classList.remove('show');
    }
  });

  window.toggleNotifications = function () {
    window.location.href = (window.BASE_URL || '') + '/admin/notifications';
  };

  window.toggleMessages = function () {
    window.location.href = (window.BASE_URL || '') + '/admin/messages';
  };

  setTimeout(function () {
    document.querySelectorAll('.alert').forEach(function (alert) {
      try {
        var bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
      } catch (e) {}
    });
  }, 5000);
})();
