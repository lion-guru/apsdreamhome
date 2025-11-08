// Property Details Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const propertyId = document.getElementById('propertyId').value;
    const userId = document.getElementById('userId')?.value;

    // Load recommendations when tabs are clicked
    document.querySelectorAll('#recommendationTabs button[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('show.bs.tab', function(e) {
            const target = e.target.getAttribute('data-bs-target').replace('#', '');
            loadRecommendations(target);
        });
    });

    // Initial load of similar properties
    loadRecommendations('similar');

    // Handle schedule visit form submission
    const scheduleForm = document.getElementById('scheduleVisitForm');
    if (scheduleForm) {
        scheduleForm.addEventListener('submit', handleScheduleSubmit);
    }

    // Functions to load recommendations
    function loadRecommendations(type) {
        const container = document.getElementById(`${type}Properties`);
        if (!container) return;

        const params = new URLSearchParams({
            property_id: propertyId,
            user_id: userId || ''
        });

        fetch(`/apsdreamhome/api/property_recommendations.php?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.recommendations[type]) {
                    container.innerHTML = data.recommendations[type]
                        .map(property => createPropertyCard(property))
                        .join('');
                }
            })
            .catch(error => {
                console.error('Error loading recommendations:', error);
                container.innerHTML = '<div class="col-12"><div class="alert alert-danger">Failed to load recommendations</div></div>';
            });
    }

    // Create property card HTML
    function createPropertyCard(property) {
        return `
            <div class="col-md-6 col-lg-4">
                <div class="card property-card h-100">
                    <img src="${property.image_url || '/assets/images/placeholder.jpg'}" 
                         class="card-img-top" alt="${property.title}">
                    <div class="card-body">
                        <h5 class="card-title">${property.title}</h5>
                        <p class="card-text">
                            <i class="fas fa-map-marker-alt"></i> ${property.location}<br>
                            <i class="fas fa-home"></i> ${property.property_type}<br>
                            <strong>₹${formatPrice(property.price)}</strong>
                        </p>
                        <a href="/apsdreamhome/property-details.php?id=${property.id}" 
                           class="btn btn-outline-primary">View Details</a>
                    </div>
                </div>
            </div>
        `;
    }

    // Handle schedule visit form submission
    async function handleScheduleSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const formData = new FormData(form);

        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Scheduling...';

            const response = await fetch('/apsdreamhome/api/schedule_visit.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.status === 'success') {
                showAlert('success', 'Visit scheduled successfully! Check your email for confirmation.');
                bootstrap.Modal.getInstance(document.getElementById('scheduleModal')).hide();
                form.reset();
            } else {
                showAlert('danger', data.error || 'Failed to schedule visit. Please try again.');
            }
        } catch (error) {
            console.error('Error scheduling visit:', error);
            showAlert('danger', 'System error. Please try again later.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Schedule Visit';
        }
    }

    // Helper functions
    function formatPrice(price) {
        return new Intl.NumberFormat('en-IN').format(price);
    }

    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.main-content').insertAdjacentElement('afterbegin', alertDiv);
        setTimeout(() => alertDiv.remove(), 5000);
    }
});
