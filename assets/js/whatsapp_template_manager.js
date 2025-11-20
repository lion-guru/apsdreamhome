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
                document.getElementById('messagePreview').innerHTML = data.preview.replace(/\\{\\{(\\w+)\\}\\}/g, (match, variable) => '<span class=\"variable-highlight\">{{' + variable + '}}</span>');
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
    const matches = text.match(/\\{\\{(\\w+)\\}\\}/g);
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