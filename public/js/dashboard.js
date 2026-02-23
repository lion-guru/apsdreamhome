// APS Dream Home - Enhanced Dashboard JavaScript

// Modern Dashboard Controller
class DashboardController {
  constructor() {
    this.init();
    this.setupEventListeners();
    this.initializeCharts();
    this.startRealTimeUpdates();
  }

  init() {
    console.log("APS Dream Home Dashboard initialized");
    this.setupMobileMenu();
    this.setupTooltips();
    this.setupNotifications();
  }

  setupEventListeners() {
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById("mobileMenuToggle");
    if (mobileMenuToggle) {
      mobileMenuToggle.addEventListener("click", () => this.toggleMobileMenu());
    }

    // Menu items
    document.querySelectorAll(".menu-item").forEach((item) => {
      item.addEventListener("click", (e) => this.handleMenuClick(e));
    });

    // Chart options
    document.querySelectorAll(".chart-option").forEach((option) => {
      option.addEventListener("click", (e) => this.handleChartOptionClick(e));
    });

    // Search functionality
    this.setupSearch();

    // User menu
    this.setupUserMenu();

    // Notification handling
    this.setupNotifications();
  }

  setupMobileMenu() {
    const sidebar = document.getElementById("sidebar");
    const mobileMenuToggle = document.getElementById("mobileMenuToggle");

    if (mobileMenuToggle && sidebar) {
      mobileMenuToggle.addEventListener("click", () => {
        sidebar.classList.toggle("active");
        this.updateMenuToggleIcon();
      });

      // Close menu when clicking outside
      document.addEventListener("click", (e) => {
        if (
          window.innerWidth <= 768 &&
          !sidebar.contains(e.target) &&
          !mobileMenuToggle.contains(e.target)
        ) {
          sidebar.classList.remove("active");
          this.updateMenuToggleIcon();
        }
      });
    }
  }

  updateMenuToggleIcon() {
    const mobileMenuToggle = document.getElementById("mobileMenuToggle");
    const sidebar = document.getElementById("sidebar");

    if (mobileMenuToggle && sidebar) {
      const icon = mobileMenuToggle.querySelector("i");
      if (sidebar.classList.contains("active")) {
        icon.className = "bi bi-x";
      } else {
        icon.className = "bi bi-list";
      }
    }
  }

  handleMenuClick(e) {
    e.preventDefault();
    const menuItem = e.currentTarget;

    // Remove active class from all menu items
    document.querySelectorAll(".menu-item").forEach((item) => {
      item.classList.remove("active");
    });

    // Add active class to clicked item
    menuItem.classList.add("active");

    // Close mobile menu after selection
    if (window.innerWidth <= 768) {
      const sidebar = document.getElementById("sidebar");
      if (sidebar) {
        sidebar.classList.remove("active");
        this.updateMenuToggleIcon();
      }
    }

    // Load content based on menu item
    const href = menuItem.getAttribute("href");
    if (href && href !== "#") {
      this.loadContent(href);
    }
  }

  handleChartOptionClick(e) {
    const option = e.currentTarget;
    const parent = option.parentElement;

    // Remove active class from all options
    parent.querySelectorAll(".chart-option").forEach((opt) => {
      opt.classList.remove("active");
    });

    // Add active class to clicked option
    option.classList.add("active");

    // Update chart data
    const chartType = option.textContent;
    this.updateChart(chartType);
  }

  setupSearch() {
    const searchInput = document.querySelector(".search-bar input");
    if (searchInput) {
      let searchTimeout;

      searchInput.addEventListener("input", (e) => {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();

        if (query.length >= 2) {
          searchTimeout = setTimeout(() => {
            this.performSearch(query);
          }, 300);
        }
      });

      searchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
          e.preventDefault();
          const query = e.target.value.trim();
          if (query) {
            this.performSearch(query);
          }
        }
      });
    }
  }

  performSearch(query) {
    console.log("Searching for:", query);
    // Implement search functionality
    this.showSearchResults(query);
  }

  showSearchResults(query) {
    // This would connect to your backend API
    console.log("Displaying search results for:", query);
    // Implement search results display
  }

  setupUserMenu() {
    const userMenu = document.querySelector(".user-menu");
    if (userMenu) {
      userMenu.addEventListener("click", (e) => {
        e.stopPropagation();
        this.toggleUserDropdown();
      });
    }

    // Close dropdown when clicking outside
    document.addEventListener("click", () => {
      this.closeUserDropdown();
    });
  }

  toggleUserDropdown() {
    // Implement user dropdown menu
    console.log("Toggle user dropdown");
  }

  closeUserDropdown() {
    // Close user dropdown
  }

  setupNotifications() {
    const notificationBtn = document.querySelector(".notification-btn");
    if (notificationBtn) {
      notificationBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        this.toggleNotifications();
      });
    }

    // Close notifications when clicking outside
    document.addEventListener("click", () => {
      this.closeNotifications();
    });
  }

  toggleNotifications() {
    // Implement notifications panel
    console.log("Toggle notifications panel");
    this.updateNotificationBadge();
  }

  closeNotifications() {
    // Close notifications panel
  }

  updateNotificationBadge() {
    const badge = document.querySelector(".notification-badge");
    if (badge) {
      // Update notification count
      const count = parseInt(badge.textContent);
      if (count > 0) {
        badge.textContent = count - 1;
        if (count - 1 === 0) {
          badge.style.display = "none";
        }
      }
    }
  }

  setupTooltips() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="tooltip"]'),
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }

  initializeCharts() {
    // Initialize dashboard charts
    this.initRevenueChart();
    this.initPropertyChart();
    this.initActivityChart();
  }

  initRevenueChart() {
    const chartContainer = document.querySelector(".chart-container");
    if (chartContainer) {
      // This would initialize a real chart library like Chart.js
      console.log("Initializing revenue chart");
      // Placeholder for chart initialization
    }
  }

  initPropertyChart() {
    const chartContainers = document.querySelectorAll(".chart-container");
    chartContainers.forEach((container, index) => {
      if (index === 1) {
        console.log("Initializing property chart");
        // Placeholder for property chart
      }
    });
  }

  initActivityChart() {
    // Initialize activity timeline chart
    console.log("Initializing activity chart");
  }

  updateChart(chartType) {
    console.log("Updating chart for:", chartType);
    // This would update the chart data based on the selected option
    this.refreshChartData(chartType);
  }

  refreshChartData(chartType) {
    // Fetch new chart data from API
    this.fetchChartData(chartType)
      .then((data) => {
        this.renderChart(data);
      })
      .catch((error) => {
        console.error("Error fetching chart data:", error);
      });
  }

  async fetchChartData(chartType) {
    // Simulate API call
    return new Promise((resolve) => {
      setTimeout(() => {
        resolve({
          labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
          data: [65, 78, 90, 81, 96, 105],
        });
      }, 500);
    });
  }

  renderChart(data) {
    console.log("Rendering chart with data:", data);
    // Implement chart rendering
  }

  loadContent(url) {
    console.log("Loading content for:", url);
    // Implement content loading via AJAX
    this.showLoadingSpinner();

    // Simulate content loading
    setTimeout(() => {
      this.hideLoadingSpinner();
    }, 1000);
  }

  showLoadingSpinner() {
    // Show loading spinner
    const spinner = document.createElement("div");
    spinner.className = "loading-spinner";
    spinner.innerHTML = '<div class="loading"></div>';
    document.body.appendChild(spinner);
  }

  hideLoadingSpinner() {
    // Hide loading spinner
    const spinner = document.querySelector(".loading-spinner");
    if (spinner) {
      spinner.remove();
    }
  }

  startRealTimeUpdates() {
    // Start real-time updates for dashboard data
    setInterval(() => {
      this.updateDashboardStats();
    }, 30000); // Update every 30 seconds
  }

  updateDashboardStats() {
    console.log("Updating dashboard stats...");
    // Fetch updated stats from API
    this.fetchDashboardStats()
      .then((stats) => {
        this.updateStatsDisplay(stats);
      })
      .catch((error) => {
        console.error("Error updating stats:", error);
      });
  }

  async fetchDashboardStats() {
    // Simulate API call for dashboard stats
    return new Promise((resolve) => {
      setTimeout(() => {
        resolve({
          properties: 248,
          customers: 1429,
          leads: 89,
          revenue: "₹24.5L",
        });
      }, 500);
    });
  }

  updateStatsDisplay(stats) {
    // Update stat cards with new data
    const statValues = document.querySelectorAll(".stat-value");
    if (statValues.length >= 4) {
      statValues[0].textContent = stats.properties;
      statValues[1].textContent = stats.customers.toLocaleString();
      statValues[2].textContent = stats.leads;
      statValues[3].textContent = stats.revenue;
    }
  }

  // Utility methods
  formatCurrency(amount) {
    return new Intl.NumberFormat("en-IN", {
      style: "currency",
      currency: "INR",
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(amount);
  }

  formatDate(date) {
    return new Intl.DateTimeFormat("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
    }).format(date);
  }

  formatNumber(num) {
    if (num >= 1000000) {
      return (num / 1000000).toFixed(1) + "M";
    } else if (num >= 1000) {
      return (num / 1000).toFixed(1) + "K";
    }
    return num.toString();
  }

  // Error handling
  handleError(error, context = "") {
    console.error(`Error in ${context}:`, error);
    this.showErrorMessage(error.message || "An error occurred");
  }

  showErrorMessage(message) {
    // Show error message to user
    const alert = document.createElement("div");
    alert.className = "alert alert-danger alert-dismissible fade show";
    alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

    const container = document.querySelector(".dashboard-content");
    if (container) {
      container.insertBefore(alert, container.firstChild);

      // Auto-hide after 5 seconds
      setTimeout(() => {
        alert.remove();
      }, 5000);
    }
  }

  showSuccessMessage(message) {
    // Show success message to user
    const alert = document.createElement("div");
    alert.className = "alert alert-success alert-dismissible fade show";
    alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

    const container = document.querySelector(".dashboard-content");
    if (container) {
      container.insertBefore(alert, container.firstChild);

      // Auto-hide after 3 seconds
      setTimeout(() => {
        alert.remove();
      }, 3000);
    }
  }
}

// Legacy functions for backward compatibility
function formatCurrency(amount) {
  return "₹" + parseFloat(amount).toLocaleString("en-IN");
}

function showLoading() {
  if (!$("#loadingOverlay").length) {
    $("body").append(`
            <div id="loadingOverlay" style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
            ">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        `);
  }
  $("#loadingOverlay").show();
}

function hideLoading() {
  $("#loadingOverlay").hide();
}

function confirmAction(message, callback) {
  if (confirm(message)) {
    callback();
  }
}

function copyToClipboard(text) {
  navigator.clipboard
    .writeText(text)
    .then(function () {
      showAlert("Copied to clipboard!", "success");
    })
    .catch(function (err) {
      console.error("Could not copy text: ", err);
    });
}

// Show alert
function showAlert(message, type = "info") {
  const alertClass = `alert-${type}`;
  const alert = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `);

  $("body").append(alert);

  setTimeout(function () {
    alert.fadeOut();
  }, 5000);
}

// Export table to CSV
function exportToCSV(tableId, filename) {
  const table = document.getElementById(tableId);
  if (!table) return;

  const rows = table.querySelectorAll("tr");
  const csv = [];

  rows.forEach((row) => {
    const cells = row.querySelectorAll("td, th");
    const rowData = [];
    cells.forEach((cell) => {
      rowData.push('"' + cell.textContent.trim() + '"');
    });
    csv.push(rowData.join(","));
  });

  const blob = new Blob([csv.join("\n")], { type: "text/csv" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = filename + ".csv";
  a.click();
  window.URL.revokeObjectURL(url);
}

// Print page
function printPage() {
  window.print();
}

// Initialize tooltips and popovers
$(function () {
  $('[data-toggle="tooltip"]').tooltip();
  $('[data-toggle="popover"]').popover();
});

// Auto-hide alerts after 5 seconds
$(document).ready(function () {
  setTimeout(function () {
    $(".alert:not(.alert-permanent)").fadeOut();
  }, 5000);
});

// Form validation helper
function validateForm(formId) {
  const form = document.getElementById(formId);
  if (!form) return false;

  const requiredFields = form.querySelectorAll("[required]");
  let isValid = true;

  requiredFields.forEach((field) => {
    if (!field.value.trim()) {
      field.classList.add("is-invalid");
      isValid = false;
    } else {
      field.classList.remove("is-invalid");
    }
  });

  return isValid;
}

// File upload preview
function previewFile(input, previewId) {
  const preview = document.getElementById(previewId);
  if (!preview) return;

  if (input.files && input.files[0]) {
    const reader = new FileReader();

    reader.onload = function (e) {
      preview.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" style="max-height: 150px;">`;
    };

    reader.readAsDataURL(input.files[0]);
  }
}

// Search functionality
function setupSearch(inputId, tableId) {
  const searchInput = document.getElementById(inputId);
  const table = document.getElementById(tableId);

  if (!searchInput || !table) return;

  searchInput.addEventListener("keyup", function () {
    const filter = this.value.toUpperCase();
    const rows = table.getElementsByTagName("tr");

    for (let i = 1; i < rows.length; i++) {
      const cells = rows[i].getElementsByTagName("td");
      let match = false;

      for (let j = 0; j < cells.length; j++) {
        if (cells[j].innerText.toUpperCase().indexOf(filter) > -1) {
          match = true;
          break;
        }
      }

      rows[i].style.display = match ? "" : "none";
    }
  });
}

// Pagination helper
function setupPagination(totalPages, currentPage, callback) {
  const pagination = $(".pagination");
  pagination.empty();

  // Previous button
  pagination.append(`
        <li class="page-item ${currentPage === 1 ? "disabled" : ""}">
            <a class="page-link" href="#" onclick="${currentPage > 1 ? callback + "(" + (currentPage - 1) + ")" : "return false"}">Previous</a>
        </li>
    `);

  // Page numbers
  for (let i = 1; i <= totalPages; i++) {
    pagination.append(`
            <li class="page-item ${i === currentPage ? "active" : ""}">
                <a class="page-link" href="#" onclick="${callback}(${i})">${i}</a>
            </li>
        `);
  }

  // Next button
  pagination.append(`
        <li class="page-item ${currentPage === totalPages ? "disabled" : ""}">
            <a class="page-link" href="#" onclick="${currentPage < totalPages ? callback + "(" + (currentPage + 1) + ")" : "return false"}">Next</a>
        </li>
    `);
}
