// Toggle the side navigation
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }

    // Add active class to current nav item
    const currentPage = window.location.pathname;
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Handle form validation
function validateForm(form) {
    if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
    }
    form.classList.add('was-validated');
    return form.checkValidity();
}

// Handle AJAX form submissions
function submitFormAjax(form, successCallback = null, errorCallback = null) {
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: form.method,
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (successCallback) {
                successCallback(data);
            } else {
                showAlert('success', data.message || 'Operation completed successfully.');
            }
        } else {
            if (errorCallback) {
                errorCallback(data);
            } else {
                showAlert('danger', data.message || 'An error occurred.');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An unexpected error occurred.');
    });
}

// Show alert message
function showAlert(type, message, permanent = false) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show${permanent ? ' alert-permanent' : ''}`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const alertContainer = document.querySelector('.alert-container') || document.querySelector('main');
    alertContainer.insertBefore(alertDiv, alertContainer.firstChild);
    
    if (!permanent) {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alertDiv);
            bsAlert.close();
        }, 5000);
    }
}

// Handle file uploads with preview
function handleFileUpload(input, previewElement) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            if (previewElement.tagName === 'IMG') {
                previewElement.src = e.target.result;
            } else {
                previewElement.style.backgroundImage = `url(${e.target.result})`;
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Format currency
function formatCurrency(amount, currency = 'INR') {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

// Format date
function formatDate(date, format = 'long') {
    const options = format === 'long' 
        ? { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
        : { year: 'numeric', month: 'short', day: 'numeric' };
    
    return new Date(date).toLocaleDateString('en-IN', options);
}

// Handle confirmation dialogs
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Handle dynamic form fields
function addFormField(container, template) {
    const newField = template.cloneNode(true);
    container.appendChild(newField);
    return newField;
}

function removeFormField(field) {
    field.remove();
}

// Handle notifications
function checkNotifications() {
    fetch('/apsdreamhomefinal/admin/api/check_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.count > 0) {
                document.querySelector('.notification-badge').textContent = data.count;
                document.querySelector('.notification-badge').style.display = 'inline';
            } else {
                document.querySelector('.notification-badge').style.display = 'none';
            }
        });
}

// Check for new notifications every minute
setInterval(checkNotifications, 60000);
