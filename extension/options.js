// APS Dream Home AI Assistant - Options Page JavaScript

// Provider configurations
const PROVIDERS = [
  {
    id: 'ollama',
    name: 'Ollama (Local)',
    desc: 'Run models locally on your machine. Unlimited, private, free.',
    avatar: '🏠',
    features: ['Free', 'Unlimited', 'Private', 'Offline'],
    free: true
  },
  {
    id: 'groq',
    name: 'Groq',
    desc: 'Fastest inference in the world. Llama 3.3 70B, Mixtral, Gemma.',
    avatar: '⚡',
    features: ['Free tier: 30 RPM', 'Fastest inference', 'Llama 3.3 70B', 'Mixtral'],
    free: true
  },
  {
    id: 'xai_grok',
    name: 'xAI Grok',
    desc: 'xAI\'s Grok model with free tier access.',
    avatar: '🤖',
    features: ['Free tier available', 'Grok-2 latest', 'Reasoning'],
    free: true
  },
  {
    id: 'gemini',
    name: 'Google Gemini',
    desc: 'Google\'s multimodal AI with generous free tier.',
    avatar: '💎',
    features: ['Free: 15 RPM', '1M tokens/day', 'Multimodal', 'Gemini 2.5 Flash'],
    free: true
  },
  {
    id: 'deepseek',
    name: 'DeepSeek',
    desc: 'DeepSeek\'s advanced reasoning models with free tier.',
    avatar: '🐋',
    features: ['Free tier available', 'DeepSeek-V3', 'Reasoning'],
    free: true
  },
  {
    id: 'together_ai',
    name: 'Together.ai',
    desc: 'Together.ai hosts open models with generous free tier.',
    avatar: '🤝',
    features: ['Free: 100k tokens/day', 'Llama 3.1', 'Fast inference'],
    free: true
  },
  {
    id: 'together',
    name: 'Together.ai (Legacy)',
    desc: 'Together.ai legacy endpoint.',
    avatar: '🔗',
    features: ['Free: 100k tokens/day', 'Llama 3.1'],
    free: true
  },
  {
    id: 'cohere',
    name: 'Cohere',
    desc: 'Cohere\'s Command R+ with generous free tier.',
    avatar: '🔮',
    features: ['Free: 100 calls/min', 'Command R+', 'RAG optimized'],
    free: true
  },
  {
    id: 'openrouter',
    name: 'OpenRouter',
    desc: 'Access 100+ models via single API. Free models available.',
    avatar: '🔀',
    features: ['Free models available', '100+ models', 'Model routing'],
    free: true
  },
  {
    id: 'huggingface',
    name: 'Hugging Face',
    desc: 'Hugging Face Inference API with free tier.',
    avatar: '🤗',
    features: ['Free: 30k tokens/day', '1000+ models', 'Open source'],
    free: true
  },
  {
    id: 'together',
    name: 'Together.ai (New)',
    desc: 'Together.ai new platform with generous free tier.',
    avatar: '☁️',
    features: ['Free: 100k tokens/day', 'Llama 3.1', 'Fast'],
    free: true
  }
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

// DOM Elements
const providerGrid = document.getElementById('providerGrid');
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
});

async function loadSettings() {
  try {
    const result = await chrome.storage.sync.get([
      'authToken',
      'userId',
      'userRole',
      'apiBaseUrl',
      'aiProvider',
      'apiKey',
      'aiModel',
      'autoSuggest',
      'quickReply'
    ]);
    
    settings = { ...DEFAULT_SETTINGS, ...result };
    selectedProvider = settings.aiProvider || 'ollama';
    updateProviderSelection();
    updateToggleStates();
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

function renderProviders() {
  const container = document.getElementById('providerGrid');
  if (!container) return;
  
  container.innerHTML = PROVIDERS.map(p => `
    <label class="provider-card ${settings.aiProvider === p.id ? 'selected' : ''}" data-provider="${p.id}">
      <input type="radio" name="aiProvider" value="${p.id}" ${settings.aiProvider === p.id ? 'checked' : ''} style="display:none;">
      <div class="provider-header">
        <div class="provider-avatar" style="background: ${getProviderColor(p.id)}">${p.avatar}</div>
        <div class="provider-info">
          <div class="provider-name">${p.name}</div>
          <div class="provider-desc">${p.desc}</div>
        </div>
      </div>
      <div class="provider-features">
        ${p.features.map(f => `<span class="feature-tag ${f.free ? 'free' : ''}">${f}</span>`).join('')}
      </div>
    `).join('');
  
  // Add click handlers
  document.querySelectorAll('.provider-card').forEach(card => {
    card.addEventListener('click', () => selectProvider(card.dataset.provider));
  });
}

function selectProvider(providerId) {
  settings.aiProvider = providerId;
  saveSettings();
  updateProviderSelection();
  
  // Show/hide API key field
  const apiKeyGroup = document.querySelector('.input-group');
  if (apiKeyGroup) {
    apiKeyGroup.style.display = providerId === 'ollama' ? 'none' : 'flex';
  }
  
  showToast(`Provider changed to ${PROVIDERS.find(p => p.id === providerId)?.name}`, 'info');
}

function updateProviderSelection() {
  document.querySelectorAll('.provider-card').forEach(card => {
    const providerId = card.dataset.provider;
    if (settings.aiProvider === providerId) {
      card.classList.add('selected');
      card.querySelector('input').checked = true;
    } else {
      card.classList.remove('selected');
      card.querySelector('input').checked = false;
    }
  });
}

function updateToggleStates() {
  const autoSuggestToggle = document.getElementById('autoSuggestToggle');
  const quickReplyToggle = document.getElementById('quickReplyToggle');
  
  if (autoSuggestToggle) {
    autoSuggestToggle.classList.toggle('active', settings.autoSuggest !== false);
  }
  if (quickReplyToggle) {
    quickReplyToggle.classList.toggle('active', settings.quickReply !== false);
  }
}

async function loadAPIKey() {
  const keyName = 'aiApiKey_' + settings.aiProvider;
  const result = await chrome.storage.sync.get(keyName);
  if (result[keyName]) {
    document.getElementById('apiKeyInput').value = result[keyName];
  }
}

async function saveAPIKey() {
  const apiKey = document.getElementById('apiKeyInput').value.trim();
  if (!apiKey) {
    showToast('Please enter an API key', 'error');
    return;
  }
  
  const keyName = 'aiApiKey_' + settings.aiProvider;
  await chrome.storage.sync.set({ [keyName]: apiKey });
  showToast('API key saved successfully', 'success');
}

async function saveModel() {
  const model = document.getElementById('modelInput').value.trim();
  settings.aiModel = model;
  await saveSettings();
  showToast(model ? 'Model saved: ' + model : 'Model reset to default', 'success');
}

async function loadModel() {
  const result = await chrome.storage.sync.get('aiModel');
  if (result.aiModel) {
    document.getElementById('modelInput').value = result.aiModel;
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
  const autoSuggestToggle = document.getElementById('autoSuggestToggle');
  const quickReplyToggle = document.getElementById('quickReplyToggle');
  
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

function showToast(message, type = 'info') {
  const container = document.getElementById('toastContainer') || (() => {
    const c = document.createElement('div');
    c.id = 'toastContainer';
    c.style.cssText = 'position: fixed; bottom: 24px; right: 24px; z-index: 10000;';
    document.body.appendChild(c);
    return c;
  })();
  
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.textContent = message;
  toast.style.cssText = `
    position: fixed; bottom: 24px; right: 24px; z-index: 10000;
    padding: 12px 20px; border-radius: 8px; color: white; font-weight: 500; font-size: 13px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: slideIn 0.3s ease;
  `;
  
  const colors = { success: '#10b981', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' };
  toast.style.background = colors[type] || colors.info;
  
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'slideIn 0.3s ease-out reverse';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}