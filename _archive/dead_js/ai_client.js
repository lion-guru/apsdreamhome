var AIClient = {
  init: function () {
    /* AI Client initialized */
  },
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
        /* AI error handled silently */
      });
  },
};
