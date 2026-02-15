<div class="container py-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4"><i class="fas fa-magic text-primary me-2"></i>AI Property Suggestions</h2>
                    <p class="text-muted text-center mb-4">Let our AI help you find the perfect property based on your preferences.</p>
                    
                    <form id="ai-suggestion-form" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="property_type" class="form-label">Property Type</label>
                                <select id="property_type" name="property_type" class="form-select" required>
                                    <option value="">Select Property Type</option>
                                    <?php foreach ($property_types as $type): ?>
                                        <option value="<?= h($type['type_name']) ?>"><?= h($type['type_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="budget" class="form-label">Budget (₹)</label>
                                <input type="number" id="budget" name="budget" class="form-control" placeholder="e.g. 5000000" required>
                            </div>

                            <div class="col-md-6">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" id="location" name="location" class="form-control" placeholder="e.g. Lucknow, UP" required>
                            </div>

                            <div class="col-md-12">
                                <button type="submit" id="generate-btn" class="btn btn-primary w-100 py-2">
                                    <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                                    <i class="fas fa-search me-2"></i>Generate AI Suggestions
                                </button>
                            </div>
                        </div>
                    </form>

                    <div id="ai-loading-indicator" class="text-center d-none my-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-primary fw-bold">Analyzing market data and generating suggestions...</p>
                    </div>

                    <div id="ai-error-container" class="alert alert-danger d-none"></div>

                    <div id="ai-suggestion-results" class="mt-4 d-none">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h4 class="card-title mb-3"><i class="fas fa-lightbulb text-warning me-2"></i>Our Recommendations</h4>
                                <div id="suggestions-content" class="text-break" style="white-space: pre-wrap;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('ai-suggestion-form');
    const generateBtn = document.getElementById('generate-btn');
    const loadingIndicator = document.getElementById('ai-loading-indicator');
    const errorContainer = document.getElementById('ai-error-container');
    const resultsContainer = document.getElementById('ai-suggestion-results');
    const suggestionsContent = document.getElementById('suggestions-content');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Reset UI
        errorContainer.classList.add('d-none');
        resultsContainer.classList.add('d-none');
        loadingIndicator.classList.remove('d-none');
        generateBtn.disabled = true;
        generateBtn.querySelector('.spinner-border').classList.remove('d-none');

        const formData = {
            property_type: document.getElementById('property_type').value,
            budget: document.getElementById('budget').value,
            location: document.getElementById('location').value
        };

        try {
            const response = await fetch('<?= BASE_URL ?>api/ai/suggestions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                // Format markdown-like response to HTML if needed, but for now pre-wrap handles basic formatting
                suggestionsContent.textContent = data.suggestions;
                resultsContainer.classList.remove('d-none');
                
                // Scroll to results
                resultsContainer.scrollIntoView({ behavior: 'smooth' });
            } else {
                errorContainer.textContent = data.error || 'Failed to generate suggestions. Please try again.';
                errorContainer.classList.remove('d-none');
            }
        } catch (error) {
            console.error('Error:', error);
            errorContainer.textContent = 'An error occurred. Please check your connection and try again.';
            errorContainer.classList.remove('d-none');
        } finally {
            loadingIndicator.classList.add('d-none');
            generateBtn.disabled = false;
            generateBtn.querySelector('.spinner-border').classList.add('d-none');
        }
    });
});
</script>
