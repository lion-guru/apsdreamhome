(function () {
  'use strict';

  let chatOpen = false;

  var uc = window.chatbotUserContext || {};
  var ChatbotUserContext = {
    role: uc.role || 'guest',
    userId: uc.userId || '',
    userName: uc.userName || '',
    isLoggedIn: uc.isLoggedIn || false,
  };

  let chatLanguage = localStorage.getItem('chatLanguage') || 'hinglish';

  window.toggleChatLanguage = function () {
    chatLanguage = chatLanguage === 'hinglish' ? 'english' : 'hinglish';
    localStorage.setItem('chatLanguage', chatLanguage);
    updateLanguageButton();
    const langName = chatLanguage === 'hinglish' ? 'Hinglish' : 'English';
    addMessage('Switched to ' + langName + '! 🌐', false, { animate: true });
  };

  function updateLanguageButton() {
    var btn = document.getElementById('langToggle');
    if (btn) {
      btn.textContent = chatLanguage === 'hinglish' ? '🇮🇳 HI' : '🇬🇧 EN';
      btn.title = chatLanguage === 'hinglish' ? 'Switch to English' : 'Hinglish mein badlein';
    }
  }

  var ChatbotState = {
    userName: ChatbotUserContext.userName || localStorage.getItem('chatbot_user_name') || null,
    conversationHistory: JSON.parse(localStorage.getItem('chatbot_history') || '[]'),
    isTyping: false,
    sessionId: 'session_' + Date.now(),
    userRole: ChatbotUserContext.role,
    saveUserName: function (name) {
      this.userName = name;
      localStorage.setItem('chatbot_user_name', name);
    },
    addToHistory: function (role, message) {
      this.conversationHistory.push({ role: role, message: message, timestamp: new Date().toISOString() });
      if (this.conversationHistory.length > 20) {
        this.conversationHistory = this.conversationHistory.slice(-20);
      }
      localStorage.setItem('chatbot_history', JSON.stringify(this.conversationHistory));
    },
    getContextForAI: function () {
      return this.conversationHistory.slice(-5).map(function (h) {
        return { role: h.role, content: h.message };
      });
    },
  };

  window.toggleChat = function () {
    var popup = document.getElementById('chatPopup');
    if (!popup) return;
    chatOpen = !chatOpen;
    if (chatOpen) {
      popup.classList.add('active');
      var inp = document.getElementById('chatInput');
      if (inp) inp.focus();
    } else {
      popup.classList.remove('active');
    }
  };

  function showTyping() {
    var chatBody = document.getElementById('chatBody');
    if (!chatBody) return;
    hideTyping();
    var typing = document.createElement('div');
    typing.id = 'typingIndicator';
    typing.className = 'typing-indicator ai-message bot';
    typing.innerHTML = '<span></span><span></span><span></span>';
    chatBody.appendChild(typing);
    chatBody.scrollTop = chatBody.scrollHeight;
    ChatbotState.isTyping = true;
  }

  function hideTyping() {
    var typing = document.getElementById('typingIndicator');
    if (typing) {
      typing.remove();
    }
    ChatbotState.isTyping = false;
  }

  function calculateTypingDelay(message) {
    return Math.min(1000 + message.length * 30, 4000);
  }

  window.sendQuickMessage = function (message) {
    var inp = document.getElementById('chatInput');
    if (inp) inp.value = message;
    sendChatMessage();
  };

  function addMessage(text, isUser, options) {
    options = options || {};
    var chatBody = document.getElementById('chatBody');
    if (!chatBody) return null;
    var time = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    var messageDiv = document.createElement('div');
    messageDiv.className = 'ai-message ' + (isUser ? 'user' : 'bot');
    var displayText = isUser ? escapeHtml(text) : formatBotResponse(text);
    messageDiv.innerHTML =
      '<div class="ai-message-content">' + displayText + '</div><span class="ai-time">' + time + '</span>';
    chatBody.appendChild(messageDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
    ChatbotState.addToHistory(isUser ? 'user' : 'assistant', text);
    return messageDiv;
  }

  function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  function formatBotResponse(text) {
    return text
      .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
      .replace(/\*(.*?)\*/g, '<em>$1</em>')
      .replace(/\n/g, '<br>');
  }

  function getPersonalizedGreeting() {
    if (ChatbotState.userName) {
      var greetings = [
        'Welcome back, ' + ChatbotState.userName + '! 🙏',
        'Namaste ' + ChatbotState.userName + '! Kaise madad kar sakta hoon?',
        'Hello ' + ChatbotState.userName + '! Ready to find your dream property?',
      ];
      return greetings[Math.floor(Math.random() * greetings.length)];
    }
    return null;
  }

  function extractName(message) {
    var patterns = [
      /(?:mera|my)\s+naam\s+(\w+)/i,
      /(?:i am|i'm|iam)\s+(\w+)/i,
      /(?:namaste|hello|hi)\s+(\w+)/i,
      /(?:this is)\s+(\w+)/i,
    ];
    for (var i = 0; i < patterns.length; i++) {
      var match = message.match(patterns[i]);
      if (match) return match[1];
    }
    return null;
  }

  function sendChatMessage() {
    var input = document.getElementById('chatInput');
    if (!input) return;
    var message = input.value.trim();
    if (!message || ChatbotState.isTyping) return;

    addMessage(message, true, { animate: true });
    input.value = '';

    var extractedName = extractName(message);
    if (extractedName && !ChatbotState.userName) {
      ChatbotState.saveUserName(extractedName);
    }

    showTyping();

    var apiUrl = window.chatbotApiUrl || '/api/gemini/chat';

    fetch(apiUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Session-ID': ChatbotState.sessionId },
      body: JSON.stringify({
        message: message,
        session_id: ChatbotState.sessionId,
        user_name: ChatbotState.userName,
        user_role: ChatbotState.userRole,
        context: ChatbotState.getContextForAI(),
        language: chatLanguage,
      }),
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        var replyText = data.reply || data.response || "Sorry, I didn't understand that.";
        var typingDelay = calculateTypingDelay(replyText);
        setTimeout(function () {
          hideTyping();
          if (data.success) {
            addMessage(replyText, false, { animate: true });
            if (data.quick_replies && data.quick_replies.length > 0) {
              setTimeout(function () {
                addQuickReplies(data.quick_replies);
              }, 500);
            }
          } else {
            addMessage(
              "Sorry, I'm having trouble understanding. Try calling us at <strong>+91 92771 21112</strong> or <a href='/contact'>Contact Form</a>",
              false,
              { animate: true }
            );
          }
        }, typingDelay);
      })
      .catch(function () {
        hideTyping();
        addMessage(
          'Connection issue! Please try again or call <strong>+91 92771 21112</strong> for instant help.',
          false,
          { animate: true }
        );
      });
  }

  window.sendChatMessage = sendChatMessage;

  function addQuickReplies(replies) {
    var chatBody = document.getElementById('chatBody');
    if (!chatBody) return;
    var suggestionsDiv = document.createElement('div');
    suggestionsDiv.className = 'quick-replies-container';
    suggestionsDiv.style.cssText = 'display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;';
    replies.forEach(function (reply) {
      var btn = document.createElement('button');
      btn.className = 'quick-reply-btn';
      btn.textContent = reply;
      btn.style.cssText =
        'background:linear-gradient(135deg,#667eea,#764ba2);color:white;border:none;padding:8px 16px;border-radius:20px;cursor:pointer;font-size:12px;';
      btn.onclick = function () {
        sendQuickMessage(reply);
      };
      suggestionsDiv.appendChild(btn);
    });
    chatBody.appendChild(suggestionsDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
  }

  window.handleChatKeypress = function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendChatMessage();
    }
  };

  function initializeChatbot() {
    updateLanguageButton();
    var savedName = ChatbotState.userName;
    if (savedName) {
      setTimeout(function () {
        var greeting = getPersonalizedGreeting();
        if (greeting) addMessage(greeting, false, { animate: true });
      }, 1500);
    }
  }

  setTimeout(function () {
    if (!chatOpen) {
      var btn = document.getElementById('aiFloatBtn');
      if (btn) btn.style.animation = 'pulse 1s infinite';
    }
  }, 10000);

  document.addEventListener('DOMContentLoaded', initializeChatbot);
})();
