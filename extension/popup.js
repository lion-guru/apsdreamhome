// APS Dream Home AI Assistant - Popup JavaScript

// Provider configurations (matching options.js)
const PROVIDERS = [
  { id: 'ollama', name: 'Ollama (Local)', desc: 'Run models locally on your machine. Unlimited, private, free.', avatar: '🏠', features: ['Free', 'Unlimited', 'Private', 'Offline'], free: true },
  { id: 'groq', name: 'Groq', desc: 'Fastest inference in the world. Llama 3.3 70B, Mixtral, Gemma.', avatar: '⚡', features: ['Free tier: 30 RPM', 'Fastest inference', 'Llama 3.3 70B', 'Mixtral'], free: true },
  { id: 'xai_grok', name: 'xAI Grok', desc: "xAI's Grok model with free tier access.", avatar: '🤖', features: ['Free tier available', 'Grok-2 latest', 'Reasoning'], free: true },
  { id: 'gemini', name: 'Google Gemini', desc: "Google's multimodal AI with generous free tier.", avatar: '💎', features: ['Free: 15 RPM', '1M tokens/day', 'Multimodal', 'Gemini 2.5 Flash'], free: true },
  { id: 'deepseek', name: 'DeepSeek', desc: "DeepSeek's advanced reasoning models with free tier.", avatar: '🐋', features: ['Free tier available', 'DeepSeek-V3', 'Reasoning'], free: true },
  { id: 'together_ai', name: 'Together.ai', desc: 'Together.ai hosts open models with generous free tier.', avatar: '🤝', features: ['Free: 100k tokens/day', 'Llama 3.1', 'Fast inference'], free: true },
  { id: 'cohere', name: 'Cohere', desc: "Cohere's Command R+ with generous free tier.", avatar: '🔮', features: ['Free: 100 calls/min', 'Command R+', 'RAG optimized'], free: true },
  { id: 'openrouter', name: 'OpenRouter', desc: 'Access 100+ models via single API. Free models available.', avatar: '🔀', features: ['Free models available', '100+ models', 'Model routing'], free: true },
  { id: 'huggingface', name: 'Hugging Face', desc: 'Hugging Face Inference API with free tier.', avatar: '🤗', features: ['Free: 30k tokens/day', '1000+ models', 'Open source'], free: true },
];

// Default settings
const DEFAULT_SETTINGS = {
  aiProvider: 'ollama',
  apiKey: '',
  aiModel: '',
  autoSuggest: true,
  quickReply: true,
  apiBaseUrl: 'http://localhost/apsdreamhome'
};

// State
let settings = { ...DEFAULT_SETTINGS };
let selectedProvider = 'ollama';
let chatHistory = [];

// DOM Elements
const tabs = document.querySelectorAll('.tab');
const panels = document.querySelectorAll('.panel');
const chatInput = document.getElementById('chatInput');
const sendChatBtn = document.getElementById('sendChat');
const chatMessages = document.getElementById('chatMessages');
const providerList = document.getElementById('providerList');
const apiKeyInput = document.getElementById('apiKeyInput');
const modelInput = document.getElementById('modelInput');
const autoSuggestToggle = document.getElementById('autoSuggestToggle');
const quickReplyToggle = document.getElementById('quickReplyToggle');

// Initialize
document.addEventListener('DOMContentLoaded', async () => {
  await loadSettings();
  renderProviders();
  updateToggleStates();
  loadAPIKey();
  loadModel();
  setupTabSwitching();
  setupChat();
});

// Tab switching
function setupTabSwitching() {
  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      panels.forEach(p => p.classList.remove('active'));
      
      tab.classList.add('active');
      document.getElementById(`panel-${tab.dataset.tab}`).classList.add('active');
    });
  });
}

// Chat functionality
function setupChat() {
  sendChatBtn.addEventListener('click', sendChatMessage);
  chatInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendChatMessage();
    }
  });
}

async function sendChatMessage() {
  const message = chatInput.value.trim();
  if (!message) return;
  
  // Add user message to chat
  addChatMessage(message, 'user');
  chatInput.value = '';
  
  // Show typing indicator
  const typingId = showTypingIndicator();
  
  try {
    const response = await chrome.runtime.sendMessage({
      action: 'aiRequest',
      endpoint: 'chat',
      data: {
        message,
        provider: settings.aiProvider,
        apiKey: await getAPIKey(),
        model: settings.aiModel,
        history: chatHistory.slice(-10)
      }
    });
    
    removeTypingIndicator(typingId);
    
    if (response.success) {
      addChatMessage(response.text || response.message || 'Response received', 'assistant');
      chatHistory.push({ role: 'user', content: message });
      chatHistory.push({ role: 'assistant', content: response.text || response.message || 'Response received' });
    } else {
      addChatMessage('Error: ' + (response.error || 'Unknown error'), 'error');
    }
  } catch (error) {
    removeTypingIndicator(typingId);
    addChatMessage('Error: ' + error.message, 'error');
  }
}

function addChatMessage(content, role) {
  const messageDiv = document.createElement('div');
  messageDiv.style.cssText = `
    display: flex; gap: 8px; margin-bottom: 12px; animation: fadeIn 0.3s ease;
    ${role === 'user' ? 'flex-direction: row-reverse;' : ''}
  `;
  
  const avatar = document.createElement('div');
  avatar.style.cssText = `
    width: 32px; height: 32px; border-radius: 50%; display: flex; 
    align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;
    ${role === 'user' ? 'background: #0d9488; color: white;' : 'background: #f1f5f9; color: #0d9488;'}
  `;
  avatar.textContent = role === 'user' ? '👤' : '🤖';
  
  const bubble = document.createElement('div');
  bubble.style.cssText = `
    max-width: 75%; padding: 10px 14px; border-radius: 16px; font-size: 13px; line-height: 1.5;
    ${role === 'user' ? 'background: #0d9488; color: white; border-bottom-right-radius: 4px;' : 'background: white; color: #1e293b; border: 1px solid #e2e8f0; border-bottom-left-radius: 4px;'}
  `;
  bubble.textContent = content;
  
  messageDiv.appendChild(avatar);
  messageDiv.appendChild(bubble);
  chatMessages.appendChild(messageDiv);
  chatMessages.scrollTop = chatMessages.scrollHeight;
}

function showTypingIndicator() {
  const id = 'typing-' + Date.now();
  const typingDiv = document.createElement('div');
  typingDiv.id = id;
  typingDiv.style.cssText = 'display: flex; gap: 8px; margin-bottom: 12px;';
  typingDiv.innerHTML = `
    <div style="width: 32px; height: 32px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 14px;">🤖</div>
    <div style="background: #f1f5f9; padding: 10px 14px; border-radius: 16px; border-bottom-left-radius: 4px; display: flex; gap: 4px;">
      <div class="typing-dot"></div>
      <div class="typing-dot"></div>
      <div class="typing-dot"></div>
    </div>
    <style>
      @keyframes typing { 0%, 60%, 100% { transform: translateY(0); } 30% { transform: translateY(-4px); } }
      .typing-dot { width: 6px; height: 6px; background: #0d9488; border-radius: 50%; animation: typing 1.4s infinite ease-in-out; }
      .typing-dot:nth-child(2) { animation-delay: 0.2s; }
      .typing-dot:nth-child(3) { animation-delay: 0.4s; }
    </style>
  `;
  chatMessages.appendChild(typingDiv);
  chatMessages.scrollTop = chatMessages.scrollHeight;
  return id;
}

function removeTypingIndicator(id) {
  const el = document.getElementById(id);
  if (el) el.remove();
}

// Provider rendering
function renderProviders() {
  if (!providerList) return;
  
  providerList.innerHTML = PROVIDERS.map(p => `
    <label class="provider-option ${settings.aiProvider === p.id ? 'selected' : ''}" data-provider="${p.id}">
      <input type="radio" name="aiProvider" value="${p.id}" ${settings.aiProvider === p.id ? 'checked' : ''} style="display:none;">
      <div class="provider-info">
        <div class="provider-avatar" style="background: ${getProviderColor(p.id)}">${p.avatar}</div>
        <div>
          <div class="provider-name">${p.name}</div>
          <div class="provider-desc">${p.desc}</div>
        </div>
      </div>
    </label>
  `).join('');
  
  // Add click handlers
  document.querySelectorAll('.provider-option').forEach(option => {
    option.addEventListener('click', () => selectProvider(option.dataset.provider));
  });
}

function getProviderColor(id) {
  const colors = {
    ollama: '#8b5cf6', groq: '#ff6b35', xai_grok: '#000000', gemini: '#4285f4',
    deepseek: '#2a80b9', together_ai: '#f59e0b', cohere: '#ff6b6b', openrouter: '#8b5cf6', huggingface: '#ffd21e'
  };
  return colors[id] || '#0d9488';
}

function selectProvider(providerId) {
  settings.aiProvider = providerId;
  saveSettings();
  renderProviders();
  
  // Show/hide API key field
  const apiKeyGroup = document.querySelector('.input-group');
  if (apiKeyGroup) {
    apiKeyGroup.style.display = providerId === 'ollama' ? 'none' : 'flex';
  }
  
  loadAPIKey();
  showToast(`Provider changed to ${PROVIDERS.find(p => p.id === providerId)?.name}`, 'info');
}

// Settings management
async function loadSettings() {
  try {
    const result = await chrome.storage.sync.get([
      'authToken', 'userId', 'userRole', 'apiBaseUrl',
      'aiProvider', 'apiKey', 'aiModel', 'autoSuggest', 'quickReply'
    ]);
    
    settings = { ...DEFAULT_SETTINGS, ...result };
    selectedProvider = settings.aiProvider || 'ollama';
  } catch (error) {
    console.error('Failed to load settings:', error);
    showToast('Failed to load settings', 'error');
  }
}

async function saveSettings() {
  try {
    await chrome.storage.sync.set(settings);
  } catch (error) {
    console.error('Failed to save settings:', error);
  }
}

async function getAPIKey() {
  const keyName = 'aiApiKey_' + settings.aiProvider;
  const result = await chrome.storage.sync.get(keyName);
  return result[keyName] || '';
}

async function loadAPIKey() {
  const key = await getAPIKey();
  if (apiKeyInput) apiKeyInput.value = key;
  
  const apiKeyGroup = document.querySelector('.input-group');
  if (apiKeyGroup) {
    apiKeyGroup.style.display = settings.aiProvider === 'ollama' ? 'none' : 'flex';
  }
}

async function saveAPIKey() {
  const apiKey = apiKeyInput.value.trim();
  if (!apiKey) {
    showToast('Please enter an API key', 'error');
    return;
  }
  
  const keyName = 'aiApiKey_' + settings.aiProvider;
  await chrome.storage.sync.set({ [keyName]: apiKey });
  showToast('API key saved successfully', 'success');
}

async function saveModel() {
  const model = modelInput.value.trim();
  settings.aiModel = model;
  await saveSettings();
  showToast(model ? 'Model saved: ' + model : 'Model reset to default', 'success');
}

async function loadModel() {
  const result = await chrome.storage.sync.get('aiModel');
  if (result.aiModel && modelInput) {
    modelInput.value = result.aiModel;
  }
}

function toggleAutoSuggest() {
  settings.autoSuggest = !settings.autoSuggest;
  saveSettings();
  updateToggleStates();
  showToast(settings.autoSuggest ? 'Auto-suggest enabled' : 'Auto-suggest disabled', 'info');
}

function toggleQuickReply() {
  settings.quickReply = !settings.quickReply;
  saveSettings();
  updateToggleStates();
  showToast(settings.quickReply ? 'Quick reply enabled' : 'Quick reply disabled', 'info');
}

function updateToggleStates() {
  if (autoSuggestToggle) {
    autoSuggestToggle.classList.toggle('active', settings.autoSuggest !== false);
  }
  if (quickReplyToggle) {
    quickReplyToggle.classList.toggle('active', settings.quickReply !== false);
  }
}

function clearAuth() {
  if (confirm('Are you sure you want to sign out? This will clear all saved settings.')) {
    chrome.storage.sync.clear();
    showToast('Signed out successfully', 'success');
    setTimeout(() => window.close(), 1000);
  }
}

// Tool actions
function toolAction(action) {
  chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
    if (tabs[0]) {
      chrome.tabs.sendMessage(tabs[0].id, { action: 'getPageContent' }, (response) => {
        if (chrome.runtime.lastError) {
          showToast('Cannot access page content', 'error');
          return;
        }
        
        const text = response?.content || '';
        if (!text) {
          showToast('No content found on page', 'warning');
          return;
        }
        
        executeToolAction(action, text, tabs[0].id);
      });
    }
  });
}

async function executeToolAction(action, text, tabId) {
  showToast(`Processing ${action}...`, 'info');
  
  try {
    const response = await chrome.runtime.sendMessage({
      action: 'aiRequest',
      endpoint: action,
      data: {
        text,
        provider: settings.aiProvider,
        apiKey: await getAPIKey(),
        model: settings.aiModel,
        url: (await getTabInfo(tabId)).url
      }
    });
    
    if (response.success) {
      showResult(response.text || response.summary || response.translation || response.extracted || response.content, action);
      showToast(`${action.charAt(0).toUpperCase() + action.slice(1)} complete!`, 'success');
    } else {
      showToast('Error: ' + (response.error || 'Unknown error'), 'error');
    }
  } catch (error) {
    showToast('Error: ' + error.message, 'error');
  }
}

function getTabInfo(tabId) {
  return new Promise((resolve) => {
    chrome.tabs.get(tabId, (tab) => resolve(tab));
  });
}

function useTemplate(templateId) {
  const templates = {
    new_launch: 'New project launch announcement with property details and CTA',
    price_drop: 'Price drop alert with urgency and savings highlight',
    emi_reminder: 'EMI offer with calculation and benefits',
    site_visit: 'Site visit invitation with schedule and directions',
    festival: 'Festival greeting with property wish and offer',
    followup: 'Follow-up message for interested leads',
    sold_fomo: 'Sold out notification creating FOMO',
    referral: 'Referral program invitation with rewards'
  };
  
  const prompt = templates[templateId];
  if (!prompt) return;
  
  chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
    if (tabs[0]) {
      chrome.tabs.sendMessage(tabs[0].id, { action: 'getPageContent' }, (response) => {
        const context = response?.content || '';
        executeToolAction('generate', `${prompt}. Context: ${context}`, tabs[0].id);
      });
    }
  });
}

function showResult(content, type) {
  // Create modal or update chat
  if (document.getElementById('panel-chat').classList.contains('active')) {
    addChatMessage(content, 'assistant');
    chatHistory.push({ role: 'assistant', content });
  } else {
    // Show in a toast or small modal
    showToast(`${type.charAt(0).toUpperCase() + type.slice(1)} result copied to clipboard`, 'success');
  }
  
  // Copy to clipboard
  navigator.clipboard.writeText(content).catch(() => {});
}

function showToast(message, type = 'info') {
  const container = document.getElementById('toastContainer');
  if (!container) return;
  
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.textContent = message;
  
  const colors = { success: '#10b981', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' };
  toast.style.background = colors[type] || colors.info;
  
  container.appendChild(toast);
  
  setTimeout(() => {
    toast.style.animation = 'slideIn 0.3s ease-out reverse';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// Quick actions
function quickAction(action) {
  switch (action) {
    case 'share':
      chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
        if (tabs[0]) {
          chrome.runtime.sendMessage({ action: 'openShareModal', url: tabs[0].url });
        }
      });
      break;
  }
}