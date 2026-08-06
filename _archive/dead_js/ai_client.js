var AIClient = {
  init: function () {},
  sendMessage: function (message, callback) {
    fetch('/api/ai/chatbot', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: message }),
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (callback) callback(data);
      })
      .catch(function (err) {
        console.error('AI error:', err);
      });
  },
};
