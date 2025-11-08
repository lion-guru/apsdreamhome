document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('testimonialForm');
    if (!form) return;

    // Handle star rating
    const ratingInputs = form.querySelectorAll('input[name="rating"]');
    const starLabels = form.querySelectorAll('.rating-input label');
    
    // Initialize rating display
    function updateStarRating(selectedValue) {
        starLabels.forEach(star => {
            const starInput = document.getElementById(star.getAttribute('for'));
            if (starInput.value <= selectedValue) {
                star.classList.add('text-warning');
            } else {
                star.classList.remove('text-warning');
            }
        });
    }
    
    starLabels.forEach(label => {
        label.addEventListener('click', function() {
            const inputId = this.getAttribute('for');
            const input = document.getElementById(inputId);
            updateStarRating(input.value);
        });
    });

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        const spinner = submitBtn.querySelector('.spinner-border');
        const successMessage = document.getElementById('testimonialSuccess');
        
        try {
            // Show loading state
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            
            // Prepare form data
            const formData = {
                name: form.querySelector('#name').value.trim(),
                email: form.querySelector('#email').value.trim(),
                rating: form.querySelector('input[name="rating"]:checked')?.value || '',
                testimonial: form.querySelector('#testimonial').value.trim(),
                consent: form.querySelector('#consent').checked
            };
            
            // Submit to API
            const response = await fetch('api/submit_testimonial.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.message || 'Failed to submit testimonial');
            }
            
            if (result.success) {
                // Show success message
                form.classList.add('d-none');
                successMessage.classList.remove('d-none');
                
                // Reset form
                form.reset();
                starLabels.forEach(star => star.classList.remove('text-warning'));
                form.classList.remove('was-validated');
                
                // Scroll to success message
                successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Reset form after 5 seconds (optional)
                setTimeout(() => {
                    form.classList.remove('d-none');
                    successMessage.classList.add('d-none');
                }, 10000);
            } else {
                // Handle validation errors
                if (result.errors) {
                    Object.entries(result.errors).forEach(([field, message]) => {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            const feedback = input.nextElementSibling;
                            if (feedback && feedback.classList.contains('invalid-feedback')) {
                                feedback.textContent = message;
                            }
                        }
                    });
                }
                throw new Error(result.message || 'Validation failed');
            }
            
        } catch (error) {
            console.error('Error submitting testimonial:', error);
            
            // Show error message to user
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger mt-3';
            errorAlert.innerHTML = `
                <i class="fas fa-exclamation-circle me-2"></i>
                ${error.message || 'There was an error submitting your testimonial. Please try again later.'}
            `;
            
            // Insert after form or at the end of the card
            const cardBody = form.closest('.card-body');
            if (cardBody) {
                cardBody.insertBefore(errorAlert, form.nextSibling);
                
                // Remove error message after 5 seconds
                setTimeout(() => {
                    errorAlert.remove();
                }, 5000);
            }
            
        } finally {
            // Reset button state
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
        }
    });

    // Add Bootstrap validation styles
    form.addEventListener('input', function(e) {
        if (e.target.matches('input, textarea, select, [type="checkbox"]')) {
            const input = e.target;
            if (input.checkValidity()) {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            } else {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
            }
        }
    });
    
    // Handle click on stars directly (in case the label click doesn't work)
    form.querySelector('.rating-input').addEventListener('click', (e) => {
        const star = e.target.closest('label');
        if (star) {
            const input = document.getElementById(star.getAttribute('for'));
            if (input) {
                input.checked = true;
                updateStarRating(input.value);
            }
        }
    });
});
