document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[data-validate]');

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
                showFormError(form, 'Please correct the errors before submitting.');
            }
        });

        // Real-time validation on input
        form.querySelectorAll('input, select, textarea').forEach(field => {
            ['blur', 'input'].forEach(eventType => {
                field.addEventListener(eventType, function() {
                    validateField(this);
                    updateSubmitButton(form);
                });
            });
        });
    });

    function validateForm(form) {
        let isValid = true;
        form.querySelectorAll('input, select, textarea').forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });
        return isValid;
    }

    function validateField(field) {
        const value = field.value.trim();
        const type = field.type;
        const required = field.hasAttribute('required');
        const pattern = field.getAttribute('pattern');
        const minLength = field.getAttribute('minlength');
        const maxLength = field.getAttribute('maxlength');
        let isValid = true;
        let errorMessage = '';

        // Remove existing error message
        removeErrorMessage(field);

        // Required field validation
        if (required && !value) {
            isValid = false;
            errorMessage = 'This field is required';
        } else if (value) {
            // Validate based on input type
            switch(type) {
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address';
                    }
                    break;
                case 'tel':
                    const phoneRegex = /^[\d\s\-\+\(\)]+$/;
                    if (!phoneRegex.test(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid phone number';
                    }
                    break;
                case 'password':
                    if (minLength && value.length < parseInt(minLength)) {
                        isValid = false;
                        errorMessage = `Password must be at least ${minLength} characters`;
                    } else if (!(/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/.test(value))) {
                        isValid = false;
                        errorMessage = 'Password must contain letters and numbers';
                    }
                    break;
            }

            // Pattern validation
            if (pattern && !new RegExp(pattern).test(value)) {
                isValid = false;
                errorMessage = field.getAttribute('data-pattern-message') || 'Please match the requested format';
            }

            // Length validation
            if (maxLength && value.length > parseInt(maxLength)) {
                isValid = false;
                errorMessage = `Maximum ${maxLength} characters allowed`;
            }
            }
        }

        // Phone validation
        if (field.getAttribute('data-type') === 'phone' && value) {
            const phoneRegex = /^[0-9]{10}$/;
            if (!phoneRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid 10-digit phone number';
            }
        }

        // Password validation
        if (type === 'password' && value) {
            if (value.length < 8) {
                isValid = false;
                errorMessage = 'Password must be at least 8 characters long';
            }
        }

        // Password confirmation
        if (field.getAttribute('data-match')) {
            const matchField = document.getElementById(field.getAttribute('data-match'));
            if (matchField && value !== matchField.value) {
                isValid = false;
                errorMessage = 'Passwords do not match';
            }
        }

        // Remove validating class and add appropriate status class
        if (isValid) {
            field.classList.add('is-valid');
            field.classList.remove('is-invalid');
            return true;
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            showErrorMessage(field, errorMessage);
            return false;
        }

        function showErrorMessage(field, message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = message;
        
            // Position the error message appropriately
            const container = field.parentNode;
            const existingError = container.querySelector('.invalid-feedback');
            if (existingError) {
                container.removeChild(existingError);
            }
            container.appendChild(errorDiv);
        }

        function removeErrorMessage(field) {
            const errorDiv = field.parentNode.querySelector('.invalid-feedback');
            if (errorDiv) {
                field.classList.remove('is-invalid');
                field.classList.remove('is-valid');
                errorDiv.remove();
            }
        }

        function showFormError(form, message) {
            const formError = document.createElement('div');
            formError.className = 'alert alert-danger mt-3';
            formError.textContent = message;
        
            // Remove existing form error if any
            const existingError = form.querySelector('.alert-danger');
            if (existingError) {
                existingError.remove();
            }
        
            form.insertBefore(formError, form.firstChild);
        
            // Auto-hide the error after 5 seconds
            setTimeout(() => {
                formError.remove();
            }, 5000);
        }

        function updateSubmitButton(form) {
            const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
            if (!submitButton) return;
        
            const isFormValid = Array.from(form.querySelectorAll('input, select, textarea'))
                .every(field => !field.classList.contains('is-invalid'));
        
            submitButton.disabled = !isFormValid;
            submitButton.classList.toggle('disabled', !isFormValid);
        }
});