<?php
$extraHead = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">';
?>
<div class="container py-5">
    <div class="text-center mb-5">
        <h1><i class="fas fa-robot me-2"></i>AI Property Suggestions</h1>
        <p class="lead text-muted">Personalized property recommendations powered by AI</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form id="aiSuggestionForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Property Type</label>
                            <select class="form-select" name="property_type" required>
                                <option value="">Select Type</option>
                                <option value="plot">Plot / Land</option>
                                <option value="house">House / Villa</option>
                                <option value="flat">Flat / Apartment</option>
                                <option value="commercial">Commercial</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Budget Range</label>
                            <select class="form-select" name="budget">
                                <option value="">Select Budget</option>
                                <option value="under_5_lakh">Under 5 Lakh</option>
                                <option value="5_to_10_lakh">5 - 10 Lakh</option>
                                <option value="10_to_20_lakh">10 - 20 Lakh</option>
                                <option value="20_to_50_lakh">20 - 50 Lakh</option>
                                <option value="above_50_lakh">Above 50 Lakh</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Preferred Location</label>
                            <select class="form-select" name="location">
                                <option value="">Select Location</option>
                                <option value="gorakhpur">Gorakhpur</option>
                                <option value="lucknow">Lucknow</option>
                                <option value="kushinagar">Kushinagar</option>
                                <option value="varanasi">Varanasi</option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="getSuggestionsBtn">
                                <i class="fas fa-magic me-2"></i>Get AI Suggestions
                            </button>
                        </div>
                    </form>

                    <div id="suggestionsResult" class="mt-4" style="display:none;">
                        <h5 class="mb-3"><i class="fas fa-lightbulb me-2 text-warning"></i>AI Suggestions</h5>
                        <div id="suggestionsList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('aiSuggestionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = document.getElementById('getSuggestionsBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Analyzing...';

    fetch('<?= BASE_URL ?>api/ai/suggestions', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-magic me-2"></i>Get AI Suggestions';
        const resultDiv = document.getElementById('suggestionsResult');
        const listDiv = document.getElementById('suggestionsList');

        if (data.success && data.suggestions && data.suggestions.length > 0) {
            listDiv.innerHTML = data.suggestions.map(s => 
                '<div class="alert alert-light border mb-2"><i class="fas fa-check-circle text-success me-2"></i>' + s + '</div>'
            ).join('');
        } else {
            listDiv.innerHTML = '<div class="alert alert-info">Please fill in all fields to get personalized suggestions.</div>';
        }
        resultDiv.style.display = 'block';
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-magic me-2"></i>Get AI Suggestions';
    });
});
</script>
