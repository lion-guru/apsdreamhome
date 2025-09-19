/**
 * AI Property Suggestions Module
 * Handles client-side interactions for AI-powered property recommendations
 */
class AIPropertySuggestions {
    constructor() {
        this.suggestionForm = document.getElementById('ai-suggestion-form');
        this.suggestionResults = document.getElementById('ai-suggestion-results');
        this.loadingIndicator = document.getElementById('ai-loading-indicator');
        this.errorContainer = document.getElementById('ai-error-container');
        
        this.initEventListeners();
    }

    /**
     * Initialize event listeners for AI suggestion form
     */
    initEventListeners() {
        if (this.suggestionForm) {
            this.suggestionForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.generateSuggestions();
            });
        }
    }

    /**
     * Validate form input before submission
     * @returns {Object|null} Validated context or null
     */
    validateInput() {
        const propertyType = document.getElementById('property-type').value;
        const budget = document.getElementById('budget').value;
        const location = document.getElementById('location').value;

        const validationErrors = [];

        if (!propertyType) validationErrors.push('Property Type');
        if (!budget) validationErrors.push('Budget');
        if (!location) validationErrors.push('Location');

        if (validationErrors.length > 0) {
            this.displayError(`Please fill in the following fields: ${validationErrors.join(', ')}`);
            return null;
        }

        return { propertyType, budget, location };
    }

    /**
     * Generate AI property suggestions
     */
    async generateSuggestions() {
        const context = this.validateInput();
        if (!context) return;

        this.clearPreviousResults();
        this.showLoading();

        try {
            const response = await fetch('/user_ai_suggestions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(context)
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Unknown error occurred');
            }

            this.displaySuggestions(data.suggestions);
        } catch (error) {
            this.displayError(error.message);
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Clear previous suggestion results
     */
    clearPreviousResults() {
        if (this.suggestionResults) {
            this.suggestionResults.innerHTML = '';
        }
        this.hideError();
    }

    /**
     * Display AI-generated suggestions
     * @param {Array} suggestions List of property suggestions
     */
    displaySuggestions(suggestions) {
        if (!suggestions || suggestions.length === 0) {
            this.displayError('No suggestions found. Please try different criteria.');
            return;
        }

        const suggestionList = document.createElement('ul');
        suggestionList.classList.add('ai-suggestion-list');

        suggestions.forEach((suggestion, index) => {
            const suggestionItem = document.createElement('li');
            suggestionItem.classList.add('ai-suggestion-item');
            suggestionItem.innerHTML = `
                <div class="suggestion-header">
                    <span class="suggestion-number">Suggestion ${index + 1}</span>
                </div>
                <div class="suggestion-content">
                    ${suggestion}
                </div>
                <div class="suggestion-actions">
                    <button class="btn btn-primary save-suggestion">Save</button>
                    <button class="btn btn-secondary explore-suggestion">Explore</button>
                </div>
            `;
            suggestionList.appendChild(suggestionItem);
        });

        this.suggestionResults.appendChild(suggestionList);
    }

    /**
     * Show loading indicator
     */
    showLoading() {
        if (this.loadingIndicator) {
            this.loadingIndicator.style.display = 'block';
        }
    }

    /**
     * Hide loading indicator
     */
    hideLoading() {
        if (this.loadingIndicator) {
            this.loadingIndicator.style.display = 'none';
        }
    }

    /**
     * Display error message
     * @param {string} message Error message to display
     */
    displayError(message) {
        if (this.errorContainer) {
            this.errorContainer.textContent = message;
            this.errorContainer.style.display = 'block';
        }
    }

    /**
     * Hide error message
     */
    hideError() {
        if (this.errorContainer) {
            this.errorContainer.textContent = '';
            this.errorContainer.style.display = 'none';
        }
    }
}

// Initialize AI Suggestions on page load
document.addEventListener('DOMContentLoaded', () => {
    new AIPropertySuggestions();
});
