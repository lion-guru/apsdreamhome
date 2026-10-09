// APS Dream Home AI Assistant - Background Service Worker
// Handles background tasks, API communication, and extension lifecycle

// Configuration
const API_BASE = (typeof BASE_URL !== 'undefined') ? BASE_URL : 'http://localhost/apsdreamhome';
const EXTENSION_VERSION = '1.0.0';

// Extension state
let authToken = null;
let userSettings = {
  aiProvider: 'ollama',
  apiKey: '',
  model: '',
  autoSuggest: true,
  quickReply: true
};

// Initialize extension
chrome.runtime.onInstalled.addListener(async (details) => {
  console.log('[APS AI] Extension installed/updated:', details.reason);
  
  // Load user settings
  await loadUserSettings();
  
  // Create context menus
  createContextMenus();
  
  // Set up alarms for periodic tasks
  chrome.alarms.create('syncSettings', { periodInMinutes: 30 });
  chrome.alarms.create('checkNotifications', { periodInMinutes: 5 });
});

chrome.alarms.onAlarm.addListener((alarm) => {
  if (alarm.name === 'syncSettings') {
    loadUserSettings();
  } else if (alarm.name === 'checkNotifications') {
    checkNotifications();
  }
});

// Load user settings from storage
async function loadUserSettings() {
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
    
    authToken = result.authToken || null;
    userSettings = {
      aiProvider: result.aiProvider || 'ollama',
      apiKey: result.apiKey || '',
      model: result.aiModel || '',
      autoSuggest: result.autoSuggest !== false,
      quickReply: result.quickReply !== false,
      ...result
    };
    
    console.log('[APS AI] Settings loaded:', { 
      hasToken: !!authToken, 
      provider: result.aiProvider,
      hasApiKey: !!result.apiKey 
    });
  } catch (error) {
    console.error('[APS AI] Failed to load settings:', error);
  }
}

// Save user settings
async function saveUserSettings(settings) {
  try {
    await chrome.storage.sync.set(settings);
    await loadUserSettings();
    return { success: true };
  } catch (error) {
    console.error('Failed to save settings:', error);
    return { success: false, error: error.message };
  }
}

// Context menus
function createContextMenus() {
  chrome.contextMenus.removeAll(() => {
    chrome.contextMenus.create({
      id: 'aps-ai-rewrite',
      title: '✨ Rewrite with APS AI',
      contexts: ['selection'],
      documentUrlPatterns: ['*://*/*']
    });
    
    chrome.contextMenus.create({
      id: 'aps-ai-summarize',
      title: '📝 Summarize with APS AI',
      contexts: ['selection'],
      documentUrlPatterns: ['*://*/*property*', '*://*/*listing*']
    });
    
    chrome.contextMenus.create({
      id: 'aps-ai-translate',
      title: '🌐 Translate with APS AI',
      contexts: ['selection'],
      documentUrlPatterns: ['*://*/*']
    });
    
    chrome.contextMenus.create({
      id: 'aps-quick-share',
      title: '📤 Quick Share via APS',
      contexts: ['page', 'link', 'selection'],
      documentUrlPatterns: ['*://*/*property*', '*://*/*listing*']
    });
    
    chrome.contextMenus.create({
      id: 'aps-save-lead',
      title: '💾 Save as Lead',
      contexts: ['selection', 'page'],
      documentUrlPatterns: ['*://*/*property*', '*://*/*listing*']
    });
  });
}

chrome.contextMenus.onClicked.addListener(async (info, tab) => {
  const selection = info.selectionText || '';
  const pageUrl = info.pageUrl || '';
  
  switch (info.menuItemId) {
    case 'aps-ai-rewrite':
      await rewriteWithAI(selection, info.pageUrl);
      break;
    case 'aps-ai-summarize':
      await summarizeWithAI(selection, info.pageUrl);
      break;
    case 'aps-ai-translate':
      await translateWithAI(selection, info.pageUrl);
      break;
    case 'aps-quick-share':
      await quickShareProperty(info.pageUrl, selection);
      break;
    case 'aps-save-lead':
      await saveLeadFromPage(selection, info.pageUrl);
      break;
  }
});

// API call helper
async function callAPI(endpoint, data, method = 'POST') {
  const url = `${await getAPIBase()}/api/ai/${endpoint}`;
  
  const headers = {
    'Content-Type': 'application/json',
    'X-Extension-Version': chrome.runtime.getManifest().version
  };
  
  if (authToken) {
    headers['Authorization'] = `Bearer ${authToken}`;
  }
  
  try {
    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        ...headers
      },
      body: JSON.stringify({ ...data, extension: true })
    });
    
    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: 'Request failed' }));
      throw new Error(error.message || `HTTP ${response.status}`);
    }
    
    return await response.json();
  } catch (error) {
    console.error('[APS AI] API call failed:', error);
    throw error;
  }
}

function getAPIBase() {
  // Try to get from storage, fallback to default
  return new Promise((resolve) => {
    chrome.storage.sync.get('apiBaseUrl', (result) => {
      resolve(result.apiBaseUrl || 'http://localhost/apsdreamhome');
    });
  });
}

// AI functions
async function rewriteWithAI(text, url) {
  try {
    const result = await callAPI('rewrite', {
      text,
      url,
      tone: 'professional',
      length: 'concise'
    });
    
    if (result.success) {
      await copyToClipboard(result.text);
      showNotification('✅ Rewritten and copied to clipboard!');
    }
  } catch (error) {
    showNotification('Failed to rewrite: ' + error.message, true);
  }
}

async function summarizeWithAI(text, url) {
  try {
    const result = await callAPI('summarize', {
      text,
      url,
      maxLength: 200
    });
    
    if (result.success) {
      await copyToClipboard(result.summary);
      showNotification('✅ Summary copied to clipboard!');
    }
  } catch (error) {
    showNotification('Failed to summarize: ' + error.message, true);
  }
}

async function translateWithAI(text, url) {
  try {
    const result = await callAPI('translate', {
      text,
      targetLang: 'hi', // Default to Hindi
      url
    });
    
    if (result.success) {
      await copyToClipboard(result.translation);
      showNotification('🌐 Translated and copied!');
    }
  } catch (error) {
    showNotification('Translation failed: ' + error.message, true);
  }
}

async function quickShareProperty(url, selection) {
  try {
    const result = await callAPI('quick-share', {
      url,
      selection
    });
    
    if (result.success) {
      showNotification('✅ Share links generated! Check popup for links.');
    }
  } catch (error) {
    showNotification('Share failed: ' + error.message, true);
  }
}

async function saveLeadFromPage(selection, url) {
  try {
    const result = await callAPI('save-lead', {
      selection,
      url,
      source: 'extension'
    });
    
    if (result.success) {
      showNotification('✅ Lead saved to APS CRM!');
    }
  } catch (error) {
    showNotification('Save failed: ' + error.message, true);
  }
}

// Utility functions
function showNotification(message, isError = false) {
  chrome.notifications.create({
    type: 'basic',
    iconUrl: chrome.runtime.getURL('icons/icon48.png'),
    title: isError ? 'APS AI Error' : 'APS AI Assistant',
    message: message,
    priority: 2
  });
}

async function copyToClipboard(text) {
  try {
    await navigator.clipboard.writeText(text);
  } catch (error) {
    // Fallback for older browsers
    const textarea = document.createElement('textarea');
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
  }
}

// Background message handler
chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
  if (message.action === 'getSettings') {
    sendResponse({ success: true, settings: userSettings });
    return true;
  }
  
  if (message.action === 'saveSettings') {
    saveUserSettings(message.settings).then(result => {
      sendResponse({ success: true, ...result });
    });
    return true;
  }
  
  if (message.action === 'aiRequest') {
    callAPI(message.endpoint, message.data)
      .then(result => sendResponse({ success: true, ...result }))
      .catch(error => sendResponse({ success: false, error: error.message }));
    return true;
  }
  
  if (message.action === 'getUserInfo') {
    chrome.storage.sync.get(['userId', 'userRole', 'authToken'], (result) => {
      sendResponse({ success: true, ...result });
    });
    return true;
  }
  
  return true; // Keep channel open for async response
});

// Periodic tasks
async function checkNotifications() {
  if (!authToken) return;
  
  try {
    const response = await fetch(`${await getAPIBase()}/api/notifications/unread`, {
      headers: { 'Authorization': `Bearer ${authToken}` }
    });
    
    if (response.ok) {
      const data = await response.json();
      if (data.count > 0) {
        chrome.action.setBadgeText({ text: data.count.toString() });
        chrome.action.setBadgeBackgroundColor({ color: '#EF4444' });
      } else {
        chrome.action.setBadgeText({ text: '' });
      }
    }
  } catch (error) {
    console.error('Notification check failed:', error);
  }
}

// Handle extension icon click
chrome.action.onClicked.addListener((tab) => {
  // This will open the popup (defined in manifest)
  // Additional logic can go here
});

// Tab update listener for auto-suggestions
chrome.tabs.onUpdated.addListener((tabId, changeInfo, tab) => {
  if (changeInfo.status === 'complete' && userSettings.autoSuggest) {
    // Check if on property/lead page and inject suggestion badge
    if (tab.url && (tab.url.includes('/property/') || tab.url.includes('/listing/') || tab.url.includes('/lead/'))) {
      chrome.scripting.executeScript({
        target: { tabId: tab.id },
        func: () => {
          // Inject floating AI assist button
          if (!document.getElementById('aps-ai-float-btn')) {
            const btn = document.createElement('button');
            btn.id = 'aps-ai-float-btn';
            btn.innerHTML = '🤖 APS AI';
            btn.style.cssText = `
              position: fixed; bottom: 80px; right: 20px; z-index: 9999;
              background: linear-gradient(135deg, #0d9488, #0f766e);
              color: white; border: none; border-radius: 50px;
              padding: 12px 20px; font-weight: 600; cursor: pointer;
              box-shadow: 0 4px 20px rgba(13,148,136,0.4);
              font-family: system-ui, sans-serif; font-size: 14px;
              display: flex; align-items: center; gap: 8px;
            `;
            btn.innerHTML = '🤖 APS AI';
            btn.onclick = () => {
              chrome.runtime.sendMessage({ action: 'openPopup' });
            };
            document.body.appendChild(btn);
          }
        }
      });
      }
    }
  });

// Handle keyboard shortcuts
chrome.commands.onCommand.addListener((command) => {
  if (command === 'toggle-assistant') {
    chrome.action.openPopup();
  } else if (command === 'quick-share') {
    chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
      if (tabs[0]) {
        chrome.tabs.sendMessage(tabs[0].id, { action: 'quickShare' });
      }
    });
  }
});

// Export for testing
if (typeof module !== 'undefined' && module.exports) {
  module.exports = {
    loadUserSettings,
    saveUserSettings,
    callAPI,
    showNotification,
    copyToClipboard
  };
}