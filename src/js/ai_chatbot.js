$(function() {
    // Check if AI chatbot is enabled
    $.get('admin/fetch_ai_settings.php', function(settings) {
        if(settings.ai_chatbot == 1) {
            if($('#aiChatbotWidget').length) return;
            var chatbotHtml = `
            <div id="aiChatbotWidget" style="position:fixed;bottom:24px;right:24px;z-index:9999;font-family:sans-serif;">
                <button id="openChatbotBtn" class="btn btn-primary rounded-circle shadow" style="width:56px;height:56px;font-size:24px;"><i class="fa fa-comments"></i></button>
                <div id="chatbotWindow" class="card shadow" style="display:none;width:320px;max-width:90vw;">
                    <div class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center">
                        <span><i class="fa fa-robot me-2"></i>APS AI Assistant</span>
                        <button type="button" class="btn-close btn-close-white btn-sm ms-2" id="closeChatbotBtn"></button>
                    </div>
                    <div class="card-body p-3" id="chatbotMessages" style="height:260px;overflow-y:auto;font-size:15px;background:#f8f9fa;">
                        <div class="text-muted small">AI is enabled. How can I help you?</div>
                    </div>
                    <div class="card-footer p-2">
                        <form id="chatbotForm" autocomplete="off">
                            <div class="input-group">
                                <input type="text" class="form-control" id="chatbotInput" placeholder="Type your message..." required>
                                <button class="btn btn-primary" type="submit"><i class="fa fa-paper-plane"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>`;
            $('body').append(chatbotHtml);
            $('#openChatbotBtn').click(function(){
                $('#chatbotWindow').show();
                $('#openChatbotBtn').hide();
            });
            $('#closeChatbotBtn').click(function(){
                $('#chatbotWindow').hide();
                $('#openChatbotBtn').show();
            });
            $('#chatbotForm').submit(function(e){
                e.preventDefault();
                var msg = $('#chatbotInput').val();
                if(!msg) return;
                $('#chatbotMessages').append(`<div class='mb-2 text-end'><span class='badge bg-primary'>You</span> <span>${msg}</span></div>`);
                $('#chatbotInput').val('');
                $('#chatbotMessages').append(`<div class='mb-2'><span class='badge bg-secondary'>AI</span> <span><em>Thinking...</em></span></div>`);
                $('#chatbotMessages').scrollTop($('#chatbotMessages')[0].scrollHeight);
                // Send to backend for real AI response
                $.ajax({
                    url: 'ai_chatbot_api.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({message: msg}),
                    success: function(resp) {
                        $('#chatbotMessages .badge.bg-secondary').last().next().remove(); // Remove old demo/placeholder if any
                        if(resp.success) {
                            $('#chatbotMessages').append(`<div class='mb-2'><span class='badge bg-secondary'>AI</span> <span>${resp.reply.replace(/\n/g,'<br>')}</span></div>`);
                        } else {
                            $('#chatbotMessages').append(`<div class='mb-2 text-danger'><span class='badge bg-secondary'>AI</span> <span>${resp.error||'AI error'}</span></div>`);
                        }
                        $('#chatbotMessages').scrollTop($('#chatbotMessages')[0].scrollHeight);
                    },
                    error: function() {
                        $('#chatbotMessages').append(`<div class='mb-2 text-danger'><span class='badge bg-secondary'>AI</span> <span>Network error. Please try again.</span></div>`);
                        $('#chatbotMessages').scrollTop($('#chatbotMessages')[0].scrollHeight);
                    }
                });
            });
        }
    });
});
