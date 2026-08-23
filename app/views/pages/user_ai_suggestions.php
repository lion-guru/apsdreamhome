<?php
$extraHead = '<link rel="stylesheet" href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css">';
?>
<div class="container py-5">
    <div class="text-center mb-5">
        <h1><i class="fas fa-robot me-2"></i><?= __('user_ai_suggestions_heading', 'AI Property Suggestions') ?></h1>
        <p class="lead text-muted"><?= __('user_ai_suggestions_subtitle', 'Personalized property recommendations powered by AI') ?></p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form id="aiSuggestionForm">
    <?php echo CSRFProtection::csrfField(); ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?= __('user_ai_suggestions_label_property_type', 'Property Type') ?></label>
                            <select class="form-select" name="property_type" required>
                                <option value=""><?= __('user_ai_suggestions_select_type', 'Select Type') ?></option>
                                <option value="plot"><?= __('user_ai_suggestions_type_plot', 'Plot / Land') ?></option>
                                <option value="house"><?= __('user_ai_suggestions_type_house', 'House / Villa') ?></option>
                                <option value="flat"><?= __('user_ai_suggestions_type_flat', 'Flat / Apartment') ?></option>
                                <option value="commercial"><?= __('user_ai_suggestions_type_commercial', 'Commercial') ?></option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold"><?= __('user_ai_suggestions_label_budget', 'Budget Range') ?></label>
                            <select class="form-select" name="budget">
                                <option value=""><?= __('user_ai_suggestions_select_budget', 'Select Budget') ?></option>
                                <option value="under_5_lakh"><?= __('user_ai_suggestions_budget_under5', 'Under 5 Lakh') ?></option>
                                <option value="5_to_10_lakh"><?= __('user_ai_suggestions_budget_5_10', '5 - 10 Lakh') ?></option>
                                <option value="10_to_20_lakh"><?= __('user_ai_suggestions_budget_10_20', '10 - 20 Lakh') ?></option>
                                <option value="20_to_50_lakh"><?= __('user_ai_suggestions_budget_20_50', '20 - 50 Lakh') ?></option>
                                <option value="above_50_lakh"><?= __('user_ai_suggestions_budget_above50', 'Above 50 Lakh') ?></option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold"><?= __('user_ai_suggestions_label_location', 'Preferred Location') ?></label>
                            <select class="form-select" name="location">
                                <option value=""><?= __('user_ai_suggestions_select_location', 'Select Location') ?></option>
                                <option value="gorakhpur"><?= __('city_gorakhpur', 'Gorakhpur') ?></option>
                                <option value="lucknow"><?= __('city_lucknow', 'Lucknow') ?></option>
                                <option value="kushinagar"><?= __('city_kushinagar', 'Kushinagar') ?></option>
                                <option value="varanasi"><?= __('city_varanasi', 'Varanasi') ?></option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="getSuggestionsBtn">
                                <i class="fas fa-magic me-2"></i><?= __('user_ai_suggestions_btn_get', 'Get AI Suggestions') ?>
                            </button>
                        </div>
                    </form>

                    <div id="suggestionsResult" class="mt-4 style-2248">
                        <h5 class="mb-3"><i class="fas fa-lightbulb me-2 text-warning"></i><?= __('user_ai_suggestions_results_heading', 'AI Suggestions') ?></h5>
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
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span><?= __('user_ai_suggestions_analyzing', 'Analyzing...') ?>';

    fetch('<?= BASE_URL ?>api/ai/suggestions', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-magic me-2"></i><?= __('user_ai_suggestions_btn_get', 'Get AI Suggestions') ?>';
        const resultDiv = document.getElementById('suggestionsResult');
        const listDiv = document.getElementById('suggestionsList');

        if (data.success && data.suggestions && data.suggestions.length > 0) {
            listDiv.innerHTML = data.suggestions.map(s => 
                '<div class="alert alert-light border mb-2"><i class="fas fa-check-circle text-success me-2"></i>' + s + '</div>'
            ).join('');
        } else {
            listDiv.innerHTML = '<div class="alert alert-info"><?= __('user_ai_suggestions_empty', 'Please fill in all fields to get personalized suggestions.') ?></div>';
        }
        resultDiv.style.display = 'block';
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-magic me-2"></i><?= __('user_ai_suggestions_btn_get', 'Get AI Suggestions') ?>';
    });
});
</script>
