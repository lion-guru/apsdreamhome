// Initialize TinyMCE editors
function initTinyMCE() {
    const editorConfig = {
        height: 200,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | ' +
            'bold italic forecolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | help',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; font-size: 14px }',
        setup: function(editor) {
            editor.on('Change', function() {
                updatePreview();
            });
        }
    };

    // Initialize TinyMCE for content areas
    ['content', 'sidebar', 'header', 'footer', 'navigation'].forEach(id => {
        tinymce.init({
            ...editorConfig,
            selector: `#${id}`
        });
    });
}

// Function to update preview content
function updatePreview() {
    const frame = document.getElementById('preview-frame');
    const doc = frame.contentDocument || frame.contentWindow.document;
    const previewData = {
        title: document.getElementById('title').value,
        content: tinymce.get('content').getContent(),
        sidebar: tinymce.get('sidebar').getContent(),
        header: tinymce.get('header').getContent(),
        footer: tinymce.get('footer').getContent(),
        navigation: tinymce.get('navigation').getContent()
    };

    // Update the preview content
    const compiledContent = window.compiledTemplate;
    let updatedContent = compiledContent;
    
    // Replace placeholders with updated content
    Object.keys(previewData).forEach(key => {
        const placeholder = new RegExp(`\\{\\{${key}\\}\\}`, 'g');
        updatedContent = updatedContent.replace(placeholder, previewData[key]);
    });

    // Update the iframe content
    doc.open();
    doc.write(`<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width, initial-scale=1.0"><link href="assets/css/bootstrap.min.css" rel="stylesheet"></head><body>${updatedContent}</body></html>`);
    doc.close();
}

// Add event listener for title input
document.getElementById('title').addEventListener('input', updatePreview);

// Initialize TinyMCE when the page loads
window.addEventListener('load', function() {
    initTinyMCE();
});