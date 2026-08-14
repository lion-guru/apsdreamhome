<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-primary text-white p-4">
                    <h2 class="mb-0"><i class="fas fa-magic me-2"></i> <?= __('aigen_heading', [], 'Property Description Generator') ?></h2>
                    <p class="mb-0 text-white-50 mt-1"><?= __('aigen_subtitle', [], 'Generate compelling descriptions for your property with the help of AI.') ?></p>
                </div>
                
                <div class="card-body p-4 p-lg-5">
                    <form id="propertyForm">
    <?php echo CSRFProtection::csrfField(); ?>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="propertyType" class="form-label fw-bold"><?= __('aigen_label_type', [], 'Property Type') ?></label>
                                <select id="propertyType" class="form-select py-2" required>
                                    <option value=""><?= __('aigen_select', [], 'Select') ?></option>
                                    <?php foreach ($property_types as $type): ?>
                                        <option value="<?= h($type['type_name']); ?>">
                                            <?= h($type['type_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="location" class="form-label fw-bold"><?= __('aigen_label_location', [], 'Location') ?></label>
                                <input type="text" id="location" class="form-control py-2" required placeholder="<?= __('aigen_placeholder_location', [], 'e.g. Gorakhpur, UP') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="price" class="form-label fw-bold"><?= __('aigen_label_price', [], 'Price (â‚¹)') ?></label>
                                <input type="number" id="price" class="form-control py-2" required placeholder="<?= __('aigen_placeholder_price', [], 'e.g. 5000000') ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="bedrooms" class="form-label fw-bold"><?= __('aigen_label_bedrooms', [], 'Bedrooms') ?></label>
                                <input type="number" id="bedrooms" class="form-control py-2" placeholder="<?= __('aigen_placeholder_bedrooms', [], 'e.g. 3') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="bathrooms" class="form-label fw-bold"><?= __('aigen_label_bathrooms', [], 'Bathrooms') ?></label>
                                <input type="number" id="bathrooms" class="form-control py-2" placeholder="<?= __('aigen_placeholder_bathrooms', [], 'e.g. 2') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="area" class="form-label fw-bold"><?= __('aigen_label_area', [], 'Area (sq ft)') ?></label>
                                <input type="number" id="area" class="form-control py-2" required placeholder="<?= __('aigen_placeholder_area', [], 'e.g. 1200') ?>">
                            </div>
                            
                            <div class="col-12">
                                <label for="additionalFeatures" class="form-label fw-bold"><?= __('aigen_label_features', [], 'Additional Features') ?></label>
                                <textarea id="additionalFeatures" class="form-control py-2" rows="4" placeholder="<?= __('aigen_placeholder_features', [], 'Enter additional features like parking, garden, security, etc...') ?>"></textarea>
                            </div>
                            
                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill">
                                    <i class="fas fa-magic me-2"></i> <?= __('aigen_generate', [], 'Generate Description') ?>
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="loading text-center py-4 mt-4 border-top" id="loading" class="style-54390">
                        <div class="spinner-border text-primary mb-2" role="status"></div>
                        <p class="text-muted"><?= __('aigen_loading', [], 'AI is generating the description, please wait...') ?></p>
                    </div>
                    
                    <div id="resultContainer" class="mt-5 pt-4 border-top" class="style-54390">
                        <h4 class="fw-bold mb-3"><?= __('aigen_result_heading', [], 'Generated Description:') ?></h4>
                        <div id="generatedDescription" class="p-4 bg-light rounded-4 border position-relative" class="style-76392">
                        </div>
                        <div class="mt-3 text-end">
                            <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="copyToClipboard()">
                                <i class="fas fa-copy me-1"></i> <?= __('aigen_copy', [], 'Copy to Clipboard') ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('propertyForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const loading = document.getElementById('loading');
    const resultContainer = document.getElementById('resultContainer');
    const descriptionDiv = document.getElementById('generatedDescription');

    // Collect form data
    const details = `
        Property Type: ${document.getElementById('propertyType').value}
        Location: ${document.getElementById('location').value}
        Price: â‚¹${document.getElementById('price').value}
        Area: ${document.getElementById('area').value} sq ft
        Bedrooms: ${document.getElementById('bedrooms').value || 'N/A'}
        Bathrooms: ${document.getElementById('bathrooms').value || 'N/A'}
        Additional Features: ${document.getElementById('additionalFeatures').value || 'None'}
    `.trim();

    loading.style.display = 'block';
    resultContainer.style.display = 'none';
    descriptionDiv.textContent = '';

    try {
        const response = await fetch('<?= BASE_URL ?>/ai/content/description', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ details })
        });

        const data = await response.json();
        if (data.success) {
            descriptionDiv.textContent = data.description;
            resultContainer.style.display = 'block';
        } else {
            alert(`Error: ${data.error || '<?= __('aigen_error_generate', [], 'Failed to generate description') ?>'}`);
        }
    } catch (error) {
        alert(`Error: ${error.message}`);
    } finally {
        loading.style.display = 'none';
    }
});

function copyToClipboard() {
    const text = document.getElementById('generatedDescription').textContent;
    navigator.clipboard.writeText(text).then(() => {
        alert('<?= __('aigen_copied', [], 'Description copied to clipboard!') ?>');
    });
}
</script>
