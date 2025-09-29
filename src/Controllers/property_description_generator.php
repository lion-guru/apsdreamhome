<?php
require_once('config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get property types from database
$property_types_query = "SELECT * FROM property_types ORDER BY type_name";
$property_types = $con->query($property_types_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Description Generator</title>
    <link href="css/style.css" rel="stylesheet">
    <style>
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }
        .input-group {
            margin-bottom: 20px;
        }
        .input-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        .input-field {
            flex: 1;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        textarea {
            min-height: 100px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        #generatedDescription {
            white-space: pre-wrap;
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            min-height: 100px;
            margin-top: 20px;
        }
        .loading {
            display: none;
            margin: 10px 0;
        }
        .btn-primary {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Property Description Generator</h1>
        <form id="propertyForm">
            <div class="input-row">
                <div class="input-field">
                    <label for="propertyType">Property Type</label>
                    <select id="propertyType" required>
                        <option value="">चुनें</option>
                        <?php while($type = $property_types->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($type['id']); ?>">
                                <?php echo htmlspecialchars($type['type_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="input-field">
                    <label for="location">Location</label>
                    <input type="text" id="location" required placeholder="e.g. Gorakhpur, UP">
                </div>
                <div class="input-field">
                    <label for="price">Price (₹)</label>
                    <input type="number" id="price" required placeholder="e.g. 5000000">
                </div>
            </div>

            <div class="input-row">
                <div class="input-field">
                    <label for="bedrooms">Bedrooms</label>
                    <input type="number" id="bedrooms" placeholder="e.g. 3">
                </div>
                <div class="input-field">
                    <label for="bathrooms">Bathrooms</label>
                    <input type="number" id="bathrooms" placeholder="e.g. 2">
                </div>
                <div class="input-field">
                    <label for="area">Area (sq ft)</label>
                    <input type="number" id="area" required placeholder="e.g. 1200">
                </div>
            </div>

            <div class="input-group">
                <label for="additionalFeatures">Additional Features</label>
                <textarea id="additionalFeatures" placeholder="Enter additional features like parking, garden, security etc."></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Generate Description</button>
        </form>

        <div class="loading">Generating description...</div>
        <div id="generatedDescription"></div>
    </div>

    <script>
    document.getElementById('propertyForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const loading = document.querySelector('.loading');
        const descriptionDiv = document.getElementById('generatedDescription');

        // Collect form data
        const propertyData = {
            type: document.getElementById('propertyType').value,
            location: document.getElementById('location').value,
            price: document.getElementById('price').value,
            bedrooms: document.getElementById('bedrooms').value,
            bathrooms: document.getElementById('bathrooms').value,
            area: document.getElementById('area').value,
            features: document.getElementById('additionalFeatures').value,
            language: 'hi' // Default to Hindi
        };

        // Save to database
        try {
            await fetch('/admin/api/save_property_description.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(propertyData)
            });
        } catch (error) {
            console.error('Error saving description:', error);
        }

        // Create prompt for Gemini
        const prompt = `Generate a professional and engaging property description in Hindi for the following property:\n\n` +
            `Property Type: ${propertyData.type}\n` +
            `Location: ${propertyData.location}\n` +
            `Price: ₹${propertyData.price}\n` +
            `Area: ${propertyData.area} sq ft\n` +
            (propertyData.bedrooms ? `Bedrooms: ${propertyData.bedrooms}\n` : '') +
            (propertyData.bathrooms ? `Bathrooms: ${propertyData.bathrooms}\n` : '') +
            (propertyData.features ? `Additional Features: ${propertyData.features}\n` : '') +
            `\nPlease write a compelling description that highlights the property's best features and its location advantages. Include the price and area details naturally in the text.`;

        loading.style.display = 'block';
        descriptionDiv.textContent = '';

        try {
            const response = await fetch('/api/gemini.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ prompt })
            });

            const data = await response.json();
            if (data.error) {
                descriptionDiv.textContent = `Error: ${data.message}`;
            } else {
                descriptionDiv.textContent = data.candidates[0].content.parts[0].text;
            }
        } catch (error) {
            descriptionDiv.textContent = `Error: ${error.message}`;
        } finally {
            loading.style.display = 'none';
        }
    });
    </script>
</body>
</html>