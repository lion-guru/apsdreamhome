// WhatsApp Integration Functions
function openWhatsApp() {
    const message = encodeURIComponent("Hi APS Dream Home, I need help with:");
    window.open(`https://wa.me/919876543210?text=${message}`, '_blank');
}

function sendWhatsAppMessage(phone, message, name = '') {
    const encodedMessage = encodeURIComponent(`Hi APS Dream Home, ${name ? name + ' - ' : ''}${message}`);
    window.open(`https://wa.me/91${phone}?text=${encodedMessage}`, '_blank');
}

function shareOnWhatsApp(platform = 'web') {
    const message = encodeURIComponent(`Check out APS Dream Home - Complete Real Estate & Colonizer Management System

🏠 Property Management
🏗️ Plot Management
👥 Farmer Relations
📞 APS CRM System
💰 MLM Commission System

Visit: ${window.location.origin}
Contact: 1800-XXX-XXXX`);

    if (platform === 'mobile') {
        window.location.href = `whatsapp://send?text=${message}`;
    } else {
        window.open(`https://wa.me/?text=${message}`, '_blank');
    }
}

// WhatsApp Contact Form Handler
function handleWhatsAppContact(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const name = formData.get('name') || form.querySelector('input[placeholder*="Name"]').value;
    const email = formData.get('email') || form.querySelector('input[placeholder*="Email"]').value;
    const phone = formData.get('phone') || form.querySelector('input[placeholder*="WhatsApp"]').value;
    const interest = formData.get('interest') || form.querySelector('select').value;
    const message = formData.get('message') || form.querySelector('textarea').value;

    if (!name || !phone) {
        showAlert('Error', 'Please fill in your name and WhatsApp number.');
        return;
    }

    const whatsappMessage = `Hi APS Dream Home,

Name: ${name}
Email: ${email}
Phone: ${phone}
Interest: ${interest}
Message: ${message}

Please contact me regarding this inquiry.`;

    sendWhatsAppMessage(phone, whatsappMessage, name);

    // Show success message
    showSuccessMessage('WhatsApp message sent successfully! Our team will contact you soon.');

    // Reset form
    form.reset();

    return false;
}

// WhatsApp Chat Widget
function initializeWhatsAppWidget() {
    // Check if widget already exists
    if (document.querySelector('.whatsapp-widget')) {
        return;
    }

    const widget = document.createElement('div');
    widget.className = 'whatsapp-widget';
    widget.innerHTML = `
        <div class="whatsapp-widget-button" onclick="toggleWhatsAppWidget()">
            <i class="fab fa-whatsapp"></i>
        </div>
        <div class="whatsapp-widget-popup">
            <div class="whatsapp-widget-header">
                <h6>Chat with APS Dream Home</h6>
                <button onclick="closeWhatsAppWidget()" class="whatsapp-widget-close">&times;</button>
            </div>
            <div class="whatsapp-widget-body">
                <p>Hi! How can we help you today?</p>
                <div class="whatsapp-widget-options">
                    <button onclick="selectWhatsAppOption('property')" class="whatsapp-option">
                        🏠 Property Inquiry
                    </button>
                    <button onclick="selectWhatsAppOption('plot')" class="whatsapp-option">
                        🏗️ Plot Information
                    </button>
                    <button onclick="selectWhatsAppOption('support')" class="whatsapp-option">
                        🎧 Customer Support
                    </button>
                    <button onclick="selectWhatsAppOption('booking')" class="whatsapp-option">
                        📋 Booking Status
                    </button>
                </div>
                <div class="whatsapp-widget-input" style="display: none;">
                    <input type="text" placeholder="Type your message..." id="whatsappInput">
                    <button onclick="sendWhatsAppFromWidget()" class="btn btn-success btn-sm">
                        <i class="fab fa-whatsapp"></i> Send
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(widget);

    // Add widget styles
    const style = document.createElement('style');
    style.textContent = `
        .whatsapp-widget {
            position: fixed;
            bottom: 100px;
            right: 30px;
            z-index: 1001;
            font-family: inherit;
        }

        .whatsapp-widget-button {
            width: 60px;
            height: 60px;
            background: #25d366;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 30px;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(37, 211, 102, 0.3);
            transition: all 0.3s ease;
            animation: whatsapp-bounce 2s infinite;
        }

        .whatsapp-widget-button:hover {
            background: #128c7e;
            transform: scale(1.1);
        }

        .whatsapp-widget-popup {
            position: absolute;
            bottom: 70px;
            right: 0;
            width: 300px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.3s ease;
        }

        .whatsapp-widget-popup.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .whatsapp-widget-header {
            background: #25d366;
            color: white;
            padding: 15px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .whatsapp-widget-close {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }

        .whatsapp-widget-body {
            padding: 15px;
        }

        .whatsapp-widget-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 15px;
        }

        .whatsapp-option {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .whatsapp-option:hover {
            background: #25d366;
            color: white;
            border-color: #25d366;
        }

        .whatsapp-widget-input {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }

        .whatsapp-widget-input input {
            flex: 1;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 8px;
            font-size: 14px;
        }

        @keyframes whatsapp-bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
    `;
    document.head.appendChild(style);
}

function toggleWhatsAppWidget() {
    const popup = document.querySelector('.whatsapp-widget-popup');
    popup.classList.toggle('show');
}

function closeWhatsAppWidget() {
    document.querySelector('.whatsapp-widget-popup').classList.remove('show');
}

function selectWhatsAppOption(option) {
    const inputSection = document.querySelector('.whatsapp-widget-input');
    const optionsSection = document.querySelector('.whatsapp-widget-options');
    const input = document.getElementById('whatsappInput');

    optionsSection.style.display = 'none';
    inputSection.style.display = 'flex';
    input.focus();

    let placeholderMessage = '';
    switch(option) {
        case 'property':
            placeholderMessage = 'Tell us about the property you\'re looking for...';
            break;
        case 'plot':
            placeholderMessage = 'Which plot are you interested in?';
            break;
        case 'support':
            placeholderMessage = 'Describe your issue...';
            break;
        case 'booking':
            placeholderMessage = 'Enter your booking number...';
            break;
    }

    input.placeholder = placeholderMessage;
    input.dataset.option = option;
}

function sendWhatsAppFromWidget() {
    const input = document.getElementById('whatsappInput');
    const option = input.dataset.option;

    if (!input.value.trim()) {
        input.focus();
        return;
    }

    let message = '';
    switch(option) {
        case 'property':
            message = `Property Inquiry: ${input.value}`;
            break;
        case 'plot':
            message = `Plot Information Request: ${input.value}`;
            break;
        case 'support':
            message = `Support Request: ${input.value}`;
            break;
        case 'booking':
            message = `Booking Status Check: ${input.value}`;
            break;
    }

    sendWhatsAppMessage('9876543210', message, 'Website Visitor');
    closeWhatsAppWidget();
    input.value = '';
    showSuccessMessage('Message sent to WhatsApp!');
}

// Initialize WhatsApp widget when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize WhatsApp widget after 3 seconds
    setTimeout(initializeWhatsAppWidget, 3000);

    // Add WhatsApp event listeners to forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', handleWhatsAppContact);
    });

    // Add WhatsApp share buttons functionality
    document.querySelectorAll('[onclick*="shareOnWhatsApp"]').forEach(button => {
        button.addEventListener('click', function() {
            shareOnWhatsApp();
        });
    });
});
