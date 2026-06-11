<?php
$page_title = $page_title ?? 'Property AI Assistant - APS Dream Home';
$page_description = $page_description ?? 'AI Assistant for Property Information';
$property = $property ?? null;
$user_role = $user_role ?? 'customer';
$context = $context ?? '';
$base = $base ?? BASE_URL;
?>

<section class="py-5 bg-gradient-primary text-white position-relative overflow-hidden">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, #0f4c75 0%, #3282b8 50%, #00b4d8 100%);"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3"><i class="fas fa-home me-3"></i>Property AI Assistant</h1>
                <p class="lead mb-0">Ask questions about this property</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/properties" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Properties
                </a>
            </div>
        </div>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <div class="row">
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body aps-cp-card-body">
                        <h5 class="card-title"><i class="fas fa-info-circle text-primary me-2"></i>Property Details</h5>
                        <?php if ($property): ?>
                        <div class="mb-3">
                            <?php if (!empty($property['image'])): ?>
                            <img src="<?= !empty($property['image']) ? htmlspecialchars($property['image']) : (BASE_URL . '/assets/images/property-placeholder.jpg') ?>" alt="<?= htmlspecialchars($property['title'] ?? 'Property') ?>" class="img-fluid rounded mb-3">
                            <?php endif; ?>
                            <h4><?= htmlspecialchars($property['title'] ?? 'Untitled') ?></h4>
                            <p class="text-muted mb-1">
                                <i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($property['location'] ?? 'N/A') ?>
                            </p>
                            <p class="mb-1">
                                <span class="badge bg-primary"><?= htmlspecialchars(ucfirst($property['type'] ?? 'N/A')) ?></span>
                                <span class="badge bg-success">&#8377; <?= htmlspecialchars(number_format(intval($property['price'] ?? 0))) ?></span>
                            </p>
                            <div class="row text-center mt-3 g-2">
                                <?php if (!empty($property['bedrooms'])): ?>
                                <div class="col-4">
                                    <small class="text-muted d-block">Bedrooms</small>
                                    <strong><?= htmlspecialchars($property['bedrooms']) ?></strong>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($property['bathrooms'])): ?>
                                <div class="col-4">
                                    <small class="text-muted d-block">Bathrooms</small>
                                    <strong><?= htmlspecialchars($property['bathrooms']) ?></strong>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($property['area'])): ?>
                                <div class="col-4">
                                    <small class="text-muted d-block">Area</small>
                                    <strong><?= htmlspecialchars($property['area']) ?> sq.ft</strong>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-building fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No property selected. Ask me about our available properties!</p>
                            <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/properties" class="btn btn-outline-primary btn-sm">Browse Properties</a>
                        </div>
                        <?php endif; ?>
                        <hr>
                        <h6>Suggested Questions</h6>
                        <div class="d-flex flex-wrap gap-1 mb-0">
                            <button class="btn btn-sm btn-outline-primary" onclick="sendPropertyQuery('What is the price?')">Price</button>
                            <button class="btn btn-sm btn-outline-success" onclick="sendPropertyQuery('Tell me about the location')">Location</button>
                            <button class="btn btn-sm btn-outline-info" onclick="sendPropertyQuery('What amenities are nearby?')">Amenities</button>
                            <button class="btn btn-sm btn-outline-warning" onclick="sendPropertyQuery('Is this a good investment?')">Investment</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0"><i class="fas fa-comment-dots text-primary me-2"></i>Chat</h5>
                        <span class="badge bg-info">Context: Property Chat</span>
                    </div>
                    <div class="card-body p-0">
                        <div id="property-chat-messages" class="p-4" style="height: 450px; overflow-y: auto; background: #f8f9fa;">
                            <div class="text-center py-5">
                                <div class="mb-3"><span class="display-1">ðŸ </span></div>
                                <h5>Ask about this property!</h5>
                                <p class="text-muted">Get instant answers about pricing, location, amenities, and more.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="input-group">
                            <input type="text" id="property-chat-input" class="form-control" placeholder="Ask about this property..." onkeypress="handlePropertyKeyPress(event)">
                            <button class="btn btn-primary" onclick="sendPropertyMessage()">
                                <i class="fas fa-paper-plane me-1"></i> Ask
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
const propertyContext = <?= json_encode($context) ?>;

function handlePropertyKeyPress(e) {
    if (e.key === 'Enter') sendPropertyMessage();
}

function sendPropertyQuery(query) {
    document.getElementById('property-chat-input').value = query;
    sendPropertyMessage();
}

async function sendPropertyMessage() {
    const input = document.getElementById('property-chat-input');
    const message = input.value.trim();
    if (!message) return;
    input.value = '';
    addPropertyMessage('user', message);
    addPropertyTyping();
    try {
        const res = await fetch('<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/api/ai-chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message, role: 'customer', context: propertyContext })
        });
        const data = await res.json();
        removePropertyTyping();
        addPropertyMessage('assistant', data.reply ?? 'No response.');
    } catch (e) {
        removePropertyTyping();
        addPropertyMessage('assistant', 'Connection error. Please try again.');
    }
}

function addPropertyMessage(type, text) {
    const container = document.getElementById('property-chat-messages');
    container.querySelector('.text-center')?.remove();
    const div = document.createElement('div');
    const isUser = type === 'user';
    div.className = 'd-flex mb-3 ' + (isUser ? 'justify-content-end' : 'justify-content-start');
    div.innerHTML = `
        <div class="${isUser ? 'bg-primary text-white' : 'bg-white border'} rounded-3 p-3 shadow-sm" style="max-width: 80%;">
            <div class="small">${text}</div>
            <small class="${isUser ? 'text-white-50' : 'text-muted'} d-block mt-1">${new Date().toLocaleTimeString()}</small>
        </div>`;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function addPropertyTyping() {
    const container = document.getElementById('property-chat-messages');
    const div = document.createElement('div');
    div.id = 'property-typing';
    div.className = 'd-flex mb-3';
    div.innerHTML = `<div class="bg-white border rounded-3 p-3 shadow-sm"><div class="typing-dots"><span></span><span></span><span></span></div></div>`;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function removePropertyTyping() {
    document.getElementById('property-typing')?.remove();
}
</script>

<style>
.typing-dots { display: flex; gap: 4px; padding: 2px 0; }
.typing-dots span {
    width: 8px; height: 8px; border-radius: 50%; background: #3282b8;
    animation: dotBounce 1.4s infinite ease-in-out;
}
.typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.typing-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes dotBounce {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-8px); }
}
</style>
