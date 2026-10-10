// APS Dream Home AI Assistant - Content Script
// Injects AI assist features into property/lead pages

// Configuration
const APS_API_BASE = (typeof BASE_URL !== 'undefined') ? BASE_URL : 'http://localhost/apsdreamhome';

// Global state
let apsAIEnabled = true;
let apsAIButton = null;

// Initialize
(function() {
  'use strict';
  
  // Wait for DOM to be ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
  
  function init() {
    console.log('[APS AI] Content script loaded');
    
    // Check if we're on a relevant page
    if (!isRelevantPage()) {
      console.log('[APS AI] Not a relevant page, skipping');
      return;
    }
    
    // Inject floating AI button
    injectFloatButton();
    
    // Add event listeners for dynamic content
    observeDOMChanges();
    
    // Listen for messages from background/extension
    chrome.runtime.onMessage.addListener(handleMessage);
    
    console.log('[APS AI] Content script initialized');
  }
  
  function isRelevantPage() {
    const url = window.location.href;
    const hostname = window.location.hostname;
    
    // Check if we're on APS Dream Home domain
    const isAPSDomain = hostname.includes('apsdreamhome') || 
                        hostname.includes('localhost') || 
                        hostname.includes('127.0.0.1');
    
    // Check if on property/lead related page
    const path = window.location.pathname;
    const isPropertyPage = window.location.pathname.includes('/property/') ||
                          window.location.pathname.includes('/listing/') ||
                          window.location.pathname.includes('/lead/') ||
                          window.location.pathname.includes('/plots/') ||
                          window.location.pathname.includes('/colonies/') ||
                          window.location.pathname.includes('/leads/');
    
    return isAPSDomain && (isPropertyPage || window.location.pathname.includes('/property/') || 
                           window.location.pathname.includes('/listing/') || 
                           window.location.pathname.includes('/lead/') ||
                           window.location.pathname.includes('/plots/') ||
                           window.location.pathname.includes('/colonies/') ||
                           window.location.pathname.includes('/leads/') ||
                           window.location.pathname.includes('/property/') ||
                           window.location.pathname.includes('/properties/'));
  }
  
  function injectFloatButton() {
    if (document.getElementById('aps-ai-float-btn')) {
      return; // Already injected
    }
    
    const btn = document.createElement('button');
    btn.id = 'aps-ai-float-btn';
    btn.innerHTML = '🤖 APS AI';
    btn.setAttribute('aria-label', 'Open APS AI Assistant');
    btn.title = 'Open APS AI Assistant (Alt+Shift+A)';
    btn.style.cssText = `
      position: fixed; bottom: 80px; right: 20px; z-index: 9999;
      background: linear-gradient(135deg, #0d9488, #0f766e);
      color: white; border: none; border-radius: 50px;
      padding: 12px 20px; font-weight: 600; cursor: pointer;
      box-shadow: 0 4px 20px rgba(13,148,136,0.4);
      font-family: system-ui, sans-serif; font-size: 14px;
      display: flex; align-items: center; gap: 8px;
      transition: all 0.2s ease;
    `;
    
    btn.onmouseenter = () => {
      btn.style.transform = 'translateY(-2px)';
      btn.style.boxShadow = '0 6px 25px rgba(13,148,136,0.5)';
    };
    
    btn.onmouseleave = () => {
      btn.style.transform = 'translateY(0)';
      btn.style.boxShadow = '0 4px 20px rgba(13,148,136,0.4)';
    };
    
    btn.onclick = () => {
      chrome.runtime.sendMessage({ action: 'openPopup' });
    };
    
    // Keyboard shortcut hint
    btn.title = 'Open APS AI Assistant (Alt+Shift+A)';
    
    document.body.appendChild(btn);
    
    // Add keyboard shortcut listener
    document.addEventListener('keydown', (e) => {
      if (e.altKey && e.shiftKey && (e.key === 'A' || e.key === 'a')) {
        e.preventDefault();
        chrome.runtime.sendMessage({ action: 'openPopup' });
      }
    });
    
    console.log('[APS AI] Float button injected');
  }
  
  function observeDOMChanges() {
    const observer = new MutationObserver((mutations) => {
      for (const mutation of mutations) {
        if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
          // Check for dynamically added property/lead elements
          for (const node of mutation.addedNodes) {
            if (node.nodeType === Node.ELEMENT_NODE) {
              enhanceNewElements(node);
            }
          }
        }
      }
    });
    
    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
  }
  
  function enhanceNewElements(node) {
    // Add AI assist buttons to property cards, lead cards, etc.
    const propertyCards = node.querySelectorAll ? node.querySelectorAll('.property-card, .listing-card, .lead-card, .property-card') : [];
    
    for (const card of propertyCards) {
      if (!card.querySelector('.aps-ai-assist-btn')) {
        enhanceCard(card);
      }
    }
    
    // Enhance property detail pages
    const detailSections = node.querySelectorAll ? node.querySelectorAll('.property-detail, .lead-detail, .property-detail') : [];
    for (const section of detailSections) {
      if (!section.querySelector('.aps-ai-assist-section')) {
        enhanceDetailSection(section);
      }
    }
  }
  
  function enhanceCard(card) {
    const actionsDiv = document.createElement('div');
    actionsDiv.className = 'aps-ai-assist-section';
    actionsDiv.style.cssText = 'position: absolute; top: 10px; right: 10px; z-index: 10; display: flex; gap: 4px; opacity: 0; transition: opacity 0.2s;';
    
    const actions = [
      { title: 'AI Summary', icon: '📝', action: 'summarize' },
      { title: 'Rewrite', icon: '✨', action: 'rewrite' },
      { title: 'Share', icon: '📤', action: 'share' }
    ];
    
    actions.forEach(action => {
      const btn = document.createElement('button');
      btn.innerHTML = action.icon;
      btn.title = action.title;
      btn.style.cssText = `
        width: 32px; height: 32px; border-radius: 50%;
        background: rgba(13, 148, 136, 0.9); color: white;
        border: none; cursor: pointer; display: flex;
        align-items: center; justify-content: center;
        font-size: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        transition: all 0.2s ease;
      `;
      btn.onmouseenter = () => btn.style.transform = 'scale(1.1)';
      btn.onmouseleave = () => btn.style.transform = 'scale(1)';
      btn.onclick = (e) => {
        e.stopPropagation();
        handleAIAction(action.action, card);
      };
      actionsDiv.appendChild(btn);
    });
    
    card.style.position = 'relative';
    card.appendChild(actionsDiv);
    
    // Show on hover
    card.addEventListener('mouseenter', () => {
      actionsDiv.style.opacity = '1';
    });
    card.addEventListener('mouseleave', () => {
      actionsDiv.style.opacity = '0';
    });
  }
  
  function enhanceDetailSection(section) {
    const container = document.createElement('div');
    container.className = 'aps-ai-assist-section';
    container.style.cssText = 'margin: 16px 0; padding: 16px; background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%); border: 1px solid #d1fae5; border-radius: 12px;';
    
    container.innerHTML = `
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
        <span style="font-size: 20px;">🤖</span>
        <strong style="color: #0d9488; font-size: 16px;">APS AI Assistant</strong>
        <span style="font-size: 12px; color: #64748b;">Tap any button to use AI on this page</span>
      </div>
      <div style="display: flex; flex-wrap: wrap; gap: 8px;">
        <button class="aps-ai-btn" data-action="summarize" style="background: #0d9488; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 6px;">
          <i class="fas fa-compress-alt"></i> Summarize Page
        </button>
        <button class="aps-ai-btn" data-action="rewrite" style="background: #6366f1; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 6px;">
          <i class="fas fa-magic"></i> Rewrite Content
        </button>
        <button class="aps-ai-btn" data-action="extract" style="background: #f59e0b; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 6px;">
          <i class="fas fa-list"></i> Extract Details
        </button>
        <button class="aps-ai-btn" data-action="share" style="background: #2563eb; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 13px; display: flex; align-items-center; gap: 6px;">
          <i class="fas fa-share-alt"></i> Share Property
        </button>
      </div>
    `;
    
    section.insertBefore(container, section.firstChild);
    
    // Add event listeners to buttons
    container.querySelectorAll('.aps-ai-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const action = btn.dataset.action;
        handleAIAction(action, document.body);
      });
    });
  }
  
  function handleAIAction(action, contextElement) {
    // Extract relevant content from page
    const pageContent = extractPageContent();
    const pageUrl = window.location.href;
    
    switch (action) {
      case 'summarize':
        chrome.runtime.sendMessage({
          action: 'summarize',
          text: pageContent,
          url: window.location.href
        });
        break;
      case 'rewrite':
        chrome.runtime.sendMessage({
          action: 'rewrite',
          text: pageContent,
          url: window.location.href
        });
        break;
      case 'extract':
        chrome.runtime.sendMessage({
          action: 'extract',
          text: pageContent,
          url: window.location.href
        });
        break;
      case 'share':
        // Trigger share modal
        chrome.runtime.sendMessage({ action: 'openShareModal', url: window.location.href });
        break;
    }
  }
  
  function extractPageContent() {
    // Extract relevant content from the page
    const selectors = [
      '.property-detail', '.property-detail', '.listing-detail',
      '.property-description', '.listing-description',
      '.property-info', '.listing-info',
      '.property-title', '.listing-title',
      '.property-price', '.listing-price',
      '.property-location', '.listing-location'
    ];
    
    let content = '';
    for (const selector of selectors) {
      const elements = document.querySelectorAll(selector);
      for (const el of elements) {
        const text = el.innerText.trim();
        if (text.length > 20) {
          content += text + '\n\n';
        }
      }
    }
    
    // Fallback to body text if nothing found
    if (!content) {
      content = document.body.innerText.slice(0, 5000);
    }
    
    return content.slice(0, 8000); // Limit length
  }
  
  function handleMessage(message, sender, sendResponse) {
    switch (message.action) {
      case 'openPopup':
        // Open extension popup programmatically (not directly possible, but we can notify)
        chrome.runtime.sendMessage({ action: 'openPopup' });
        break;
      case 'getPageContent':
        sendResponse({ content: extractPageContent() });
        break;
      case 'showResult':
        showAIResult(message.data);
        break;
      case 'showNotification':
        showNotification(message.message, message.type);
        break;
    }
    return true;
  }
  
  function showAIResult(data) {
    // Create or update result display
    let container = document.getElementById('aps-ai-result');
    if (!container) {
      container = document.createElement('div');
      container.id = 'aps-ai-result';
      container.style.cssText = `
        position: fixed; top: 80px; right: 20px; z-index: 10000;
        max-width: 400px; background: white; border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15); padding: 16px;
        font-family: system-ui, sans-serif; font-size: 14px;
        border: 1px solid #e2e8f0; border-left: 4px solid #0d9488;
        z-index: 9999;
      `;
      document.body.appendChild(container);
    }
    
    container.innerHTML = `
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <strong style="color: #0d9488;">🤖 APS AI Result</strong>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #64748b;">✕</button>
      </div>
      <div style="white-space: pre-wrap; font-size: 14px; line-height: 1.6; color: #334155;">${message.data.content || message.data.text || message.data.summary || JSON.stringify(message.data)}</div>
      <div style="margin-top: 12px; display: flex; gap: 8px;">
        <button onclick="navigator.clipboard.writeText(this.parentElement.previousElementSibling.innerText).then(()=>alert('Copied!'))" style="padding: 6px 12px; background: #0d9488; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px;">Copy</button>
        <button onclick="this.closest('#aps-ai-result').remove()" style="padding: 6px 12px; background: #f1f5f9; color: #475569; border: none; border-radius: 6px; cursor: pointer; font-size: 12px;">Dismiss</button>
      </div>
    `;
  }
  
  function showNotification(message, type = 'info') {
    const colors = {
      info: '#3b82f6',
      success: '#10b981',
      warning: '#f59e0b',
      error: '#ef4444'
    };
    
    const notification = document.createElement('div');
    notification.style.cssText = `
      position: fixed; bottom: 24px; right: 24px; z-index: 10000;
      padding: 16px 24px; border-radius: 10px; color: white;
      font-weight: 500; font-size: 14px; box-shadow: 0 10px 40px rgba(0,0,0,0.15);
      background: ${colors[type] || colors.info};
      animation: slideIn 0.3s ease-out;
    `;
    notification.innerHTML = `
      <div style="display: flex; align-items: center; gap: 8px;">
        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-times-circle' : 'fa-info-circle'}"></i>
        <span>${message}</span>
      </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
      notification.style.animation = 'slideOut 0.3s ease-in forwards';
      setTimeout(() => notification.remove(), 300);
    }, 4000);
  }
  
  // Handle messages from background/popup
  chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
    switch (message.action) {
      case 'getPageContent':
        sendResponse({ content: extractPageContent() });
        break;
      case 'showResult':
        showAIResult(message.data);
        break;
      case 'showNotification':
        showNotification(message.message, message.type);
        break;
      case 'getPageInfo':
        sendResponse({
          url: window.location.href,
          title: document.title,
          content: extractPageContent().slice(0, 5000)
        });
        break;
    }
    return true;
  });
  
  // Keyboard shortcut
  document.addEventListener('keydown', (e) => {
    if (e.altKey && e.shiftKey && (e.key === 'A' || e.key === 'a')) {
      e.preventDefault();
      chrome.runtime.sendMessage({ action: 'openPopup' });
    }
  });
  
  console.log('[APS AI] Content script fully loaded');
})();