<?php
/**
 * APS Dream Home - WhatsApp Template Management Interface
 * Web interface for managing WhatsApp message templates
 */

require_once 'config.php';
require_once 'whatsapp_templates.php';

// Initialize templates if not exists
$templates_created = initializeWhatsAppTemplates();

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>APS Dream Home - WhatsApp Template Manager</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        .template-card { margin: 15px 0; border-radius: 10px; }
        .template-preview { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 0.9em; }
        .variable-highlight { background: #fff3cd; padding: 2px 4px; border-radius: 3px; }
        .category-badge { padding: 5px 10px; border-radius: 15px; font-size: 0.8em; }
        .customer-service { background: #d4edda; color: #155724; }
        .property { background: #d1ecf1; color: #0c5460; }
        .booking { background: #fcefdc; color: #8a6d3b; }
        .commission { background: #f8d7da; color: #721c24; }
        .payment { background: #e2e3e5; color: #383d41; }
        .appointment { background: #d4edda; color: #155724; }
        .system { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class='container-fluid py-4'>
        <!-- Header -->
        <div class='row mb-4'>
            <div class='col-12'>
                <div class='card template-card' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;'>
                    <div class='card-body text-center'>
                        <h1><i class='fas fa-edit me-3'></i>WhatsApp Template Manager</h1>
                        <p class='mb-0'>Create and Manage WhatsApp Message Templates</p>
                        <small>Templates Created: {$templates_created} | Total Templates: " . count(getWhatsAppTemplates()) . "</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <ul class='nav nav-tabs mb-4' id='templateTabs' role='tablist'>
            <li class='nav-item' role='presentation'>
                <button class='nav-link active' id='templates-tab' data-bs-toggle='tab' data-bs-target='#templates' type='button' role='tab'>
                    <i class='fas fa-list me-2'></i>All Templates
                </button>
            </li>
            <li class='nav-item' role='presentation'>
                <button class='nav-link' id='create-tab' data-bs-toggle='tab' data-bs-target='#create' type='button' role='tab'>
                    <i class='fas fa-plus me-2'></i>Create Template
                </button>
            </li>
            <li class='nav-item' role='presentation'>
                <button class='nav-link' id='test-tab' data-bs-toggle='tab' data-bs-target='#test' type='button' role='tab'>
                    <i class='fas fa-vial me-2'></i>Test Templates
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class='tab-content' id='templateTabContent'>";

// ALL TEMPLATES TAB
echo "<div class='tab-pane fade show active' id='templates' role='tabpanel'>";
$templates = getWhatsAppTemplates();

if (empty($templates)) {
    echo "<div class='alert alert-info'><i class='fas fa-info-circle me-2'></i>No templates found. Create your first template!</div>";
} else {
    echo "<div class='row'>";
    foreach ($templates as $name => $template) {
        $category_class = strtolower(str_replace('_', '-', $template['category']));
        echo "<div class='col-md-6 col-lg-4 mb-4'>";
        echo "<div class='card template-card h-100'>";
        echo "<div class='card-header d-flex justify-content-between align-items-center'>";
        echo "<h5 class='mb-0'><i class='fas fa-file-alt me-2'></i>{$name}</h5>";
        echo "<span class='category-badge {$category_class}'>{$template['category']}</span>";
        echo "</div>";
        echo "<div class='card-body'>";
        echo "<p><strong>Language:</strong> {$template['language']}</p>";

        if (!empty($template['header'])) {
            echo "<p><strong>Header:</strong><br><span class='template-preview'>" . htmlspecialchars(substr($template['header'], 0, 100)) . "...</span></p>";
        }

        echo "<p><strong>Body:</strong><br><span class='template-preview'>" . htmlspecialchars(substr($template['body'], 0, 150)) . "...</span></p>";

        if (!empty($template['footer'])) {
            echo "<p><strong>Footer:</strong><br><span class='template-preview'>" . htmlspecialchars(substr($template['footer'], 0, 100)) . "...</span></p>";
        }

        if (!empty($template['variables'])) {
            echo "<p><strong>Variables:</strong> ";
            foreach ($template['variables'] as $variable) {
                echo "<span class='variable-highlight'>{{$variable}}</span> ";
            }
            echo "</p>";
        }

        echo "<small class='text-muted'>Created: {$template['created_at']}</small>";
        echo "</div>";
        echo "<div class='card-footer text-center'>";
        echo "<button class='btn btn-sm btn-primary me-2' onclick='editTemplate(\"{$name}\")'><i class='fas fa-edit me-1'></i>Edit</button>";
        echo "<button class='btn btn-sm btn-success me-2' onclick='testTemplate(\"{$name}\")'><i class='fas fa-vial me-1'></i>Test</button>";
        echo "<button class='btn btn-sm btn-danger' onclick='deleteTemplate(\"{$name}\")'><i class='fas fa-trash me-1'></i>Delete</button>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }
    echo "</div>";
}

echo "</div>";

// CREATE TEMPLATE TAB
echo "<div class='tab-pane fade' id='create' role='tabpanel'>";
echo "<div class='card template-card'>";
echo "<div class='card-header'>";
echo "<h4><i class='fas fa-plus me-2'></i>Create New Template</h4>";
echo "</div>";
echo "<div class='card-body'>";
echo "<form id='createTemplateForm'>";
echo "<div class='row'>";
echo "<div class='col-md-6'>";
echo "<div class='mb-3'>";
echo "<label for='templateName' class='form-label'>Template Name *</label>";
echo "<input type='text' class='form-control' id='templateName' required placeholder='welcome_message'>";
echo "<small class='form-text text-muted'>Use lowercase with underscores</small>";
echo "</div>";
echo "</div>";
echo "<div class='col-md-6'>";
echo "<div class='mb-3'>";
echo "<label for='templateCategory' class='form-label'>Category *</label>";
echo "<select class='form-control' id='templateCategory' required>";
echo "<option value='customer_service'>Customer Service</option>";
echo "<option value='property'>Property</option>";
echo "<option value='booking'>Booking</option>";
echo "<option value='commission'>Commission</option>";
echo "<option value='payment'>Payment</option>";
echo "<option value='appointment'>Appointment</option>";
echo "<option value='system'>System</option>";
echo "<option value='general'>General</option>";
echo "</select>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "<div class='mb-3'>";
echo "<label for='templateHeader' class='form-label'>Header (Optional)</label>";
echo "<input type='text' class='form-control' id='templateHeader' placeholder='🎉 Welcome to APS Dream Home!'>";
echo "</div>";

echo "<div class='mb-3'>";
echo "<label for='templateBody' class='form-label'>Body *</label>";
echo "<textarea class='form-control' id='templateBody' rows='6' required placeholder='Hi {{customer_name}}!&#10;&#10;Thank you for choosing APS Dream Home...'></textarea>";
echo "<small class='form-text text-muted'>Use {{variable_name}} for dynamic content</small>";
echo "</div>";

echo "<div class='mb-3'>";
echo "<label for='templateFooter' class='form-label'>Footer (Optional)</label>";
echo "<input type='text' class='form-control' id='templateFooter' placeholder='📞 Contact us: {{phone_number}}'>";
echo "</div>";

echo "<div class='mb-3'>";
echo "<label class='form-label'>Variables (from your template):</label>";
echo "<div id='variablesList'></div>";
echo "<small class='form-text text-muted'>Variables will be auto-detected from your template content</small>";
echo "</div>";

echo "<div class='text-center'>";
echo "<button type='submit' class='btn btn-primary'><i class='fas fa-save me-2'></i>Create Template</button>";
echo "</div>";
echo "</form>";
echo "</div>";
echo "</div>";
echo "</div>";

// TEST TEMPLATES TAB
echo "<div class='tab-pane fade' id='test' role='tabpanel'>";
echo "<div class='card template-card'>";
echo "<div class='card-header'>";
echo "<h4><i class='fas fa-vial me-2'></i>Test WhatsApp Templates</h4>";
echo "</div>";
echo "<div class='card-body'>";
echo "<form id='testTemplateForm'>";
echo "<div class='row'>";
echo "<div class='col-md-6'>";
echo "<div class='mb-3'>";
echo "<label for='testTemplateName' class='form-label'>Select Template *</label>";
echo "<select class='form-control' id='testTemplateName' required>";
echo "<option value=''>Choose a template...</option>";

foreach ($templates as $name => $template) {
    echo "<option value='{$name}'>{$name} ({$template['category']})</option>";
}

echo "</select>";
echo "</div>";
echo "</div>";
echo "<div class='col-md-6'>";
echo "<div class='mb-3'>";
echo "<label for='testPhone' class='form-label'>Test Phone Number *</label>";
echo "<input type='text' class='form-control' id='testPhone' required placeholder='9876543210' value='9876543210'>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "<div id='templateVariables'>";
echo "</div>";

echo "<div class='mb-3'>";
echo "<label class='form-label'>Message Preview:</label>";
echo "<div class='template-preview' id='messagePreview'>Select a template to see preview...</div>";
echo "</div>";

echo "<div class='text-center'>";
echo "<button type='submit' class='btn btn-success'><i class='fas fa-paper-plane me-2'></i>Send Test Message</button>";
echo "</div>";
echo "</form>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "</div></div>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
<script>
document.getElementById('createTemplateForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = {
        name: document.getElementById('templateName').value,
        category: document.getElementById('templateCategory').value,
        header: document.getElementById('templateHeader').value,
        body: document.getElementById('templateBody').value,
        footer: document.getElementById('templateFooter').value,
        variables: extractVariables(document.getElementById('templateBody').value)
    };

    fetch('api/manage_whatsapp_templates.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Template created successfully!');
            location.reload();
        } else {
            alert('❌ Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
    });
});

document.getElementById('testTemplateName').addEventListener('change', function() {
    const templateName = this.value;
    if (templateName) {
        // Load template variables and preview
        fetch('api/get_template_preview.php?name=' + encodeURIComponent(templateName))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('messagePreview').innerHTML = data.preview.replace(/\{\{(\w+)\}\}/g, (match, variable) => '<span class=\"variable-highlight\">{{' + variable + '}}</span>');
                generateVariableInputs(data.variables);
            }
        });
    }
});

document.getElementById('testTemplateForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = {
        template_name: document.getElementById('testTemplateName').value,
        phone: document.getElementById('testPhone').value,
        variables: getVariableValues()
    };

    fetch('api/test_whatsapp_template.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Test message sent successfully!');
        } else {
            alert('❌ Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
    });
});

function extractVariables(text) {
    const matches = text.match(/\{\{(\w+)\}\}/g);
    if (!matches) return [];
    return matches.map(match => match.replace(/[{}]/g, ''));
}

function generateVariableInputs(variables) {
    const container = document.getElementById('templateVariables');
    container.innerHTML = '';

    variables.forEach(variable => {
        const div = document.createElement('div');
        div.className = 'mb-3';
        div.innerHTML = `
            <label for='var_" + variable + "' class='form-label'>" + variable + " *</label>
            <input type='text' class='form-control' id='var_" + variable + "' required placeholder='Enter " + variable + " value...'>
        `;
        container.appendChild(div);
    });
}

function getVariableValues() {
    const inputs = document.querySelectorAll('#templateVariables input');
    const values = {};
    inputs.forEach(input => {
        const variable = input.id.replace('var_', '');
        values[variable] = input.value;
    });
    return values;
}

function editTemplate(name) {
    // Implement edit functionality
    alert('Edit functionality coming soon! Template: ' + name);
}

function testTemplate(name) {
    document.getElementById('testTemplateName').value = name;
    document.getElementById('testTemplateName').dispatchEvent(new Event('change'));
    document.querySelector('[href=\"#test\"]').click();
}

function deleteTemplate(name) {
    if (confirm('Are you sure you want to delete template: ' + name + '?')) {
        fetch('api/manage_whatsapp_templates.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ name: name })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Template deleted successfully!');
                location.reload();
            } else {
                alert('❌ Error: ' + data.error);
            }
        });
    }
}

// Auto-detect variables when typing
document.getElementById('templateBody').addEventListener('input', function() {
    const variables = extractVariables(this.value);
    document.getElementById('variablesList').innerHTML = variables.map(v =>
        '<span class=\"variable-highlight\">{{' + v + '}}</span>'
    ).join(' ');
});
</script>
</body>
</html>";
