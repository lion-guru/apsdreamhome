<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-primary text-white p-4">
                    <h2 class="mb-0"><i class="fas fa-magic me-2"></i> Property Description Generator</h2>
                    <p class="mb-0 text-white-50 mt-1">AI की मदद से अपनी प्रॉपर्टी के लिए बेहतरीन विवरण तैयार करें।</p>
                </div>
                
                <div class="card-body p-4 p-lg-5">
                    <form id="propertyForm">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="propertyType" class="form-label fw-bold">Property Type</label>
                                <select id="propertyType" class="form-select py-2" required>
                                    <option value="">चुनें</option>
                                    <?php foreach ($property_types as $type): ?>
                                        <option value="<?= h($type['type_name']); ?>">
                                            <?= h($type['type_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="location" class="form-label fw-bold">Location</label>
                                <input type="text" id="location" class="form-control py-2" required placeholder="उदा. गोरखपुर, यूपी">
                            </div>
                            <div class="col-md-4">
                                <label for="price" class="form-label fw-bold">Price (₹)</label>
                                <input type="number" id="price" class="form-control py-2" required placeholder="उदा. 5000000">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="bedrooms" class="form-label fw-bold">Bedrooms</label>
                                <input type="number" id="bedrooms" class="form-control py-2" placeholder="उदा. 3">
                            </div>
                            <div class="col-md-4">
                                <label for="bathrooms" class="form-label fw-bold">Bathrooms</label>
                                <input type="number" id="bathrooms" class="form-control py-2" placeholder="उदा. 2">
                            </div>
                            <div class="col-md-4">
                                <label for="area" class="form-label fw-bold">Area (sq ft)</label>
                                <input type="number" id="area" class="form-control py-2" required placeholder="उदा. 1200">
                            </div>
                            
                            <div class="col-12">
                                <label for="additionalFeatures" class="form-label fw-bold">Additional Features</label>
                                <textarea id="additionalFeatures" class="form-control py-2" rows="4" placeholder="अन्य सुविधाएँ जैसे पार्किंग, गार्डन, सुरक्षा आदि दर्ज करें..."></textarea>
                            </div>
                            
                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill">
                                    <i class="fas fa-magic me-2"></i> Generate Description
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="loading text-center py-4 mt-4 border-top" id="loading" style="display: none;">
                        <div class="spinner-border text-primary mb-2" role="status"></div>
                        <p class="text-muted">AI विवरण तैयार कर रहा है, कृपया प्रतीक्षा करें...</p>
                    </div>
                    
                    <div id="resultContainer" class="mt-5 pt-4 border-top" style="display: none;">
                        <h4 class="fw-bold mb-3">Generated Description:</h4>
                        <div id="generatedDescription" class="p-4 bg-light rounded-4 border position-relative" style="white-space: pre-wrap; font-size: 1.1rem; line-height: 1.6;">
                        </div>
                        <div class="mt-3 text-end">
                            <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="copyToClipboard()">
                                <i class="fas fa-copy me-1"></i> Copy to Clipboard
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
        Price: ₹${document.getElementById('price').value}
        Area: ${document.getElementById('area').value} sq ft
        Bedrooms: ${document.getElementById('bedrooms').value || 'N/A'}
        Bathrooms: ${document.getElementById('bathrooms').value || 'N/A'}
        Additional Features: ${document.getElementById('additionalFeatures').value || 'None'}
    `.trim();

    loading.style.display = 'block';
    resultContainer.style.display = 'none';
    descriptionDiv.textContent = '';

    try {
        const response = await fetch('<?= BASE_URL ?>api/ai/generate-description', {
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
            alert(`Error: ${data.error || 'Failed to generate description'}`);
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
        alert('Description copied to clipboard!');
    });
}
</script>
